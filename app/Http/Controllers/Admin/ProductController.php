<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Brand;
use App\Models\Category;
use App\Models\Product;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class ProductController extends Controller
{
    public function index(Request $request): View
    {
        $validated = $request->validate([
            'q' => ['nullable', 'string', 'max:100'],
            'status' => ['nullable', 'in:active,inactive'],
        ]);

        $products = Product::query()
            ->with(['brand', 'category'])
            ->when($validated['q'] ?? null, function (Builder $query, string $search): void {
                $query->where(function (Builder $query) use ($search): void {
                    $query->where('name', 'like', "%{$search}%")
                        ->orWhere('sku', 'like', "%{$search}%");
                });
            })
            ->when($validated['status'] ?? null, fn (Builder $query, string $status) => $query->where('is_active', $status === 'active'))
            ->orderBy('name')
            ->paginate(20)
            ->withQueryString();

        return view('admin.products.index', compact('products'));
    }

    public function create(): View
    {
        $product = new Product(['is_active' => true]);
        [$categories, $brands] = $this->catalogOptions();

        return view('admin.products.form', compact('product', 'categories', 'brands'));
    }

    public function store(Request $request): RedirectResponse
    {
        $values = $this->validatedValues($request);
        if ($request->hasFile('image')) {
            $values['image_path'] = 'storage/'.$request->file('image')->storePublicly('products', 'public');
        }

        Product::query()->create($values);

        return redirect()->route('admin.products.index')->with('status', 'Product created.');
    }

    public function edit(Product $product): View
    {
        [$categories, $brands] = $this->catalogOptions();

        return view('admin.products.form', compact('product', 'categories', 'brands'));
    }

    public function update(Request $request, Product $product): RedirectResponse
    {
        $values = $this->validatedValues($request, $product);
        if ($request->hasFile('image')) {
            $values['image_path'] = 'storage/'.$request->file('image')->storePublicly('products', 'public');
        }

        $product->update($values);

        return redirect()->route('admin.products.index')->with('status', 'Product updated.');
    }

    public function visibility(Product $product): RedirectResponse
    {
        $product->update(['is_active' => ! $product->is_active]);

        return back()->with('status', $product->is_active ? 'Product published.' : 'Product hidden from the shop.');
    }

    /** @return array{0: Collection<int, Category>, 1: Collection<int, Brand>} */
    private function catalogOptions(): array
    {
        return [
            Category::query()->orderBy('name')->get(),
            Brand::query()->orderBy('name')->get(),
        ];
    }

    /** @return array<string, mixed> */
    private function validatedValues(Request $request, ?Product $product = null): array
    {
        $values = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'slug' => ['nullable', 'string', 'max:255', 'alpha_dash', Rule::unique('products', 'slug')->ignore($product?->id)],
            'sku' => ['nullable', 'string', 'max:100', Rule::unique('products', 'sku')->ignore($product?->id)],
            'category_id' => ['nullable', 'integer', 'exists:categories,id'],
            'brand_id' => ['nullable', 'integer', 'exists:brands,id'],
            'short_description' => ['nullable', 'string', 'max:500'],
            'description' => ['nullable', 'string', 'max:50000'],
            'price' => ['required', 'numeric', 'gt:0', 'max:9999999999.99'],
            'compare_at_price' => ['nullable', 'numeric', 'gt:price', 'max:9999999999.99'],
            'stock_quantity' => ['nullable', 'integer', 'min:0', 'max:4294967295'],
            'stock_label' => ['nullable', 'string', 'max:100'],
            'is_featured' => ['sometimes', 'boolean'],
            'is_active' => ['sometimes', 'boolean'],
            'image' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp,avif', 'max:5120'],
        ]);
        unset($values['image']);
        $values['slug'] = Str::slug(($values['slug'] ?? null) ?: $values['name']);
        $slugExists = Product::query()
            ->where('slug', $values['slug'])
            ->when($product, fn (Builder $query) => $query->where('id', '!=', $product->id))
            ->exists();

        if ($slugExists) {
            throw ValidationException::withMessages(['slug' => 'This product name or URL slug is already in use.']);
        }

        $values['is_featured'] = $request->boolean('is_featured');
        $values['is_active'] = $request->boolean('is_active');

        return $values;
    }
}
