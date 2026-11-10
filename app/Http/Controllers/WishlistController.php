<?php

namespace App\Http\Controllers;

use App\Models\Product;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class WishlistController extends Controller
{
    public function index(Request $request): View
    {
        $ids = $request->session()->get('wishlist', []);
        $products = Product::query()->where('is_active', true)->whereIn('id', $ids)->with('brand')->get();
        $request->session()->put('wishlist', $products->modelKeys());

        return view('store.wishlist', compact('products'));
    }

    public function store(Request $request, Product $product): RedirectResponse
    {
        abort_unless($product->is_active, 404);
        $ids = array_map('intval', $request->session()->get('wishlist', []));
        if (! in_array($product->id, $ids, true)) {
            $ids[] = $product->id;
        }
        $request->session()->put('wishlist', $ids);

        return back()->with('status', 'Saved to your wish list.');
    }

    public function remove(Request $request, Product $product): RedirectResponse
    {
        $request->session()->put('wishlist', array_values(array_filter(
            array_map('intval', $request->session()->get('wishlist', [])),
            fn (int $id): bool => $id !== $product->id,
        )));

        return back()->with('status', 'Removed from your wish list.');
    }

    public function legacyAdd(Request $request, string $slug): JsonResponse
    {
        $product = Product::query()->where('is_active', true)->where(function ($query) use ($slug): void {
            $query->where('slug', $slug)->orWhere('legacy_id', is_numeric($slug) ? (int) $slug : -1);
        })->firstOrFail();
        $ids = array_map('intval', $request->session()->get('wishlist', []));
        if (! in_array($product->id, $ids, true)) {
            $ids[] = $product->id;
            $request->session()->put('wishlist', $ids);
        }

        return response()->json([
            'id' => $product->id, 'name' => $product->name, 'slung' => $product->slug,
            'price' => $product->price, 'quantity' => 1, 'image' => $product->image_path,
        ]);
    }
}
