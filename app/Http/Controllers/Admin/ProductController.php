<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Cache;

class ProductController extends Controller
{
    public static function flushRedisCache(): void
    {
        try {
            $redis = Cache::store('redis');
            $redis->increment('admin:products:version');
            if (method_exists($redis, 'tags')) {
                /** @var mixed $redis */
                $redis->tags(['products-list'])->flush();
            }
        } catch (\Throwable $e) {
            Cache::forget('admin:products:version');
        }
    }

    public function index(Request $request)
    {
        $search = trim((string)$request->query('search', ''));
        $category = $request->query('category');
        $status = $request->query('status');
        $stockStatus = $request->query('stock_status');
        $event = $request->query('event');
        $page = (int)$request->query('page', 1);

        try {
            $version = Cache::store('redis')->get('admin:products:version', 1);
            $cacheKey = "admin:products:v{$version}:" . md5(json_encode([
                'search' => $search,
                'category' => $category,
                'status' => $status,
                'stock_status' => $stockStatus,
                'event' => $event,
                'page' => $page,
            ]));

            $cachedData = Cache::store('redis')->remember($cacheKey, now()->addMinutes(30), function () use ($search, $category, $status, $stockStatus, $event) {
                $query = Product::query();

                if ($search !== '') {
                    $query->where(function ($q) use ($search) {
                        $q->where('name', 'like', "%{$search}%")
                            ->orWhere('sku', 'like', "%{$search}%")
                            ->orWhere('description', 'like', "%{$search}%");
                    });
                }

                if ($category !== null && $category !== '') {
                    $query->where('category_id', $category);
                }

                if ($status !== null && $status !== '') {
                    $query->where('is_active', $status === 'active');
                }

                if ($event === 'maba') {
                    $query->where('is_event_maba', true);
                }

                if ($stockStatus === 'out_of_stock') {
                    $query->where('stock', '<=', 0);
                } elseif ($stockStatus === 'low_stock') {
                    $query->where('stock', '>', 0)->where('stock', '<', 10);
                }

                $paginator = $query->latest('id')->paginate(15);
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
                : Product::with('category')
                    ->whereIn('id', $ids)
                    ->get()
                    ->sortBy(fn ($p) => array_search($p->id, $ids))
                    ->values();

            $products = new LengthAwarePaginator(
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
            $query = Product::with('category');

            if ($search !== '') {
                $query->where(function ($q) use ($search) {
                    $q->where('name', 'like', "%{$search}%")
                        ->orWhere('sku', 'like', "%{$search}%")
                        ->orWhere('description', 'like', "%{$search}%");
                });
            }

            if ($category !== null && $category !== '') {
                $query->where('category_id', $category);
            }

            if ($status !== null && $status !== '') {
                $query->where('is_active', $status === 'active');
            }

            if ($stockStatus === 'out_of_stock') {
                $query->where('stock', '<=', 0);
            } elseif ($stockStatus === 'low_stock') {
                $query->where('stock', '>', 0)->where('stock', '<', 10);
            }

            $products = $query->latest()->paginate(15)->withQueryString();
        }

        $categories = Category::query()->where('is_active', true)->get();
        $outOfStockCount = Product::query()->where('stock', '<=', 0)->count();
        $lowStockCount = Product::query()->where('stock', '>', 0)->where('stock', '<', 10)->count();

        return view('admin.products.index', compact('products', 'categories', 'outOfStockCount', 'lowStockCount'));
    }

    public function suggestions(Request $request)
    {
        $q = trim((string)$request->query('q', ''));

        try {
            $version = Cache::store('redis')->get('admin:products:version', 1);
            $cacheKey = "admin:products:suggestions:v{$version}:" . md5($q);

            $results = Cache::store('redis')->remember($cacheKey, now()->addMinutes(15), function () use ($q) {
                $query = Product::with('category')->latest();
                if ($q !== '') {
                    $query->where(function ($w) use ($q) {
                        $w->where('name', 'like', "%{$q}%")
                          ->orWhere('sku', 'like', "%{$q}%")
                          ->orWhere('description', 'like', "%{$q}%");
                    });
                }
                return $query->take(8)->get()->map(function ($p) {
                    $catName = $p->category?->name ?? 'Uncategorized';
                    $priceFormatted = 'Rp ' . number_format($p->price, 0, ',', '.');
                    $stockText = 'Stok: ' . $p->stock;

                    return [
                        'id' => $p->id,
                        'title' => $p->name,
                        'subtitle' => "SKU: {$p->sku} • {$catName} • {$priceFormatted}",
                        'value' => $p->name,
                        'badge' => $p->is_active ? $stockText : 'Nonaktif',
                        'badge_color' => $p->is_active ? ($p->stock > 0 ? '#10B981' : '#EF4444') : '#64748B',
                        'icon' => 'flat-color-icons:box',
                    ];
                });
            });
        } catch (\Throwable $e) {
            $query = Product::with('category')->latest();
            if ($q !== '') {
                $query->where(function ($w) use ($q) {
                    $w->where('name', 'like', "%{$q}%")
                      ->orWhere('sku', 'like', "%{$q}%")
                      ->orWhere('description', 'like', "%{$q}%");
                });
            }
            $results = $query->take(8)->get()->map(function ($p) {
                $catName = $p->category?->name ?? 'Uncategorized';
                $priceFormatted = 'Rp ' . number_format($p->price, 0, ',', '.');
                $stockText = 'Stok: ' . $p->stock;

                return [
                    'id' => $p->id,
                    'title' => $p->name,
                    'subtitle' => "SKU: {$p->sku} • {$catName} • {$priceFormatted}",
                    'value' => $p->name,
                    'badge' => $p->is_active ? $stockText : 'Nonaktif',
                    'badge_color' => $p->is_active ? ($p->stock > 0 ? '#10B981' : '#EF4444') : '#64748B',
                    'icon' => 'flat-color-icons:box',
                ];
            });
        }

        return response()->json($results);
    }

    public function create()
    {
        $categories = Category::query()->where('is_active', true)->get();
        return view('admin.products.create', compact('categories'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'category_id'    => ['required', 'exists:categories,id'],
            'name'           => ['required', 'string', 'max:200'],
            'sku'            => ['nullable', 'string', 'max:50', 'unique:products,sku'],
            'description'    => ['nullable', 'string'],
            'price'          => ['required', 'numeric', 'min:0'],
            'original_price' => ['nullable', 'numeric', 'min:0'],
            'stock'          => ['required', 'integer', 'min:0'],
            'weight'         => ['required', 'integer', 'min:1'],
            'rating'         => ['nullable', 'numeric', 'min:0', 'max:5'],
            'sizes'          => ['nullable'],
            'colors'         => ['nullable'],
            'is_active'      => ['boolean'],
            'is_recommended' => ['boolean'],
            'is_event_maba'  => ['boolean'],
            'maba_color_ganjil' => ['nullable', 'string', 'max:50'],
            'maba_color_genap'  => ['nullable', 'string', 'max:50'],
            'size_chart'     => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
            'main_photo'     => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
            'photo_2'        => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
            'photo_3'        => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
            'photo_4'        => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
            'photo_5'        => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
            'photo_6'        => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
        ], [
            'main_photo.max'   => 'Ukuran Foto Utama tidak boleh lebih dari 2MB.',
            'main_photo.image' => 'Berkas Foto Utama harus berupa file gambar.',
            'main_photo.mimes' => 'Format Foto Utama harus jpg, jpeg, png, atau webp.',
            'size_chart.max'   => 'Ukuran Foto Panduan Ukuran tidak boleh lebih dari 2MB.',
            'size_chart.image' => 'Berkas Panduan Ukuran harus berupa file gambar.',
            'size_chart.mimes' => 'Format Panduan Ukuran harus jpg, jpeg, png, atau webp.',
            'photo_2.max'      => 'Ukuran Foto 2 tidak boleh lebih dari 2MB.',
            'photo_2.image'    => 'Berkas Foto 2 harus berupa file gambar.',
            'photo_2.mimes'    => 'Format Foto 2 harus jpg, jpeg, png, atau webp.',
            'photo_3.max'      => 'Ukuran Foto 3 tidak boleh lebih dari 2MB.',
            'photo_3.image'    => 'Berkas Foto 3 harus berupa file gambar.',
            'photo_3.mimes'    => 'Format Foto 3 harus jpg, jpeg, png, atau webp.',
            'photo_4.max'      => 'Ukuran Foto 4 tidak boleh lebih dari 2MB.',
            'photo_4.image'    => 'Berkas Foto 4 harus berupa file gambar.',
            'photo_4.mimes'    => 'Format Foto 4 harus jpg, jpeg, png, atau webp.',
            'photo_5.max'      => 'Ukuran Foto 5 tidak boleh lebih dari 2MB.',
            'photo_5.image'    => 'Berkas Foto 5 harus berupa file gambar.',
            'photo_5.mimes'    => 'Format Foto 5 harus jpg, jpeg, png, atau webp.',
            'photo_6.max'      => 'Ukuran Foto 6 tidak boleh lebih dari 2MB.',
            'photo_6.image'    => 'Berkas Foto 6 harus berupa file gambar.',
            'photo_6.mimes'    => 'Format Foto 6 harus jpg, jpeg, png, atau webp.',
        ]);

        $slug = Str::slug($validated['name']);
        $baseSlug = $slug;
        $counter = 1;
        while (Product::query()->where('slug', $slug)->exists()) {
            $slug = $baseSlug . '-' . $counter++;
        }

        // Parse sizes & colors dari input form (array atau comma-separated string)
        $sizes = [];
        if ($request->boolean('has_sizes', true) && !empty($validated['sizes'])) {
            $sizeInput = is_array($validated['sizes']) ? $validated['sizes'] : explode(',', $validated['sizes']);
            $sizes = array_filter(array_map(function ($item) {
                return is_string($item) ? trim($item) : (is_array($item) ? ($item['name'] ?? '') : '');
            }, $sizeInput));
        }

        $colors = [];
        if (!empty($validated['colors'])) {
            $colorInput = is_array($validated['colors']) ? $validated['colors'] : explode(',', $validated['colors']);
            foreach ($colorInput as $item) {
                if (is_string($item)) {
                    $colorName = trim($item);
                    if ($colorName !== '') {
                        $colors[] = ['name' => $colorName, 'hex' => '#000000'];
                    }
                } elseif (is_array($item)) {
                    $colorName = trim($item['name'] ?? '');
                    $colorHex = $item['hex'] ?? '#000000';
                    if ($colorName !== '') {
                        $colors[] = ['name' => $colorName, 'hex' => $colorHex];
                    }
                }
            }
        }

        $mainPhotoPath = null;
        if ($request->hasFile('main_photo')) {
            $mainPhotoPath = $request->file('main_photo')->store('products', 'public');
        }

        $sizeChartPath = null;
        if (!empty($sizes) && $request->hasFile('size_chart')) {
            $sizeChartPath = $request->file('size_chart')->store('products/size_charts', 'public');
        }

        $rating = isset($validated['rating']) && $validated['rating'] !== null && $validated['rating'] !== '' ? (float) $validated['rating'] : 0.0;

        $product = Product::create([
            'category_id'    => $validated['category_id'],
            'name'           => $validated['name'],
            'slug'           => $slug,
            'sku'            => $validated['sku'] ?? strtoupper(Str::random(8)),
            'description'    => $validated['description'] ?? '',
            'price'          => $validated['price'],
            'original_price' => $validated['original_price'] ?? null,
            'stock'          => $validated['stock'],
            'weight'         => $validated['weight'],
            'sizes'          => array_values($sizes),
            'colors'         => $colors,
            'size_chart'     => $sizeChartPath,
            'is_active'      => $request->boolean('is_active', true),
            'is_recommended' => $request->boolean('is_recommended', false),
            'is_event_maba'  => $request->boolean('is_event_maba', false),
            'maba_color_ganjil' => $request->boolean('is_event_maba') ? $request->input('maba_color_ganjil', 'Putih') : null,
            'maba_color_genap'  => $request->boolean('is_event_maba') ? $request->input('maba_color_genap', 'Biru') : null,
            'main_photo'     => $mainPhotoPath,
            'rating'         => $rating,
            'reviews_count'  => 0,
        ]);

        if ($request->hasFile('photo_2')) {
            $path2 = $request->file('photo_2')->store('products', 'public');
            $product->images()->create([
                'image' => $path2,
                'sort_order' => 1,
            ]);
        }

        if ($request->hasFile('photo_3')) {
            $path3 = $request->file('photo_3')->store('products', 'public');
            $product->images()->create([
                'image' => $path3,
                'sort_order' => 2,
            ]);
        }

        if ($request->hasFile('photo_4')) {
            $path4 = $request->file('photo_4')->store('products', 'public');
            $product->images()->create([
                'image' => $path4,
                'sort_order' => 3,
            ]);
        }

        if ($request->hasFile('photo_5')) {
            $path5 = $request->file('photo_5')->store('products', 'public');
            $product->images()->create([
                'image' => $path5,
                'sort_order' => 4,
            ]);
        }

        if ($request->hasFile('photo_6')) {
            $path6 = $request->file('photo_6')->store('products', 'public');
            $product->images()->create([
                'image' => $path6,
                'sort_order' => 5,
            ]);
        }

        Product::clearCache($product);

        return redirect()->route('admin.products.index')->with('success', 'Produk berhasil ditambahkan.');
    }

    public function show(Product $product)
    {
        $product->load(['category', 'images', 'reviews.user']);
        if (request()->wantsJson() || request()->ajax()) {
            return response()->json([
                'success' => true,
                'data'    => $product,
            ]);
        }
        return view('admin.products.show', compact('product'));
    }

    public function edit(Product $product)
    {
        $categories = Category::query()->where('is_active', true)->get();
        return view('admin.products.edit', compact('product', 'categories'));
    }

    public function update(Request $request, Product $product)
    {
        $validated = $request->validate([
            'category_id'    => ['required', 'exists:categories,id'],
            'name'           => ['required', 'string', 'max:200'],
            'sku'            => ['nullable', 'string', 'max:50', \Illuminate\Validation\Rule::unique('products', 'sku')->ignore($product->id)],
            'description'    => ['nullable', 'string'],
            'price'          => ['required', 'numeric', 'min:0'],
            'original_price' => ['nullable', 'numeric', 'min:0'],
            'stock'          => ['required', 'integer', 'min:0'],
            'weight'         => ['required', 'integer', 'min:1'],
            'rating'         => ['nullable', 'numeric', 'min:0', 'max:5'],
            'sizes'          => ['nullable'],
            'colors'         => ['nullable'],
            'is_active'      => ['boolean'],
            'is_recommended' => ['boolean'],
            'is_event_maba'  => ['boolean'],
            'maba_color_ganjil' => ['nullable', 'string', 'max:50'],
            'maba_color_genap'  => ['nullable', 'string', 'max:50'],
            'size_chart'     => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
            'remove_size_chart' => ['nullable', 'boolean'],
            'main_photo'     => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
            'photo_2'        => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
            'photo_3'        => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
            'photo_4'        => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
            'photo_5'        => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
            'photo_6'        => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
            'remove_photo_2' => ['nullable', 'boolean'],
            'remove_photo_3' => ['nullable', 'boolean'],
            'remove_photo_4' => ['nullable', 'boolean'],
            'remove_photo_5' => ['nullable', 'boolean'],
            'remove_photo_6' => ['nullable', 'boolean'],
        ], [
            'main_photo.max'   => 'Ukuran Foto Utama tidak boleh lebih dari 2MB.',
            'main_photo.image' => 'Berkas Foto Utama harus berupa file gambar.',
            'main_photo.mimes' => 'Format Foto Utama harus jpg, jpeg, png, atau webp.',
            'size_chart.max'   => 'Ukuran Foto Panduan Ukuran tidak boleh lebih dari 2MB.',
            'size_chart.image' => 'Berkas Panduan Ukuran harus berupa file gambar.',
            'size_chart.mimes' => 'Format Panduan Ukuran harus jpg, jpeg, png, atau webp.',
            'photo_2.max'      => 'Ukuran Foto 2 tidak boleh lebih dari 2MB.',
            'photo_2.image'    => 'Berkas Foto 2 harus berupa file gambar.',
            'photo_2.mimes'    => 'Format Foto 2 harus jpg, jpeg, png, atau webp.',
            'photo_3.max'      => 'Ukuran Foto 3 tidak boleh lebih dari 2MB.',
            'photo_3.image'    => 'Berkas Foto 3 harus berupa file gambar.',
            'photo_3.mimes'    => 'Format Foto 3 harus jpg, jpeg, png, atau webp.',
            'photo_4.max'      => 'Ukuran Foto 4 tidak boleh lebih dari 2MB.',
            'photo_4.image'    => 'Berkas Foto 4 harus berupa file gambar.',
            'photo_4.mimes'    => 'Format Foto 4 harus jpg, jpeg, png, atau webp.',
            'photo_5.max'      => 'Ukuran Foto 5 tidak boleh lebih dari 2MB.',
            'photo_5.image'    => 'Berkas Foto 5 harus berupa file gambar.',
            'photo_5.mimes'    => 'Format Foto 5 harus jpg, jpeg, png, atau webp.',
            'photo_6.max'      => 'Ukuran Foto 6 tidak boleh lebih dari 2MB.',
            'photo_6.image'    => 'Berkas Foto 6 harus berupa file gambar.',
            'photo_6.mimes'    => 'Format Foto 6 harus jpg, jpeg, png, atau webp.',
        ]);

        // Update slug hanya jika nama berubah
        $slug = $product->slug;
        if ($product->name !== $validated['name']) {
            $slug = Str::slug($validated['name']);
            $baseSlug = $slug;
            $counter = 1;
            while (Product::query()->where('slug', $slug)->where('id', '!=', $product->id)->exists()) {
                $slug = $baseSlug . '-' . $counter++;
            }
        }

        // Parse sizes & colors dari input form (array atau comma-separated string)
        $sizes = [];
        if ($request->boolean('has_sizes', true) && !empty($validated['sizes'])) {
            $sizeInput = is_array($validated['sizes']) ? $validated['sizes'] : explode(',', $validated['sizes']);
            $sizes = array_filter(array_map(function ($item) {
                return is_string($item) ? trim($item) : (is_array($item) ? ($item['name'] ?? '') : '');
            }, $sizeInput));
        }

        $colors = [];
        if (!empty($validated['colors'])) {
            $colorInput = is_array($validated['colors']) ? $validated['colors'] : explode(',', $validated['colors']);
            foreach ($colorInput as $item) {
                if (is_string($item)) {
                    $colorName = trim($item);
                    if ($colorName !== '') {
                        $colors[] = ['name' => $colorName, 'hex' => '#000000'];
                    }
                } elseif (is_array($item)) {
                    $colorName = trim($item['name'] ?? '');
                    $colorHex = $item['hex'] ?? '#000000';
                    if ($colorName !== '') {
                        $colors[] = ['name' => $colorName, 'hex' => $colorHex];
                    }
                }
            }
        }

        $rating = isset($validated['rating']) && $validated['rating'] !== null && $validated['rating'] !== '' ? (float) $validated['rating'] : $product->rating;

        $data = [
            'category_id'    => $validated['category_id'],
            'name'           => $validated['name'],
            'slug'           => $slug,
            'sku'            => $validated['sku'] ?? $product->sku,
            'description'    => $validated['description'] ?? '',
            'price'          => $validated['price'],
            'original_price' => $validated['original_price'] ?? null,
            'stock'          => $validated['stock'],
            'weight'         => $validated['weight'],
            'rating'         => $rating,
            'sizes'          => array_values($sizes),
            'colors'         => $colors,
            'is_active'      => $request->boolean('is_active'),
            'is_recommended' => $request->boolean('is_recommended'),
            'is_event_maba'  => $request->boolean('is_event_maba'),
            'maba_color_ganjil' => $request->boolean('is_event_maba') ? $request->input('maba_color_ganjil', 'Putih') : null,
            'maba_color_genap'  => $request->boolean('is_event_maba') ? $request->input('maba_color_genap', 'Biru') : null,
        ];

        if ($request->hasFile('main_photo')) {
            if ($product->main_photo) {
                \Illuminate\Support\Facades\Storage::disk('public')->delete($product->main_photo);
            }
            $data['main_photo'] = $request->file('main_photo')->store('products', 'public');
        } elseif ($request->boolean('remove_main_photo') && $product->main_photo) {
            \Illuminate\Support\Facades\Storage::disk('public')->delete($product->main_photo);
            $data['main_photo'] = null;
        }

        if (empty($sizes)) {
            // Jika produk tidak memiliki ukuran, otomatis hapus file panduan ukuran jika ada
            if ($product->size_chart) {
                \Illuminate\Support\Facades\Storage::disk('public')->delete($product->size_chart);
            }
            $data['size_chart'] = null;
        } elseif ($request->hasFile('size_chart')) {
            if ($product->size_chart) {
                \Illuminate\Support\Facades\Storage::disk('public')->delete($product->size_chart);
            }
            $data['size_chart'] = $request->file('size_chart')->store('products/size_charts', 'public');
        } elseif ($request->boolean('remove_size_chart') && $product->size_chart) {
            \Illuminate\Support\Facades\Storage::disk('public')->delete($product->size_chart);
            $data['size_chart'] = null;
        }

        $product->update($data);

        // Handle Photo 2
        if ($request->boolean('remove_photo_2')) {
            $oldImage = $product->images()->where('sort_order', 1)->first();
            if ($oldImage) {
                \Illuminate\Support\Facades\Storage::disk('public')->delete($oldImage->image);
                $oldImage->delete();
            }
        } elseif ($request->hasFile('photo_2')) {
            $oldImage = $product->images()->where('sort_order', 1)->first();
            if ($oldImage) {
                \Illuminate\Support\Facades\Storage::disk('public')->delete($oldImage->image);
                $oldImage->delete();
            }

            $path2 = $request->file('photo_2')->store('products', 'public');
            $product->images()->create([
                'image' => $path2,
                'sort_order' => 1,
            ]);
        }

        // Handle Photo 3
        if ($request->boolean('remove_photo_3')) {
            $oldImage = $product->images()->where('sort_order', 2)->first();
            if ($oldImage) {
                \Illuminate\Support\Facades\Storage::disk('public')->delete($oldImage->image);
                $oldImage->delete();
            }
        } elseif ($request->hasFile('photo_3')) {
            $oldImage = $product->images()->where('sort_order', 2)->first();
            if ($oldImage) {
                \Illuminate\Support\Facades\Storage::disk('public')->delete($oldImage->image);
                $oldImage->delete();
            }

            $path3 = $request->file('photo_3')->store('products', 'public');
            $product->images()->create([
                'image' => $path3,
                'sort_order' => 2,
            ]);
        }

        // Handle Photo 4
        if ($request->boolean('remove_photo_4')) {
            $oldImage = $product->images()->where('sort_order', 3)->first();
            if ($oldImage) {
                \Illuminate\Support\Facades\Storage::disk('public')->delete($oldImage->image);
                $oldImage->delete();
            }
        } elseif ($request->hasFile('photo_4')) {
            $oldImage = $product->images()->where('sort_order', 3)->first();
            if ($oldImage) {
                \Illuminate\Support\Facades\Storage::disk('public')->delete($oldImage->image);
                $oldImage->delete();
            }

            $path4 = $request->file('photo_4')->store('products', 'public');
            $product->images()->create([
                'image' => $path4,
                'sort_order' => 3,
            ]);
        }

        // Handle Photo 5
        if ($request->boolean('remove_photo_5')) {
            $oldImage = $product->images()->where('sort_order', 4)->first();
            if ($oldImage) {
                \Illuminate\Support\Facades\Storage::disk('public')->delete($oldImage->image);
                $oldImage->delete();
            }
        } elseif ($request->hasFile('photo_5')) {
            $oldImage = $product->images()->where('sort_order', 4)->first();
            if ($oldImage) {
                \Illuminate\Support\Facades\Storage::disk('public')->delete($oldImage->image);
                $oldImage->delete();
            }

            $path5 = $request->file('photo_5')->store('products', 'public');
            $product->images()->create([
                'image' => $path5,
                'sort_order' => 4,
            ]);
        }

        // Handle Photo 6
        if ($request->boolean('remove_photo_6')) {
            $oldImage = $product->images()->where('sort_order', 5)->first();
            if ($oldImage) {
                \Illuminate\Support\Facades\Storage::disk('public')->delete($oldImage->image);
                $oldImage->delete();
            }
        } elseif ($request->hasFile('photo_6')) {
            $oldImage = $product->images()->where('sort_order', 5)->first();
            if ($oldImage) {
                \Illuminate\Support\Facades\Storage::disk('public')->delete($oldImage->image);
                $oldImage->delete();
            }

            $path6 = $request->file('photo_6')->store('products', 'public');
            $product->images()->create([
                'image' => $path6,
                'sort_order' => 5,
            ]);
        }

        Product::clearCache($product);

        return redirect()->route('admin.products.index')->with('success', 'Produk berhasil diperbarui.');
    }

    public function destroy(Product $product)
    {
        if ($product->orderItems()->exists()) {
            return back()->with('error', 'Produk tidak dapat dihapus karena sudah memiliki riwayat transaksi/order. Anda dapat menonaktifkan produk ini saja.');
        }

        if ($product->main_photo) {
            \Illuminate\Support\Facades\Storage::disk('public')->delete($product->main_photo);
        }
        $product->delete();
        return redirect()->route('admin.products.index')->with('success', 'Produk berhasil dihapus.');
    }

    public function bulkDestroy(Request $request)
    {
        $request->validate([
            'ids' => ['required', 'array'],
            'ids.*' => ['exists:products,id'],
        ]);

        $products = Product::query()->whereIn('id', $request->ids, 'and', false)->get();
        $deletedCount = 0;
        $skippedCount = 0;

        foreach ($products as $product) {
            if ($product->orderItems()->exists()) {
                $skippedCount++;
                continue;
            }

            if ($product->main_photo) {
                \Illuminate\Support\Facades\Storage::disk('public')->delete($product->main_photo);
            }

            // Optional: delete additional images from storage
            foreach ($product->images as $img) {
                \Illuminate\Support\Facades\Storage::disk('public')->delete($img->image);
                $img->delete();
            }

            $product->delete();
            $deletedCount++;
        }

        Product::clearCache();

        if ($deletedCount === 0) {
            return redirect()->route('admin.products.index')
                ->with('error', 'Tidak ada produk yang berhasil dihapus karena semua produk pilihan memiliki riwayat transaksi/order.');
        }

        if ($skippedCount > 0) {
            return redirect()->route('admin.products.index')
                ->with('success', "Berhasil menghapus {$deletedCount} produk. {$skippedCount} produk dilewati karena memiliki riwayat transaksi/order.");
        }

        return redirect()->route('admin.products.index')
            ->with('success', "Berhasil menghapus {$deletedCount} produk terpilih.");
    }

    public function toggleActive(Product $product)
    {
        $product->update(['is_active' => !$product->is_active]);
        $status = $product->is_active ? 'diaktifkan' : 'dinonaktifkan';
        return back()->with('success', "Produk {$product->name} berhasil {$status}.");
    }

    public function import(Request $request)
    {
        $request->validate([
            'file' => ['required', 'file', 'max:10240'],
            'images.*' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:5120'],
            'images_zip' => ['nullable', 'file', 'mimes:zip', 'max:51200'],
        ], [
            'file.required' => 'File impor wajib dipilih.',
            'file.max' => 'Ukuran file data maksimal adalah 10 MB.',
            'images.*.image' => 'Setiap berkas gambar harus berformat gambar (jpg, jpeg, png, webp).',
            'images.*.max' => 'Ukuran setiap gambar maksimal adalah 5 MB.',
            'images_zip.mimes' => 'Berkas kompresi gambar harus berformat .zip.',
            'images_zip.max' => 'Ukuran file ZIP maksimal adalah 50 MB.',
        ]);

        // ── Process Uploaded Images (from images[] or images_zip) ──────────────
        $uploadedImagesMap = [];

        // 1. Process multiple image uploads (images[])
        if ($request->hasFile('images')) {
            foreach ($request->file('images') as $imgFile) {
                if ($imgFile && $imgFile->isValid()) {
                    $origName = $imgFile->getClientOriginalName();
                    $path = $imgFile->storeAs('products', $origName, 'public');

                    $uploadedImagesMap[strtolower($origName)] = $path;
                    $uploadedImagesMap[strtolower(basename($origName))] = $path;
                    $uploadedImagesMap[strtolower(pathinfo($origName, PATHINFO_FILENAME))] = $path;
                }
            }
        }

        // 2. Process ZIP file upload (images_zip)
        if ($request->hasFile('images_zip')) {
            $zipFile = $request->file('images_zip');
            if ($zipFile && $zipFile->isValid() && class_exists('\ZipArchive')) {
                $zip = new \ZipArchive();
                if ($zip->open($zipFile->getRealPath()) === true) {
                    for ($i = 0; $i < $zip->numFiles; $i++) {
                        $stat = $zip->statIndex($i);
                        $entryName = $stat['name'];
                        $ext = strtolower(pathinfo($entryName, PATHINFO_EXTENSION));

                        if (in_array($ext, ['jpg', 'jpeg', 'png', 'webp', 'gif'])) {
                            $baseName = basename($entryName);
                            $content = $zip->getFromIndex($i);

                            if (!empty($content) && !empty($baseName)) {
                                $targetPath = 'products/' . $baseName;
                                \Illuminate\Support\Facades\Storage::disk('public')->put($targetPath, $content);

                                $uploadedImagesMap[strtolower($baseName)] = $targetPath;
                                $uploadedImagesMap[strtolower(pathinfo($baseName, PATHINFO_FILENAME))] = $targetPath;
                            }
                        }
                    }
                    $zip->close();
                }
            }
        }

        $file = $request->file('file');
        $extension = strtolower($file->getClientOriginalExtension());
        if (!in_array($extension, ['csv', 'txt', 'xls', 'xlsx'])) {
            return back()->with('error', 'Format file tidak didukung. Harap unggah file .csv, .xls, .xlsx, atau .txt.');
        }

        $path = $file->getRealPath();
        $allRows = $this->parseRowsFromFile($path);

        if (empty($allRows)) {
            return back()->with('error', 'File yang diunggah kosong atau format biner .xlsx tidak dapat dibaca langsung. Silakan pilih "Save As" -> CSV (Comma Delimited) (*.csv) di Excel, atau unggah langsung file template .xls yang telah disediakan.');
        }

        // Extract header row
        $headerRow = array_shift($allRows);

        // Clean BOM and non-alphanumeric characters from header strings
        $cleanHeaders = array_map(function ($col) {
            $cleaned = preg_replace('/[\x00-\x1F\x7F\xEF\xBB\xBF\xFE\xFF]/u', '', (string) $col);
            return strtolower(trim(preg_replace('/[^a-zA-Z0-9]/', '', $cleaned)));
        }, $headerRow);

        $findCol = function (array $keywords, int $defaultIndex) use ($cleanHeaders) {
            foreach ($cleanHeaders as $idx => $h) {
                foreach ($keywords as $kw) {
                    if ($h === $kw || str_contains($h, $kw)) {
                        return $idx;
                    }
                }
            }
            return $defaultIndex;
        };

        // Expected columns mapping with flexible keyword matching and position fallback
        $colMap = [
            'kategori' => $findCol(['kategori', 'category', 'cat'], 0),
            'nama' => $findCol(['nama', 'name', 'produk', 'product'], 1),
            'sku' => $findCol(['sku', 'kode', 'code'], 2),
            'deskripsi' => $findCol(['deskripsi', 'description', 'desc', 'keterangan'], 3),
            'harga' => $findCol(['harga', 'price', 'jual'], 4),
            'harga_coret' => $findCol(['hargacoret', 'hargaasli', 'originalprice', 'discount'], 5),
            'stok' => $findCol(['stok', 'stock', 'qty', 'jumlah'], 6),
            'berat' => $findCol(['berat', 'weight', 'gram'], 7),
            'ukuran' => $findCol(['ukuran', 'sizes', 'size'], 8),
            'warna' => $findCol(['warna', 'colors', 'color'], 9),
            'foto_utama' => $findCol(['fotoutama', 'mainphoto', 'foto', 'image', 'photo'], 10),
        ];

        // Validate header structure (must at least have name, price, stock, category)
        if ($colMap['nama'] === -1 || $colMap['harga'] === -1 || $colMap['stok'] === -1 || $colMap['kategori'] === -1) {
            $readableHeaders = implode(', ', array_filter(array_map('trim', $headerRow)));
            return back()->with('error', "Format header file salah. Header yang terbaca: \"{$readableHeaders}\". Pastikan terdapat kolom: Kategori, Nama, Harga, dan Stok.");
        }

        $importedCount = 0;
        $skippedCount = 0;
        $errors = [];
        $rowNum = 1;

        foreach ($allRows as $data) {
            $rowNum++;

            // Ignore empty rows
            if (array_filter($data) === []) {
                continue;
            }

            // Get values based on column mapping
            $categoryName = $colMap['kategori'] !== false && isset($data[$colMap['kategori']]) ? trim($data[$colMap['kategori']]) : '';
            $name = $colMap['nama'] !== false && isset($data[$colMap['nama']]) ? trim($data[$colMap['nama']]) : '';
            $sku = $colMap['sku'] !== false && isset($data[$colMap['sku']]) && trim($data[$colMap['sku']]) !== '' ? trim($data[$colMap['sku']]) : null;
            $description = $colMap['deskripsi'] !== false && isset($data[$colMap['deskripsi']]) ? trim($data[$colMap['deskripsi']]) : null;
            $price = $colMap['harga'] !== false && isset($data[$colMap['harga']]) ? floatval(str_replace(['.', ','], ['', '.'], trim($data[$colMap['harga']]))) : 0;
            $originalPrice = $colMap['harga_coret'] !== false && isset($data[$colMap['harga_coret']]) && trim($data[$colMap['harga_coret']]) !== ''
                ? floatval(str_replace(['.', ','], ['', '.'], trim($data[$colMap['harga_coret']]))) : null;
            $stock = $colMap['stok'] !== false && isset($data[$colMap['stok']]) ? intval(trim($data[$colMap['stok']])) : 0;
            $weight = $colMap['berat'] !== false && isset($data[$colMap['berat']]) ? intval(trim($data[$colMap['berat']])) : 100;
            $sizesStr = $colMap['ukuran'] !== false && isset($data[$colMap['ukuran']]) ? trim($data[$colMap['ukuran']]) : '';
            $colorsStr = $colMap['warna'] !== false && isset($data[$colMap['warna']]) ? trim($data[$colMap['warna']]) : '';
            
            // Photo mapping
            $rawPhoto = $colMap['foto_utama'] !== false && isset($data[$colMap['foto_utama']]) && trim($data[$colMap['foto_utama']]) !== ''
                ? trim($data[$colMap['foto_utama']])
                : '';

            $mainPhoto = 'products/default.jpg';

            if (!empty($rawPhoto)) {
                $cleanRawName = strtolower(trim(basename($rawPhoto)));
                $rawFileNameWithoutExt = strtolower(pathinfo($cleanRawName, PATHINFO_FILENAME));

                if (isset($uploadedImagesMap[$cleanRawName])) {
                    $mainPhoto = $uploadedImagesMap[$cleanRawName];
                } elseif (isset($uploadedImagesMap[$rawFileNameWithoutExt])) {
                    $mainPhoto = $uploadedImagesMap[$rawFileNameWithoutExt];
                } elseif (\Illuminate\Support\Facades\Storage::disk('public')->exists('products/' . basename($rawPhoto))) {
                    $mainPhoto = 'products/' . basename($rawPhoto);
                } elseif (\Illuminate\Support\Facades\Storage::disk('public')->exists($rawPhoto)) {
                    $mainPhoto = $rawPhoto;
                } else {
                    if (str_starts_with($rawPhoto, 'http://') || str_starts_with($rawPhoto, 'https://')) {
                        $mainPhoto = $rawPhoto;
                    } elseif (str_contains($rawPhoto, '/')) {
                        $mainPhoto = $rawPhoto;
                    } else {
                        $mainPhoto = 'products/' . $rawPhoto;
                    }
                }
            }

            // Basic validation
            if (empty($name) || empty($categoryName) || $price <= 0) {
                $errors[] = "Baris $rowNum: Nama, Kategori, atau Harga tidak boleh kosong/nol. Diabaikan.";
                $skippedCount++;
                continue;
            }

            // Check unique SKU
            if (!empty($sku) && Product::query()->where('sku', $sku)->exists()) {
                $errors[] = "Baris $rowNum: SKU '$sku' sudah digunakan oleh produk lain. Diabaikan.";
                $skippedCount++;
                continue;
            }

            // Check/create category
            $category = Category::firstOrCreate(
                ['slug' => Str::slug($categoryName)],
                ['name' => $categoryName, 'description' => 'Kategori ' . $categoryName, 'is_active' => true]
            );

            // Generate slug
            $slug = Str::slug($name);
            $baseSlug = $slug;
            $counter = 1;
            while (Product::query()->where('slug', $slug)->exists()) {
                $slug = $baseSlug . '-' . $counter++;
            }

            // Parse sizes
            $sizes = [];
            if (!empty($sizesStr)) {
                $sizes = array_filter(array_map('trim', explode(';', $sizesStr)));
            }

            // Parse colors
            $colors = [];
            if (!empty($colorsStr)) {
                $colorNames = array_filter(array_map('trim', explode(';', $colorsStr)));
                foreach ($colorNames as $cName) {
                    $colors[] = ['name' => $cName, 'hex' => '#000000'];
                }
            }

            // Save product
            Product::create([
                'category_id' => $category->id,
                'name' => $name,
                'slug' => $slug,
                'sku' => $sku,
                'description' => $description,
                'price' => $price,
                'original_price' => $originalPrice,
                'stock' => $stock,
                'weight' => $weight,
                'sizes' => $sizes,
                'colors' => $colors,
                'main_photo' => $mainPhoto,
                'is_active' => true,
                'is_recommended' => false,
            ]);

            $importedCount++;
        }

        // Clear cache
        try {
            Cache::flush();
        } catch (\Exception $e) {
            // Ignore cache errors
        }

        $message = "Berhasil mengimpor $importedCount produk.";
        if (!empty($uploadedImagesMap)) {
            $countImages = count(array_unique($uploadedImagesMap));
            $message .= " ($countImages berkas foto berhasil diunggah).";
        }
        if ($skippedCount > 0) {
            $message .= " $skippedCount baris diabaikan karena kesalahan data.";
        }

        if (!empty($errors)) {
            return back()->with('success', $message)->with('import_errors', $errors);
        }

        return back()->with('success', $message);
    }

    private function parseRowsFromFile(string $path): array
    {
        $content = file_get_contents($path);

        // 1. Handle HTML-based Excel file (.xls template downloaded from system)
        if (str_contains(strtolower($content), '<table') || str_contains(strtolower($content), '<tr')) {
            preg_match_all('/<tr[^>]*>(.*?)<\/tr>/is', $content, $trMatches);
            $rows = [];
            foreach ($trMatches[1] as $trContent) {
                preg_match_all('/<t[dh][^>]*>(.*?)<\/t[dh]>/is', $trContent, $tdMatches);
                if (!empty($tdMatches[1])) {
                    $row = array_map(function ($val) {
                        return html_entity_decode(trim(strip_tags($val)), ENT_QUOTES | ENT_HTML5, 'UTF-8');
                    }, $tdMatches[1]);
                    $rows[] = $row;
                }
            }
            return $rows;
        }

        // 2. Handle CSV / Text file (auto detect delimiter: comma or semicolon)
        $rows = [];
        if (($handle = fopen($path, 'r')) !== false) {
            $firstLine = fgets($handle);
            rewind($handle);

            $delimiter = ',';
            if ($firstLine !== false) {
                $countSemicolon = substr_count($firstLine, ';');
                $countComma = substr_count($firstLine, ',');
                if ($countSemicolon > $countComma) {
                    $delimiter = ';';
                }
            }

            while (($data = fgetcsv($handle, 0, $delimiter)) !== false) {
                $rows[] = array_map('trim', $data);
            }
            fclose($handle);
        }

        return $rows;
    }

    public function downloadTemplate()
    {
        $filename = 'template_impor_produk.xls';
        
        $headers = [
            'Content-Type' => 'application/vnd.ms-excel',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
            'Cache-Control' => 'max-age=0',
        ];

        $columns = ['Kategori', 'Nama', 'SKU', 'Deskripsi', 'Harga', 'Harga_Coret', 'Stok', 'Berat', 'Ukuran', 'Warna', 'Foto_Utama'];
        $exampleRow = ['Topi', 'Topi Trucker Premium', 'TRK-001', 'Topi trucker berkualitas tinggi dengan jaring belakang.', '45000', '65000', '50', '80', 'M;L', 'Hitam;Biru', 'topi ubsi hitam.jpg'];

        $callback = function() use ($columns, $exampleRow) {
            echo '<html xmlns:o="urn:schemas-microsoft-com:office:office" xmlns:x="urn:schemas-microsoft-com:office:excel" xmlns="http://www.w3.org/TR/REC-html40">';
            echo '<head>';
            echo '<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />';
            echo '<!--[if gte mso 9]><xml><x:ExcelWorkbook><x:ExcelWorksheets><x:ExcelWorksheet><x:Name>Template Impor</x:Name><x:WorksheetOptions><x:DisplayGridlines/></x:WorksheetOptions></x:ExcelWorksheet></x:ExcelWorksheets></x:ExcelWorkbook></xml><![endif]-->';
            echo '<style>';
            echo 'table { border-collapse: collapse; }';
            echo 'th { background-color: #0D47A1; color: #FFFFFF; font-weight: bold; border: 1px solid #1E293B; height: 35px; font-family: sans-serif; font-size: 11pt; padding: 5px; text-align: center; }';
            echo 'td { border: 1px solid #CBD5E1; font-family: sans-serif; font-size: 10pt; height: 30px; padding: 5px; }';
            echo '</style>';
            echo '</head>';
            echo '<body>';
            echo '<table>';
            echo '<thead>';
            echo '<tr>';
            foreach ($columns as $col) {
                echo '<th>' . htmlspecialchars($col) . '</th>';
            }
            echo '</tr>';
            echo '</thead>';
            echo '<tbody>';
            echo '<tr>';
            foreach ($exampleRow as $val) {
                echo '<td>' . htmlspecialchars($val) . '</td>';
            }
            echo '</tr>';
            echo '</tbody>';
            echo '</table>';
            echo '</body>';
            echo '</html>';
        };

        return response()->stream($callback, 200, $headers);
    }
}