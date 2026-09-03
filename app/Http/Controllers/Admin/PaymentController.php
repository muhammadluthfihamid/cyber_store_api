<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Payment;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Facades\Cache;

class PaymentController extends Controller
{
    public static function flushRedisCache(): void
    {
        try {
            $redis = Cache::store('redis');
            $redis->increment('admin:payments:version');
        } catch (\Throwable $e) {
            Cache::forget('admin:payments:version');
        }
    }

    public function index(Request $request)
    {
        $search = trim((string)$request->query('search', ''));
        $status = $request->query('status');
        $bank = $request->query('bank');
        $page = (int)$request->query('page', 1);

        try {
            $version = Cache::store('redis')->get('admin:payments:version', 1);
            $cacheKey = "admin:payments:v{$version}:" . md5(json_encode([
                'search' => $search,
                'status' => $status,
                'bank' => $bank,
                'page' => $page,
            ]));

            $cachedData = Cache::store('redis')->remember($cacheKey, now()->addMinutes(15), function () use ($search, $status, $bank) {
                $query = Payment::query()->latest('id');

                if ($search !== '') {
                    $query->where(function ($q) use ($search) {
                        $q->where('virtual_account_number', 'like', "%{$search}%")
                          ->orWhereHas('order', fn ($o) => $o->where('invoice_number', 'like', "%{$search}%"))
                          ->orWhereHas('order.user', fn ($u) => $u->where('name', 'like', "%{$search}%"));
                    });
                }

                if ($status !== null && $status !== '') {
                    $query->where('status', $status);
                }

                if ($bank !== null && $bank !== '') {
                    $query->where('bank_code', $bank);
                }

                $paginator = $query->paginate(15);
                return [
                    'ids' => $paginator->pluck('id')->toArray(),
                    'total' => $paginator->total(),
                    'per_page' => $paginator->perPage(),
                    'current_page' => $paginator->currentPage(),
                ];
            });

            $ids = $cachedData['ids'] ?? [];
            $items = empty($ids)
                ? collect()
                : Payment::with(['order.user'])
                    ->whereIn('id', $ids)
                    ->get()
                    ->sortBy(fn ($p) => array_search($p->id, $ids))
                    ->values();

            $payments = new LengthAwarePaginator(
                $items,
                $cachedData['total'] ?? 0,
                $cachedData['per_page'] ?? 15,
                $cachedData['current_page'] ?? 1,
                [
                    'path' => Paginator::resolveCurrentPath(),
                    'query' => $request->query(),
                ]
            );
        } catch (\Throwable $e) {
            $query = Payment::with(['order.user'])->latest();

            if ($search !== '') {
                $query->where(function ($q) use ($search) {
                    $q->where('virtual_account_number', 'like', "%{$search}%")
                      ->orWhereHas('order', fn ($o) => $o->where('invoice_number', 'like', "%{$search}%"))
                      ->orWhereHas('order.user', fn ($u) => $u->where('name', 'like', "%{$search}%"));
                });
            }

            if ($status !== null && $status !== '') {
                $query->where('status', $status);
            }

            if ($bank !== null && $bank !== '') {
                $query->where('bank_code', $bank);
            }

            $payments = $query->paginate(15)->withQueryString();
        }

        return view('admin.payments.index', compact('payments'));
    }

    public function suggestions(Request $request)
    {
        $q = trim((string)$request->query('q', ''));

        try {
            $version = Cache::store('redis')->get('admin:payments:version', 1);
            $cacheKey = "admin:payments:suggestions:v{$version}:" . md5($q);

            $results = Cache::store('redis')->remember($cacheKey, now()->addMinutes(10), function () use ($q) {
                $query = Payment::with(['order.user'])->latest();
                if ($q !== '') {
                    $query->where(function ($w) use ($q) {
                        $w->where('virtual_account_number', 'like', "%{$q}%")
                          ->orWhereHas('order', fn ($o) => $o->where('invoice_number', 'like', "%{$q}%"))
                          ->orWhereHas('order.user', fn ($u) => $u->where('name', 'like', "%{$q}%"));
                    });
                }
                return $query->take(8)->get()->map(function ($pay) {
                    $statusLabels = [
                        'waiting_payment' => 'Menunggu Bayar',
                        'paid'            => 'Dibayar',
                        'expired'         => 'Kedaluwarsa',
                        'failed'          => 'Gagal'
                    ];
                    $inv = $pay->order?->invoice_number ?? '-';
                    $cust = $pay->order?->user?->name ?? '-';
                    $va = $pay->virtual_account_number ?: 'VA #' . $pay->id;
                    $bank = strtoupper($pay->bank_code ?? 'BANK');

                    return [
                        'id' => $pay->id,
                        'title' => $va . ' (' . $bank . ')',
                        'subtitle' => $inv . ' • ' . $cust . ' • Rp ' . number_format($pay->amount, 0, ',', '.'),
                        'value' => $pay->virtual_account_number ?: $inv,
                        'badge' => $statusLabels[$pay->status] ?? $pay->status,
                        'badge_status' => $pay->status,
                        'icon' => 'flat-color-icons:credit-card',
                    ];
                });
            });
        } catch (\Throwable $e) {
            $query = Payment::with(['order.user'])->latest();
            if ($q !== '') {
                $query->where(function ($w) use ($q) {
                    $w->where('virtual_account_number', 'like', "%{$q}%")
                      ->orWhereHas('order', fn ($o) => $o->where('invoice_number', 'like', "%{$q}%"))
                      ->orWhereHas('order.user', fn ($u) => $u->where('name', 'like', "%{$q}%"));
                });
            }
            $results = $query->take(8)->get()->map(function ($pay) {
                $statusLabels = [
                    'waiting_payment' => 'Menunggu Bayar',
                    'paid'            => 'Dibayar',
                    'expired'         => 'Kedaluwarsa',
                    'failed'          => 'Gagal'
                ];
                $inv = $pay->order?->invoice_number ?? '-';
                $cust = $pay->order?->user?->name ?? '-';
                $va = $pay->virtual_account_number ?: 'VA #' . $pay->id;
                $bank = strtoupper($pay->bank_code ?? 'BANK');

                return [
                    'id' => $pay->id,
                    'title' => $va . ' (' . $bank . ')',
                    'subtitle' => $inv . ' • ' . $cust . ' • Rp ' . number_format($pay->amount, 0, ',', '.'),
                    'value' => $pay->virtual_account_number ?: $inv,
                    'badge' => $statusLabels[$pay->status] ?? $pay->status,
                    'badge_status' => $pay->status,
                    'icon' => 'flat-color-icons:credit-card',
                ];
            });
        }

        return response()->json($results);
    }

    public function show(Payment $payment)
    {
        $payment->load(['order.user', 'order.items.product', 'order.address']);
        return view('admin.payments.show', compact('payment'));
    }
}

