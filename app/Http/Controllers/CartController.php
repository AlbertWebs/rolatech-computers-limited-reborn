<?php

namespace App\Http\Controllers;

use App\Models\Product;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class CartController extends Controller
{
    public function index(Request $request): View
    {
        $cart = $request->session()->get('cart', []);
        $products = Product::query()
            ->where('is_active', true)
            ->whereIn('id', array_keys($cart))
            ->get()
            ->keyBy('id');

        $items = collect($cart)->map(function (int $quantity, string|int $productId) use ($products): ?array {
            $product = $products->get($productId);

            if (! $product || $product->isOutOfStock() || ($product->stock_quantity !== null && $quantity > $product->stock_quantity)) {
                return null;
            }

            return ['product' => $product, 'quantity' => $quantity, 'line_total' => (float) $product->price * $quantity];
        })->filter();

        if ($items->count() !== count($cart)) {
            $request->session()->put('cart', $items->mapWithKeys(fn (array $item): array => [$item['product']->id => $item['quantity']])->all());
            $request->session()->flash('status', 'Unavailable products or quantities were removed from your cart.');
        }

        $subtotal = $items->sum('line_total');

        return view('store.cart', compact('items', 'subtotal'));
    }

    public function store(Request $request, Product $product): RedirectResponse
    {
        abort_unless($product->is_active, 404);
        if ($product->isOutOfStock()) {
            return back()->withErrors(['quantity' => 'This product is currently out of stock.']);
        }
        $validated = $request->validate(['quantity' => ['sometimes', 'integer', 'min:1', 'max:100']]);
        $quantity = $validated['quantity'] ?? 1;
        $cart = $request->session()->get('cart', []);
        $newQuantity = ($cart[$product->id] ?? 0) + $quantity;

        if ($product->stock_quantity !== null && $newQuantity > $product->stock_quantity) {
            return back()->withErrors(['quantity' => 'The requested quantity is not currently available.']);
        }

        $cart[$product->id] = $newQuantity;
        $request->session()->put('cart', $cart);

        return back()->with('status', 'Added to your cart.');
    }

    public function update(Request $request, Product $product): RedirectResponse
    {
        abort_unless($product->is_active, 404);
        if ($product->isOutOfStock()) {
            return back()->withErrors(['quantity' => 'This product is currently out of stock.']);
        }
        $validated = $request->validate(['quantity' => ['required', 'integer', 'min:1', 'max:100']]);

        if ($product->stock_quantity !== null && $validated['quantity'] > $product->stock_quantity) {
            return back()->withErrors(['quantity' => 'The requested quantity is not currently available.']);
        }

        $cart = $request->session()->get('cart', []);
        if (array_key_exists($product->id, $cart)) {
            $cart[$product->id] = $validated['quantity'];
            $request->session()->put('cart', $cart);
        }

        return back()->with('status', 'Cart updated.');
    }

    public function destroy(Request $request, Product $product): RedirectResponse
    {
        $cart = $request->session()->get('cart', []);
        unset($cart[$product->id]);
        $request->session()->put('cart', $cart);

        return back()->with('status', 'Item removed from your cart.');
    }
}
