<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\Payment;
use App\Services\MidtransService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class MidtransCallbackController extends Controller
{
    /**
     * Handle payment notification from Midtrans.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function handle(Request $request, MidtransService $midtransService): JsonResponse
    {
        $payload = $request->all();

        Log::info('Midtrans Webhook Received', $payload);

        $orderId = $payload['order_id'] ?? null;
        $statusCode = $payload['status_code'] ?? null;
        $grossAmount = $payload['gross_amount'] ?? null;
        $signatureKey = $payload['signature_key'] ?? null;
        $transactionStatus = $payload['transaction_status'] ?? null;

        if (!$orderId || !$statusCode || !$grossAmount) {
            return response()->json(['message' => 'Parameter tidak lengkap.'], 400);
        }

        // [SECURITY] Verify signature using MidtransService.
        // Midtrans sends gross_amount as a string (e.g. "10000.00") — keep it raw.
        $serverKeyConfigured = !empty(config('services.midtrans.server_key', env('MIDTRANS_SERVER_KEY', '')));

        if (!$serverKeyConfigured) {
            if (!app()->environment('local', 'testing')) {
                Log::error('Midtrans Webhook rejected: MIDTRANS_SERVER_KEY is not configured in production.');
                return response()->json(['message' => 'Konfigurasi server tidak lengkap.'], 500);
            }
            Log::warning('Midtrans signature verification skipped (local/testing environment, no server key).');
        } else {
            $signatureValid = $midtransService->verifyNotificationSignature(
                (string) $orderId,
                (string) $statusCode,
                (string) $grossAmount,
                (string) $signatureKey
            );

            if (!$signatureValid) {
                Log::warning('Midtrans Webhook Invalid Signature', [
                    'order_id' => $orderId,
                ]);
                return response()->json(['message' => 'Tanda tangan tidak valid.'], 403);
            }
        }

        // Find the order by invoice_number
        $order = Order::query()->where('invoice_number', $orderId)->with(['payment', 'items.product'])->first();

        if (!$order) {
            return response()->json(['message' => 'Order tidak ditemukan.'], 404);
        }

        if (!$order->payment) {
            return response()->json(['message' => 'Data pembayaran order tidak ditemukan.'], 404);
        }

        // Avoid re-processing already paid or cancelled orders
        if ($order->payment->status === Payment::STATUS_PAID) {
            return response()->json(['message' => 'Order sudah dibayar sebelumnya.']);
        }

        try {
            DB::transaction(function () use ($order, $transactionStatus, $payload) {
                if ($transactionStatus === 'capture' || $transactionStatus === 'settlement') {
                    $updateFields = [
                        'status' => Payment::STATUS_PAID,
                        'paid_at' => now(),
                    ];

                    $paymentType = $payload['payment_type'] ?? null;

                    if ($paymentType === 'bank_transfer' && !empty($payload['va_numbers'])) {
                        $updateFields['bank_code']               = $payload['va_numbers'][0]['bank']      ?? null;
                        $updateFields['virtual_account_number']  = $payload['va_numbers'][0]['va_number'] ?? null;
                    } elseif ($paymentType === 'echannel') {
                        $updateFields['bank_code']              = 'mandiri';
                        $updateFields['virtual_account_number'] = $payload['bill_key']    ?? null;
                        $updateFields['biller_code']            = $payload['biller_code'] ?? null;
                    } elseif ($paymentType === 'cstore') {
                        $updateFields['bank_code']              = $payload['store']        ?? 'cstore';
                        $updateFields['virtual_account_number'] = $payload['payment_code'] ?? null;
                    } elseif ($paymentType === 'gopay' || $paymentType === 'qris') {
                        $updateFields['bank_code']              = $paymentType;
                        $updateFields['virtual_account_number'] = $payload['acquirer'] ?? null;
                    }

                    // Update Payment and Order to PAID
                    $order->payment->update($updateFields);

                    $order->update([
                        'status' => Order::STATUS_PAID,
                    ]);

                    $order->trackings()->create([
                        'status' => Order::STATUS_PAID,
                        'description' => 'Pembayaran berhasil diverifikasi oleh sistem.',
                        'location' => $order->address?->city ?? 'Sistem',
                    ]);

                    try {
                        $user = $order->user;
                        \App\Models\Announcement::create([
                            'title' => '📦 Pesanan Baru Dibayar!',
                            'content' => "Pesanan #{$order->invoice_number} oleh " . ($user?->name ?? 'Pelanggan') . " sebesar Rp " . number_format((float)$order->total_amount, 0, ',', '.') . " telah berhasil dibayar. Mohon segera diproses.",
                            'type' => 'order',
                            'action_url' => route('admin.orders.show', $order->getRouteKey()),
                        ]);
                    } catch (\Exception $e) {
                        Log::error('Failed to create order paid announcement: ' . $e->getMessage());
                    }

                    Log::info("Order {$order->invoice_number} successfully paid.");
                } elseif (in_array($transactionStatus, ['cancel', 'deny', 'failure', 'expire'], true)) {

                    $isExpire = ($transactionStatus === 'expire');
                    $newPaymentStatus = $isExpire ? Payment::STATUS_EXPIRED : Payment::STATUS_FAILED;
                    $statusDescription = $isExpire
                        ? 'Pesanan dibatalkan otomatis karena batas waktu pembayaran habis.'
                        : 'Pembayaran gagal atau dibatalkan.';

                    // Update Payment and Order to EXPIRED/FAILED and CANCELLED
                    $order->payment->update([
                        'status' => $newPaymentStatus,
                    ]);

                    $order->update([
                        'status' => Order::STATUS_CANCELLED,
                    ]);

                    $order->trackings()->create([
                        'status' => Order::STATUS_CANCELLED,
                        'description' => $statusDescription,
                        'location' => $order->address?->city ?? 'Sistem',
                    ]);

                    // Restore Product stock
                    foreach ($order->items as $item) {
                        $product = $item->product;
                        if ($product) {
                            $product->increment('stock', $item->quantity, []);

                            $product->stockMovements()->create([
                                'user_id' => $order->user_id,
                                'type' => 'in',
                                'quantity' => $item->quantity,
                                'reference' => $order->invoice_number,
                                'note' => $isExpire
                                    ? 'Restock: Waktu pembayaran habis (Midtrans Expire)'
                                    : 'Restock: Pembayaran gagal/batal (Midtrans Cancel/Deny/Failure)',
                            ]);
                        }
                    }

                    Log::info("Order {$order->invoice_number} cancelled. Stock restored.");
                }
            });

            return response()->json(['message' => 'Status pembayaran berhasil diperbarui.']);
        } catch (\Exception $e) {
            Log::error('Error processing Midtrans Callback transaction', [
                'order_id' => $orderId,
                'error' => $e->getMessage()
            ]);

            return response()->json(['message' => 'Terjadi kesalahan internal.'], 500);
        }
    }
}
