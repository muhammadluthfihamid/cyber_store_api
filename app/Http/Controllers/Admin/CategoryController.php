<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;

class CategoryController extends Controller
{
    public static function flushRedisCache(): void
    {
        try {
            $redis = Cache::store('redis');
            $redis->increment('admin:categories:version');
        } catch (\Throwable $e) {
            Cache::forget('admin:categories:version');
        }

        try {
            Cache::store('redis')->forget('categories:active');
        } catch (\Throwable $e) {
            Cache::forget('categories:active');
        }
    }

    public function index(Request $request)
    {
        $search = trim((string)$request->query('search', ''));
        $status = $request->query('status');
        $page = (int)$request->query('page', 1);

        try {
            $version = Cache::store('redis')->get('admin:categories:version', 1);
            $cacheKey = "admin:categories:v{$version}:" . md5(json_encode([
                'search' => $search,
                'status' => $status,
                'page' => $page,
            ]));

            $cachedData = Cache::store('redis')->remember($cacheKey, now()->addMinutes(30), function () use ($search, $status) {
                $query = Category::query();

                if ($search !== '') {
                    $query->where(function ($q) use ($search) {
                        $q->where('name', 'like', "%{$search}%")
                          ->orWhere('slug', 'like', "%{$search}%")
                          ->orWhere('description', 'like', "%{$search}%");
                    });
                }

                if ($status !== null && $status !== '') {
                    $query->where('is_active', $status === 'active');
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
                : Category::withCount('products')
                    ->whereIn('id', $ids)
                    ->get()
                    ->sortBy(fn ($c) => array_search($c->id, $ids))
                    ->values();

            $categories = new LengthAwarePaginator(
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
            $query = Category::withCount('products');
            if ($search !== '') {
                $query->where(function ($q) use ($search) {
                    $q->where('name', 'like', "%{$search}%")
                      ->orWhere('slug', 'like', "%{$search}%")
                      ->orWhere('description', 'like', "%{$search}%");
                });
            }
            if ($status !== null && $status !== '') {
                $query->where('is_active', $status === 'active');
            }
            $categories = $query->latest()->paginate(15)->withQueryString();
        }

        return view('admin.categories.index', compact('categories'));
    }

    public function suggestions(Request $request)
    {
        $q = trim((string)$request->query('q', ''));

        try {
            $version = Cache::store('redis')->get('admin:categories:version', 1);
            $cacheKey = "admin:categories:suggestions:v{$version}:" . md5($q);

            $results = Cache::store('redis')->remember($cacheKey, now()->addMinutes(15), function () use ($q) {
                $query = Category::withCount('products');

                if ($q !== '') {
                    $query->where(function ($w) use ($q) {
                        $w->where('name', 'like', "%{$q}%")
                          ->orWhere('slug', 'like', "%{$q}%")
                          ->orWhere('description', 'like', "%{$q}%");
                    });
                }

                return $query->orderByDesc('is_active')->latest()->take(8)->get()->map(function ($cat) {
                    return [
                        'id' => $cat->id,
                        'title' => $cat->name,
                        'subtitle' => $cat->products_count . ' Produk • /' . $cat->slug,
                        'value' => $cat->name,
                        'badge' => $cat->is_active ? 'Aktif' : 'Nonaktif',
                        'badge_color' => $cat->is_active ? '#10B981' : '#64748B',
                        'icon' => 'flat-color-icons:tag',
                    ];
                });
            });
        } catch (\Throwable $e) {
            $query = Category::withCount('products');
            if ($q !== '') {
                $query->where(function ($w) use ($q) {
                    $w->where('name', 'like', "%{$q}%")
                      ->orWhere('slug', 'like', "%{$q}%")
                      ->orWhere('description', 'like', "%{$q}%");
                });
            }
            $results = $query->orderByDesc('is_active')->latest()->take(8)->get()->map(function ($cat) {
                return [
                    'id' => $cat->id,
                    'title' => $cat->name,
                    'subtitle' => $cat->products_count . ' Produk • /' . $cat->slug,
                    'value' => $cat->name,
                    'badge' => $cat->is_active ? 'Aktif' : 'Nonaktif',
                    'badge_color' => $cat->is_active ? '#10B981' : '#64748B',
                    'icon' => 'flat-color-icons:tag',
                ];
            });
        }

        return response()->json($results);
    }

    public function create()
    {
        return view('admin.categories.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name'        => ['required', 'string', 'max:100'],
            'description' => ['nullable', 'string'],
            'is_active'   => ['boolean'],
        ]);

        $slug = Str::slug($validated['name']);
        $base = $slug;
        $i    = 1;
        while (Category::where('slug', $slug)->exists()) {
            $slug = $base . '-' . $i++;
        }

        Category::create([
            'name'        => $validated['name'],
            'slug'        => $slug,
            'description' => $validated['description'] ?? '',
            'is_active'   => $request->boolean('is_active', true),
        ]);

        static::flushRedisCache();

        return redirect()->route('admin.categories.index')
            ->with('success', 'Kategori berhasil ditambahkan.');
    }

    public function edit(Category $category)
    {
        return view('admin.categories.edit', compact('category'));
    }

    public function update(Request $request, Category $category)
    {
        $validated = $request->validate([
            'name'        => ['required', 'string', 'max:100'],
            'description' => ['nullable', 'string'],
            'is_active'   => ['boolean'],
        ]);

        if ($category->name !== $validated['name']) {
            $slug = Str::slug($validated['name']);
            $base = $slug;
            $i    = 1;
            while (Category::where('slug', $slug)->where('id', '!=', $category->id)->exists()) {
                $slug = $base . '-' . $i++;
            }
            $category->slug = $slug;
        }

        $category->update([
            'name'        => $validated['name'],
            'slug'        => $category->slug,
            'description' => $validated['description'] ?? '',
            'is_active'   => $request->boolean('is_active'),
        ]);

        static::flushRedisCache();

        return redirect()->route('admin.categories.index')
            ->with('success', 'Kategori berhasil diperbarui.');
    }

    public function destroy(Category $category)
    {
        if ($category->products()->exists()) {
            return back()->with('error', 'Kategori tidak dapat dihapus karena masih memiliki produk.');
        }

        $category->delete();

        static::flushRedisCache();

        return redirect()->route('admin.categories.index')
            ->with('success', 'Kategori berhasil dihapus.');
    }

    public function toggleActive(Category $category)
    {
        $category->update(['is_active' => !$category->is_active]);

        static::flushRedisCache();

        $status = $category->is_active ? 'diaktifkan' : 'dinonaktifkan';
        return back()->with('success', "Kategori {$category->name} berhasil {$status}.");
    }
}

