<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\Product;
use App\Models\ProductReview;
use App\Models\ProductReviewReply;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class ProductReviewController extends Controller
{
    /**
     * Get reviews for a specific product.
     */
    public function index(Product $product): JsonResponse
    {
        $reviews = Cache::remember(
            "reviews:product:{$product->id}",
            now()->addMinutes(30),
            function () use ($product) {
                return ProductReview::with(['user:id,name,photo', 'replies.user:id,name,photo'])
                    ->where('product_id', $product->id)
                    ->latest()
                    ->get()
                    ->toArray();
            }
        );

        return response()->json($reviews);
    }

    /**
     * Submit a review for a product.
     */
    public function store(Request $request, Product $product): JsonResponse
    {
        $validated = $request->validate([
            'order_id' => ['required', 'exists:orders,id'],
            'rating' => ['required', 'integer', 'min:1', 'max:5'],
            'comment' => ['nullable', 'string', 'max:500'],
            'photo' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
            'photos' => ['nullable', 'array'],
            'photos.*' => ['image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
        ]);

        $user = $request->user();

        // Check if order exists, belongs to user, and is completed
        $order = Order::where('id', $validated['order_id'])
            ->where('user_id', $user->id)
            ->first();

        if (!$order) {
            return response()->json(['message' => 'Pesanan tidak ditemukan.'], 404);
        }

        if ($order->status !== Order::STATUS_COMPLETED) {
            return response()->json(['message' => 'Pesanan belum diselesaikan.'], 400);
        }

        // Verify the product is part of this order
        $orderItem = $order->items()->where('product_id', $product->id)->first();
        if (!$orderItem) {
            return response()->json(['message' => 'Produk tidak ditemukan dalam pesanan ini.'], 404);
        }

        // Check if review already exists
        $exists = ProductReview::where('order_id', $order->id)
            ->where('product_id', $product->id)
            ->exists();

        if ($exists) {
            return response()->json(['message' => 'Anda sudah memberikan ulasan untuk produk ini.'], 400);
        }

        $paths = [];

        // Handle single 'photo' upload
        if ($request->hasFile('photo')) {
            $paths[] = $request->file('photo')->store('reviews', 'public');
        }

        // Handle multiple 'photos' upload (e.g. photos[] from Flutter app)
        if ($request->hasFile('photos')) {
            foreach ($request->file('photos') as $file) {
                $paths[] = $file->store('reviews', 'public');
            }
        }

        $photoValue = null;
        if (!empty($paths)) {
            $photoValue = count($paths) === 1 ? $paths[0] : json_encode($paths);
        }

        // Create the review
        $review = ProductReview::create([
            'product_id' => $product->id,
            'user_id' => $user->id,
            'order_id' => $order->id,
            'rating' => $validated['rating'],
            'comment' => $validated['comment'] ?? null,
            'photo' => $photoValue,
            'is_read' => false,
        ]);

        // Recalculate average rating & reviews count
        $avgRating = ProductReview::where('product_id', $product->id)->avg('rating') ?? 4.8;
        $reviewsCount = ProductReview::where('product_id', $product->id)->count();

        $product->update([
            'rating' => round($avgRating, 1),
            'reviews_count' => $reviewsCount,
        ]);

        // Hapus cache review dan detail produk agar data terbaru langsung tampil
        Cache::forget("reviews:product:{$product->id}");
        Cache::forget("product:detail:{$product->id}");

        return response()->json([
            'message' => 'Ulasan berhasil dikirim.',
            'review' => $review->load('user:id,name,photo'),
        ], 201);
    }

    /**
     * Submit an admin reply to a review.
     */
    public function reply(Request $request, ProductReview $review): JsonResponse
    {
        if (!in_array($request->user()->role, ['admin', 'superadmin'])) {
            return response()->json(['message' => 'Hanya admin yang dapat membalas ulasan.'], 403);
        }

        $validated = $request->validate([
            'reply' => ['required', 'string', 'max:500'],
        ]);

        $reply = ProductReviewReply::create([
            'product_review_id' => $review->id,
            'user_id' => $request->user()->id,
            'reply' => $validated['reply'],
        ]);

        $review->update([
            'reply' => $validated['reply'],
        ]);

        // Hapus cache review produk agar data terbaru langsung tampil
        Cache::forget("reviews:product:{$review->product_id}");
        Cache::forget("product:detail:{$review->product_id}");

        return response()->json([
            'message' => 'Balasan berhasil dikirim.',
            'review' => $review->load(['user:id,name,photo', 'replies.user:id,name,photo']),
        ]);
    }

    /**
     * Get replies for a specific review.
     */
    public function replies(ProductReview $review): JsonResponse
    {
        $replies = $review->replies()->with('user:id,name,photo')->oldest()->get();
        return response()->json($replies);
    }

    /**
     * Get all reviews written by the authenticated user.
     */
    public function myReviews(Request $request): JsonResponse
    {
        $reviews = ProductReview::with(['product:id,name,main_photo', 'product.images'])
            ->where('user_id', $request->user()->id)
            ->latest()
            ->paginate($request->integer('per_page', 10));

        return response()->json($reviews);
    }
}
