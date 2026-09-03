<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Cart;
use App\Models\CustomerAddress;
use App\Models\Expedition;
use App\Models\Order;
use App\Models\Product;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use App\Services\MidtransService;
use App\Models\Payment;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\File;

class CheckoutController extends Controller
{
    public function store(Request $request, MidtransService $midtransService): JsonResponse
    {
        $validated = $request->validate([
            'customer_address_id' => ['required', 'exists:customer_addresses,id'],
            'expedition_id' => ['required', 'exists:expeditions,id'],
            'bank_code' => ['nullable', 'in:bca,bni,bri,mandiri,permata'],
            'note' => ['nullable', 'string', 'max:500'],
            'cart_item_ids' => ['nullable', 'array'],
            'cart_item_ids.*' => ['integer', 'exists:cart_items,id'],
        ]);

        $user = $request->user();

        $address = CustomerAddress::query()->where('id', $validated['customer_address_id'])
            ->where('user_id', $user->id)
            ->firstOrFail();

        if (blank($address->latitude) || blank($address->longitude)) {
            return response()->json([
                'message' => 'Lokasi belum lengkap. Silakan pilih titik lokasi pada map terlebih dahulu.',
            ], 422);
        }

        $expedition = Expedition::query()->where('is_active', true)->findOrFail($validated['expedition_id']);

        $cart = Cart::query()->where('user_id', $user->id)
            ->with(['items.product'])
            ->firstOrFail();

        $cartItems = $cart->items;
        if (isset($validated['cart_item_ids']) && is_array($validated['cart_item_ids'])) {
            $cartItemIds = $validated['cart_item_ids'];
            $cartItems = $cartItems->filter(function ($item) use ($cartItemIds) {
                return in_array($item->id, $cartItemIds);
            });
        }

        if ($cartItems->isEmpty()) {
            return response()->json(['message' => 'Keranjang masih kosong.'], 422);
        }

        $order = DB::transaction(function () use ($cart, $cartItems, $user, $address, $expedition, $validated, $midtransService) {
            $subtotal = 0;
            $totalWeight = 0;

            foreach ($cartItems as $item) {
                $product = Product::query()->where('id', $item->product_id)->lockForUpdate()->firstOrFail();

                if (! $product->is_active) {
                    abort(422, "Produk {$product->name} saat ini sedang tidak aktif.");
                }

                if ($product->stock <= 0) {
                    abort(422, "Stok produk {$product->name} saat ini sedang habis (0). Silakan hapus atau ganti produk ini.");
                }

                if ($product->stock < $item->quantity) {
                    abort(422, "Stok produk {$product->name} hanya tersisa {$product->stock} unit, tidak mencukupi untuk pesanan ({$item->quantity} unit).");
                }

                $subtotal += $product->price * $item->quantity;
                $totalWeight += ($product->weight ?? 1000) * $item->quantity;
            }

            // Find RajaOngkir city ID for destination
            $destinationCityId = null;
            if ($address->city) {
                $userCityName = strtolower(trim($address->city));
                $userCityName = str_replace(['kota ', 'kabupaten '], '', $userCityName);

                $path = database_path('data/rajaongkir_cities.json');
                if (File::exists($path)) {
                    $cities = json_decode(File::get($path), true);
                    foreach ($cities as $c) {
                        $dbCityName = strtolower($c['city_name']);
                        if (str_contains($dbCityName, $userCityName) || str_contains($userCityName, $dbCityName)) {
                            $destinationCityId = $c['city_id'];
                            break;
                        }
                    }
                }
            }

            // Calculate shipping cost from RajaOngkir
            $shippingCost = 0;
            if ($destinationCityId) {
                $originCityId = (int) \App\Models\Setting::get('store_city_id', 152);
                $weight = $totalWeight <= 0 ? 1000 : $totalWeight;

                $courier = 'jne';
                if ($expedition->code === 'pos') {
                    $courier = 'pos';
                } elseif ($expedition->code === 'tiki') {
                    $courier = 'tiki';
                } elseif ($expedition->code === 'sicepat') {
                    $courier = 'jne'; // fallback/approximation using JNE
                }

                try {
                    $response = Http::timeout(3)->withHeaders([
                        'key' => env('RAJAONGKIR_API_KEY')
                    ])->post(env('RAJAONGKIR_BASE_URL') . '/cost', [
                        'origin' => $originCityId,
                        'destination' => $destinationCityId,
                        'weight' => $weight,
                        'courier' => $courier,
                    ]);

                    if ($response->successful()) {
                        $data = $response->json();
                        $costs = $data['rajaongkir']['results'][0]['costs'] ?? [];
                        foreach ($costs as $c) {
                            if ($expedition->code === 'pos') {
                                if ($c['service'] == 'Pos Kilat Khusus' || $c['service'] == 'KILAT') {
                                    $shippingCost = $c['cost'][0]['value'] ?? null;
                                    break;
                                }
                            } else {
                                if ($c['service'] == 'REG') {
                                    $shippingCost = $c['cost'][0]['value'] ?? null;
                                    break;
                                }
                            }
                        }
                    }
                } catch (\Exception $e) {
                    // Ignore
                }

                if ($expedition->code === 'sicepat' && $shippingCost > 0) {
                    $shippingCost = max(8000, $shippingCost - 2000);
                }
            }

            // Fallback default jika RajaOngkir gagal
            if ($shippingCost <= 0) {
                $totalQuantity = $cartItems->sum('quantity');
                $shippingCost = $expedition->base_cost + max(0, $totalQuantity - 1) * 1000;
            }

            $serviceFee = 2000;
            $grandTotal = $subtotal + $shippingCost + $serviceFee;

            $order = Order::create([
                'invoice_number' => 'INV-' . now()->format('YmdHis') . '-' . strtoupper(Str::random(5)),
                'user_id' => $user->id,
                'customer_address_id' => $address->id,
                'expedition_id' => $expedition->id,
                'subtotal' => $subtotal,
                'shipping_cost' => $shippingCost,
                'grand_total' => $grandTotal,
                'status' => Order::STATUS_PENDING_PAYMENT,
                'note' => $validated['note'] ?? null,
            ]);

            foreach ($cartItems as $item) {
                $product = Product::query()->where('id', $item->product_id)->lockForUpdate()->firstOrFail();

                $order->items()->create([
                    'product_id' => $product->id,
                    'product_name' => $product->name,
                    'size' => $item->size,
                    'color' => $item->color,
                    'price' => $product->price,
                    'quantity' => $item->quantity,
                    'total' => $product->price * $item->quantity,
                ]);

                $product->decrement('stock', $item->quantity, []);

                $product->stockMovements()->create([
                    'user_id' => $user->id,
                    'type' => 'out',
                    'quantity' => $item->quantity,
                    'reference' => $order->invoice_number,
                    'note' => 'Checkout customer',
                ]);
            }

            $paymentData = $midtransService->createSnapTransaction($order->invoice_number, $grandTotal, $validated['bank_code'] ?? null);

            $order->payment()->create([
                'bank_code' => $validated['bank_code'] ?? null,
                'virtual_account_number' => $paymentData['va_number'] ?? null,
                'biller_code' => $paymentData['biller_code'] ?? null,
                'amount' => $grandTotal,
                'status' => Payment::STATUS_WAITING_PAYMENT,
                'expired_at' => now()->addDay(),
                'external_reference' => $paymentData['transaction_id'] ?? 'VA-' . strtoupper(Str::random(12)),
                'snap_token' => $paymentData['snap_token'] ?? null,
                'snap_url' => $paymentData['snap_url'] ?? null,
            ]);

            $order->trackings()->create([
                'status' => Order::STATUS_PENDING_PAYMENT,
                'description' => 'Pesanan dibuat dan menunggu pembayaran.',
                'location' => $address->city,
            ]);

            // Hapus hanya item-item yang di-checkout dari keranjang
            $itemIdsToDestroy = $cartItems->pluck('id')->toArray();
            \App\Models\CartItem::destroy($itemIdsToDestroy);

            return $order;
        });

        return response()->json([
            'message' => 'Checkout berhasil.',
            'order' => $order->load(['items', 'payment', 'trackings', 'address', 'expedition']),
        ], 201);
    }
}
