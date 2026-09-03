<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\Payment;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Services\MidtransService;

class PaymentController extends Controller
{
    /**
     * Check and sync transaction status with Midtrans.
     */
    public function checkStatus(Request $request, Payment $payment, MidtransService $midtransService): JsonResponse
    {
        $payment->load('order');
        abort_if($payment->order->user_id !== $request->user()->id, 403);

        if ($payment->status === Payment::STATUS_PAID) {
            return response()->json([
                'message' => 'Pembayaran sudah lunas.',
                'payment' => $payment,
                'order' => $payment->order->load(['payment', 'trackings']),
            ]);
        }

        // Fetch transaction status from Midtrans API
        $statusData = $midtransService->getTransactionStatus($payment->order->invoice_number);
        $transactionStatus = $statusData['status'];

        DB::transaction(function () use ($payment, $statusData, $transactionStatus) {
            // Dynamically update fields based on selected method in Midtrans Snap UI
            $updateFields = [];
            if (!empty($statusData['bank'])) {
                $updateFields['bank_code'] = $statusData['bank'];
            }
            if (!empty($statusData['va_number'])) {
                $updateFields['virtual_account_number'] = $statusData['va_number'];
            }
            if (!empty($statusData['biller_code'])) {
                $updateFields['biller_code'] = $statusData['biller_code'];
            }

            if ($transactionStatus === 'settlement' || $transactionStatus === 'capture') {
                $updateFields['status'] = Payment::STATUS_PAID;
                $updateFields['paid_at'] = now();

                $payment->update($updateFields);

                $payment->order->update([
                    'status' => Order::STATUS_PAID,
                ]);

                $payment->order->trackings()->create([
                    'status' => Order::STATUS_PAID,
                    'description' => 'Pembayaran berhasil diverifikasi oleh sistem.',
                    'location' => $payment->order->address?->city ?? 'Sistem',
                ]);

            } elseif (in_array($transactionStatus, ['cancel', 'deny', 'failure', 'expire'], true)) {
                $isExpire = ($transactionStatus === 'expire');
                $newPaymentStatus = $isExpire ? Payment::STATUS_EXPIRED : Payment::STATUS_FAILED;
                $statusDescription = $isExpire 
                    ? 'Pesanan dibatalkan otomatis karena batas waktu pembayaran habis.' 
                    : 'Pembayaran gagal atau dibatalkan.';

                $updateFields['status'] = $newPaymentStatus;
                $payment->update($updateFields);

                $payment->order->update([
                    'status' => Order::STATUS_CANCELLED,
                ]);

                $payment->order->trackings()->create([
                    'status' => Order::STATUS_CANCELLED,
                    'description' => $statusDescription,
                    'location' => $payment->order->address?->city ?? 'Sistem',
                ]);

                // Restore stock for all products in this order
                foreach ($payment->order->items as $item) {
                    $product = $item->product;
                    if ($product) {
                        $product->increment('stock', $item->quantity);
                        
                        $product->stockMovements()->create([
                            'user_id' => $payment->order->user_id,
                            'type' => 'in',
                            'quantity' => $item->quantity,
                            'reference' => $payment->order->invoice_number,
                            'note' => $isExpire 
                                ? 'Restock: Waktu pembayaran habis (Midtrans Expire)'
                                : 'Restock: Pembayaran gagal/batal (Midtrans Cancel/Deny/Failure)',
                        ]);
                    }
                }
            } else {
                // If it is still pending/waiting, update selected bank/VA details if present
                if (!empty($updateFields)) {
                    $payment->update($updateFields);
                }
            }
        });

        return response()->json([
            'message' => 'Status pembayaran berhasil disinkronisasi.',
            'payment' => $payment->fresh(),
            'order' => $payment->order->fresh()->load(['payment', 'trackings']),
        ]);
    }
}
