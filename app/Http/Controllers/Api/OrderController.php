<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Order;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class OrderController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $search = trim((string) ($request->input('search') ?? $request->input('q') ?? $request->input('query') ?? ''));

        $orders = Order::with(['payment', 'expedition', 'items.product', 'address'])
            ->where('user_id', $request->user()->id)
            ->when($request->filled('status'), fn ($query) => $query->where('status', $request->status))
            ->when($search !== '', function ($query) use ($search) {
                $query->where(function ($q) use ($search) {
                    $q->where('invoice_number', 'LIKE', "%{$search}%")
                      ->orWhere('note', 'LIKE', "%{$search}%")
                      ->orWhereHas('items', function ($itemQuery) use ($search) {
                          $itemQuery->where('product_name', 'LIKE', "%{$search}%");
                      })
                      ->orWhereHas('address', function ($addrQuery) use ($search) {
                          $addrQuery->where('receiver_name', 'LIKE', "%{$search}%")
                                    ->orWhere('address', 'LIKE', "%{$search}%")
                                    ->orWhere('notes', 'LIKE', "%{$search}%")
                                    ->orWhere('city', 'LIKE', "%{$search}%");
                      });
                });
            })
            ->latest()
            ->paginate($request->integer('per_page', $search !== '' ? 50 : 10));

        return response()->json($orders);
    }

    public function show(Request $request, Order $order, \App\Services\MidtransService $midtransService): JsonResponse
    {
        abort_if($order->user_id !== $request->user()->id, 403);

        if ($order->payment && $order->payment->status === \App\Models\Payment::STATUS_WAITING_PAYMENT) {
            $isPastDeadline = $order->payment->expired_at && now()->greaterThan($order->payment->expired_at);

            try {
                $statusData = $midtransService->getTransactionStatus($order->invoice_number);
                $transactionStatus = $statusData['status'] ?? 'unknown';

                if ($transactionStatus === 'settlement' || $transactionStatus === 'capture') {
                    DB::transaction(function () use ($order, $statusData) {
                        $order->payment->update([
                            'status' => \App\Models\Payment::STATUS_PAID,
                            'paid_at' => now(),
                            'bank_code' => $statusData['bank'] ?? $order->payment->bank_code,
                            'virtual_account_number' => $statusData['va_number'] ?? $order->payment->virtual_account_number,
                            'biller_code' => $statusData['biller_code'] ?? $order->payment->biller_code,
                        ]);

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
                            // Log error silently
                        }
                    });
                } elseif (in_array($transactionStatus, ['expire', 'cancel', 'deny', 'failure'], true) || $isPastDeadline) {
                    DB::transaction(function () use ($order) {
                        $order->payment->update([
                            'status' => \App\Models\Payment::STATUS_EXPIRED,
                        ]);

                        $order->update([
                            'status' => Order::STATUS_CANCELLED,
                        ]);

                        $order->trackings()->create([
                            'status' => Order::STATUS_CANCELLED,
                            'description' => 'Batas waktu pembayaran telah terlewat. Pesanan dibatalkan otomatis.',
                            'location' => $order->address?->city ?? 'Sistem',
                        ]);

                        foreach ($order->items as $item) {
                            $product = $item->product;
                            if ($product) {
                                $product->increment('stock', $item->quantity, []);
                                $product->stockMovements()->create([
                                    'user_id' => $order->user_id,
                                    'type' => 'in',
                                    'quantity' => $item->quantity,
                                    'reference' => $order->invoice_number,
                                    'note' => 'Restock: Batas waktu pembayaran terlewat',
                                ]);
                            }
                        }
                    });
                } elseif ($transactionStatus !== 'unknown') {
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
                    if (!empty($updateFields)) {
                        $order->payment->update($updateFields);
                    }
                }
            } catch (\Exception $e) {
                if ($isPastDeadline) {
                    DB::transaction(function () use ($order) {
                        $order->payment->update(['status' => \App\Models\Payment::STATUS_EXPIRED]);
                        $order->update(['status' => Order::STATUS_CANCELLED]);
                        $order->trackings()->create([
                            'status' => Order::STATUS_CANCELLED,
                            'description' => 'Batas waktu pembayaran telah terlewat. Pesanan dibatalkan otomatis.',
                            'location' => $order->address?->city ?? 'Sistem',
                        ]);
                        foreach ($order->items as $item) {
                            $product = $item->product;
                            if ($product) {
                                $product->increment('stock', $item->quantity, []);
                            }
                        }
                    });
                }
            }
        }

        return response()->json([
            'order' => $order->fresh()->load(['items.product', 'payment', 'trackings', 'address', 'expedition']),
            'status_labels' => Order::statuses(),
            'store_name' => \App\Models\Setting::get('store_name', 'UBSI Store'),
            'store_address' => \App\Models\Setting::get('store_address', 'Jl. Kramat Raya No.98, Senen, Jakarta Pusat'),
            'store_email' => \App\Models\Setting::get('store_email', 'support@bsi.ac.id'),
            'store_phone' => \App\Models\Setting::get('store_phone', '(021) 7867868'),
        ]);
    }

    public function complete(Request $request, Order $order): JsonResponse
    {
        abort_if($order->user_id !== $request->user()->id, 403);

        if ($order->status !== Order::STATUS_ARRIVED) {
            return response()->json(['message' => 'Pesanan belum dapat diselesaikan.'], 422);
        }

        $order->update(['status' => Order::STATUS_COMPLETED]);
        $order->trackings()->create([
            'status' => Order::STATUS_COMPLETED,
            'description' => 'Pesanan telah diselesaikan oleh customer.',
        ]);

        return response()->json([
            'message' => 'Pesanan selesai.',
            'order' => $order->fresh()->load(['items', 'payment', 'trackings']),
        ]);
    }

    public function trackWaybill(Request $request, Order $order): JsonResponse
    {
        abort_if($order->user_id !== $request->user()->id, 403);

        $result = $order->syncTracking();

        if (!$result['success']) {
            return response()->json(['message' => $result['message']], 422);
        }

        return response()->json([
            'message' => $result['message'],
            'order' => $order->fresh()->load(['items.product', 'payment', 'trackings', 'address', 'expedition']),
        ]);
    }

    public function simulateCourierPod(Request $request, Order $order): JsonResponse
    {
        abort_if($order->user_id !== $request->user()->id, 403);

        $receiver = $order->address?->receiver_name ?? ($order->user?->name ?? 'Pelanggan');
        $city = $order->address?->city ?? 'Alamat Tujuan';
        $courierName = $order->expedition?->name ?? 'Kurir Ekspedisi';

        $order->update(['status' => Order::STATUS_ARRIVED]);

        $order->trackings()->create([
            'status'      => Order::STATUS_ARRIVED,
            'description' => "Paket telah sampai di lokasi tujuan dan diserahkan oleh {$courierName} kepada [{$receiver}] (Ybs). Bukti foto serah terima (POD) otomatis terverifikasi sistem.",
            'location'    => $city,
            'proof_photo' => 'order_proofs/mock_pod_sample.jpg',
        ]);

        return response()->json([
            'message' => 'Simulasi kurir berhasil! Bukti pengiriman (Auto-POD) otomatis terunggah.',
            'order' => $order->fresh()->load(['items.product', 'payment', 'trackings', 'address', 'expedition']),
        ]);
    }

    public function cancel(Request $request, Order $order): JsonResponse
    {
        abort_if($order->user_id !== $request->user()->id, 403);

        $reason = $request->input('reason', 'Tidak ada alasan khusus');

        // Case 1: Pending payment -> Cancel immediately
        if ($order->status === Order::STATUS_PENDING_PAYMENT) {
            try {
                DB::transaction(function () use ($order, $reason) {
                    if ($order->payment) {
                        $order->payment->update([
                            'status' => \App\Models\Payment::STATUS_FAILED,
                        ]);
                    }

                    $order->update([
                        'status' => Order::STATUS_CANCELLED,
                        'note' => $order->note ? $order->note . ' | Alasan Batal: ' . $reason : 'Alasan Batal: ' . $reason,
                    ]);

                    $order->trackings()->create([
                        'status' => Order::STATUS_CANCELLED,
                        'description' => 'Pesanan dibatalkan oleh pembeli. Alasan: ' . $reason,
                        'location' => $order->address?->city ?? 'Sistem',
                    ]);

                    foreach ($order->items as $item) {
                        $product = $item->product;
                        if ($product) {
                            $product->increment('stock', $item->quantity, []);
                            
                            $product->stockMovements()->create([
                                'user_id' => $order->user_id,
                                'type' => 'in',
                                'quantity' => $item->quantity,
                                'reference' => $order->invoice_number,
                                'note' => 'Restock: Dibatalkan oleh pembeli (Cancel order)',
                            ]);
                        }
                    }
                });

                return response()->json([
                    'message' => 'Pesanan berhasil dibatalkan.',
                    'order' => $order->fresh()->load(['items.product', 'payment', 'trackings', 'address', 'expedition']),
                ]);

            } catch (\Exception $e) {
                return response()->json([
                    'message' => 'Gagal membatalkan pesanan: ' . $e->getMessage(),
                ], 500);
            }
        }

        // Case 2: Paid or Packed -> Request cancellation
        if (in_array($order->status, [Order::STATUS_PAID, Order::STATUS_PACKED])) {
            if ($order->cancel_request_status === 'pending') {
                return response()->json(['message' => 'Pengajuan pembatalan untuk pesanan ini sedang diproses oleh Admin.'], 422);
            }

            if ($order->cancel_request_status === 'approved') {
                return response()->json(['message' => 'Pengajuan pembatalan sudah disetujui.'], 422);
            }

            $order->update([
                'cancel_request_status' => 'pending',
                'cancel_request_reason' => $reason,
            ]);

            $order->trackings()->create([
                'status' => $order->status,
                'description' => 'Mengajukan pembatalan pesanan. Alasan: ' . $reason,
                'location' => null,
            ]);

            return response()->json([
                'message' => 'Pengajuan pembatalan pesanan berhasil dikirim ke Admin.',
                'order' => $order->fresh()->load(['items.product', 'payment', 'trackings', 'address', 'expedition']),
            ]);
        }

        // Case 3: Other statuses -> Cannot cancel
        return response()->json(['message' => 'Pesanan tidak dapat dibatalkan atau diajukan pembatalan karena sudah dikirim/selesai.'], 422);
    }
}
