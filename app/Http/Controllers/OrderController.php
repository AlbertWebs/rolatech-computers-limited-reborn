<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\Product;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\View\View;

class OrderController extends Controller
{
    public function create(Request $request): View|RedirectResponse
    {
        $items = $this->cartItems($request);

        if ($items->isEmpty() || $items->count() !== count($request->session()->get('cart', []))) {
            return redirect()->route('cart.index')->withErrors(['cart' => 'Review your cart: it contains an unavailable product or quantity.']);
        }

        $subtotal = $items->sum('line_total');

        return view('store.checkout', compact('items', 'subtotal'));
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'full_name' => ['required', 'string', 'min:2', 'max:120'],
            'email' => ['required', 'email:rfc', 'max:254'],
            'phone' => ['required', 'string', 'regex:/^\+?[0-9\s()\-]{9,20}$/'],
            'delivery_address' => ['required', 'string', 'min:5', 'max:255'],
            'delivery_city' => ['required', 'string', 'min:2', 'max:120'],
            'customer_notes' => ['nullable', 'string', 'max:1000'],
        ]);
        $items = $this->cartItems($request);

        if ($items->isEmpty() || $items->count() !== count($request->session()->get('cart', []))) {
            return redirect()->route('cart.index')->withErrors(['cart' => 'Review your cart: it contains an unavailable product or quantity.']);
        }

        $order = DB::transaction(function () use ($items, $validated, $request): Order {
            $subtotal = $items->sum('line_total');
            $order = Order::query()->create([
                'order_number' => 'RT-'.Str::upper(Str::random(16)),
                'user_id' => $request->user()?->id,
                ...$validated,
                'status' => 'awaiting_confirmation',
                'payment_status' => 'unpaid',
                'currency' => 'KES',
                'subtotal' => $subtotal,
            ]);

            $order->items()->createMany($items->map(fn (array $item): array => [
                'product_id' => $item['product']->id,
                'product_name' => $item['product']->name,
                'sku' => $item['product']->sku,
                'quantity' => $item['quantity'],
                'unit_price' => $item['product']->price,
                'line_total' => $item['line_total'],
            ])->all());

            return $order;
        });

        $request->session()->forget('cart');

        return redirect()->route('checkout.confirmation', ['orderNumber' => $order->order_number]);
    }

    public function confirmation(string $orderNumber): View
    {
        $order = Order::query()->with('items')->where('order_number', $orderNumber)->firstOrFail();

        return view('store.order-confirmation', compact('order'));
    }

    /** @return Collection<int, array{product: Product, quantity: int, line_total: float}> */
    private function cartItems(Request $request): Collection
    {
        $cart = $request->session()->get('cart', []);
        if (! is_array($cart) || $cart === []) {
            return collect();
        }

        $products = Product::query()
            ->where('is_active', true)
            ->whereIn('id', array_keys($cart))
            ->get()
            ->keyBy('id');

        return collect($cart)->map(function (mixed $quantity, string|int $productId) use ($products): ?array {
            $product = $products->get($productId);

            if (! $product || ! is_numeric($quantity) || (int) $quantity < 1 || $product->isOutOfStock()) {
                return null;
            }

            $quantity = (int) $quantity;
            if ($product->stock_quantity !== null && $quantity > $product->stock_quantity) {
                return null;
            }

            return ['product' => $product, 'quantity' => $quantity, 'line_total' => round((float) $product->price * $quantity, 2)];
        })->filter();
    }
}
