<?php

namespace App\Http\Controllers;

use App\Models\Brand;
use App\Models\Category;
use App\Models\Product;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\View\View;

class StorefrontController extends Controller
{
    public function home(): View
    {
        $categories = Category::query()
            ->where('is_active', true)
            ->orderBy('sort_order')
            ->limit(6)
            ->get();

        $featuredProducts = Product::query()
            ->where('is_active', true)
            ->with(['brand', 'category'])
            ->orderByDesc('is_featured')
            ->latest()
            ->limit(4)
            ->get();

        return view('store.home', compact('categories', 'featuredProducts'));
    }

    public function index(Request $request): View
    {
        return $this->catalog($request, Product::query()->where('is_active', true));
    }

    public function brand(Request $request, string $slug): View
    {
        $brand = Brand::query()->where('slug', $slug)->where('is_active', true)->firstOrFail();

        return $this->catalog($request, Product::query()->where('is_active', true)->where('brand_id', $brand->id), $brand->name);
    }

    public function category(Request $request, string $slug): View
    {
        $category = Category::query()->where('slug', $slug)->where('is_active', true)->firstOrFail();

        return $this->catalog($request, Product::query()->where('is_active', true)->where('category_id', $category->id), $category->name);
    }

    public function show(string $slug): View
    {
        $product = Product::query()
            ->where('is_active', true)
            ->where('slug', $slug)
            ->with(['brand', 'category'])
            ->firstOrFail();

        $relatedProducts = Product::query()
            ->where('is_active', true)
            ->where('id', '!=', $product->id)
            ->when($product->category_id, fn (Builder $query) => $query->where('category_id', $product->category_id))
            ->with('brand')
            ->limit(4)
            ->get();

        return view('store.product', compact('product', 'relatedProducts'));
    }

    public function quickView(string $slug): View
    {
        $product = Product::query()
            ->where('is_active', true)
            ->where('slug', $slug)
            ->with(['brand', 'category'])
            ->firstOrFail();

        return view('store.quick-view', compact('product'));
    }

    private function catalog(Request $request, Builder $products, ?string $heading = null): View
    {
        $validated = $request->validate([
            'q' => ['nullable', 'string', 'max:100'],
            'brand' => ['nullable', 'string', 'max:80'],
            'category' => ['nullable', 'string', 'max:80'],
            'min_price' => ['nullable', 'numeric', 'min:0', 'max:999999999'],
            'max_price' => ['nullable', 'numeric', 'min:0', 'max:999999999', 'gte:min_price'],
            'sort' => ['nullable', 'in:relevance,price_asc,price_desc,newest'],
        ]);

        $search = $validated['q'] ?? null;
        $products
            ->with(['brand', 'category'])
            ->when($search, function (Builder $query, string $search): void {
                $query->where(function (Builder $query) use ($search): void {
                    $query->where('name', 'like', "%{$search}%")
                        ->orWhere('sku', 'like', "%{$search}%")
                        ->orWhere('short_description', 'like', "%{$search}%");
                });
            })
            ->when($validated['brand'] ?? null, fn (Builder $query, string $brand) => $query->whereHas('brand', fn (Builder $brandQuery) => $brandQuery->where('slug', $brand)))
            ->when($validated['category'] ?? null, fn (Builder $query, string $category) => $query->whereHas('category', fn (Builder $categoryQuery) => $categoryQuery->where('slug', $category)))
            ->when($validated['min_price'] ?? null, fn (Builder $query, string $price) => $query->where('price', '>=', $price))
            ->when($validated['max_price'] ?? null, fn (Builder $query, string $price) => $query->where('price', '<=', $price));

        match ($validated['sort'] ?? 'relevance') {
            'price_asc' => $products->orderBy('price'),
            'price_desc' => $products->orderByDesc('price'),
            'newest' => $products->latest(),
            default => $products->orderByDesc('is_featured')->latest(),
        };

        $productsPage = $products->paginate(12)->withQueryString();
        $categories = Category::query()->where('is_active', true)->orderBy('sort_order')->get();
        $brands = Brand::query()->where('is_active', true)->orderBy('name')->get();

        return view('store.catalog', compact('productsPage', 'categories', 'brands', 'heading', 'search'));
    }
}
