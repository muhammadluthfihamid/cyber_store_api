<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Product;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class ProductController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $page = $request->input('page', 1);
        $perPage = $request->integer('per_page', 12);
        $categoryId = $request->input('category_id', '');
        $search = $request->input('search', '');
        $isRecommended = $request->has('is_recommended') ? $request->boolean('is_recommended') : '';
        $isEventMaba = $request->has('is_event_maba') ? $request->boolean('is_event_maba') : '';

        $searchHash = $search ? md5($search) : '';
        $cacheKey = "products:index:page_{$page}:per_page_{$perPage}:cat_{$categoryId}:search_{$searchHash}:rec_{$isRecommended}:maba_{$isEventMaba}";

        $products = Cache::remember($cacheKey, now()->addMinutes(5), function () use ($request) {
            return Product::query()
                ->with(['category', 'images'])
                ->where('is_active', true)
                ->when($request->filled('category_id'), fn($query) => $query->where('category_id', $request->category_id))
                ->when($request->filled('search'), fn($query) => $query->where('name', 'like', '%' . $request->search . '%'))
                ->when($request->has('is_recommended'), fn($query) => $query->where('is_recommended', $request->boolean('is_recommended')))
                ->when($request->has('is_event_maba'), fn($query) => $query->where('is_event_maba', $request->boolean('is_event_maba')))
                ->latest()
                ->paginate($request->integer('per_page', 12))
                ->toArray();
        });

        return response()->json($products);
    }

    public function show(int|string $id): JsonResponse
    {
        $product = Cache::remember("product:detail:{$id}", now()->addMinutes(10), function () use ($id) {
            $numericId = null;
            if (is_numeric($id)) {
                $numericId = (int) $id;
            } else {
                try {
                    $decrypted = \Illuminate\Support\Facades\Crypt::decryptString(urldecode((string) $id));
                    if (is_numeric($decrypted)) {
                        $numericId = (int) $decrypted;
                    }
                } catch (\Throwable) {
                    // Not encrypted string (e.g. slug)
                }
            }

            $prod = Product::with(['category', 'images'])
                ->where(function ($query) use ($numericId, $id) {
                    if ($numericId) {
                        $query->where('id', $numericId);
                    } else {
                        $query->where('slug', $id)->orWhere('id', $id);
                    }
                })
                ->first();

            if (! $prod || ! $prod->is_active) {
                return null;
            }

            return $prod->toArray();
        });

        if (! $product) {
            abort(404);
        }

        return response()->json([
            'product' => $product,
        ]);
    }
}
