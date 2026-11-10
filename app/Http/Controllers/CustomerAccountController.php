<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class CustomerAccountController extends Controller
{
    public function login(): View|RedirectResponse
    {
        return Auth::check() ? redirect()->route('account.orders') : view('store.account.login');
    }

    public function authenticate(Request $request): RedirectResponse
    {
        $credentials = $request->validate(['email' => ['required', 'email:rfc', 'max:254'], 'password' => ['required', 'string', 'max:200']]);
        if (! Auth::attempt([...$credentials, 'is_active' => true], $request->boolean('remember'))) {
            return back()->withErrors(['email' => 'These account details could not be verified.'])->onlyInput('email');
        }
        $request->session()->regenerate();

        return redirect()->intended(route('account.orders'));
    }

    public function register(): View|RedirectResponse
    {
        return Auth::check() ? redirect()->route('account.orders') : view('store.account.register');
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:120'], 'email' => ['required', 'email:rfc', 'max:254', 'unique:users,email'],
            'password' => ['required', 'string', 'min:12', 'confirmed'],
        ]);
        $user = User::query()->create($validated);
        Auth::login($user);
        $request->session()->regenerate();

        return redirect()->route('account.orders')->with('status', 'Your Royaltech account is ready.');
    }

    public function orders(Request $request): View
    {
        $orders = Order::query()->where('user_id', $request->user()->id)->withCount('items')->latest()->paginate(15);

        return view('store.account.orders', compact('orders'));
    }

    public function profile(Request $request): View
    {
        return view('store.account.profile', ['customer' => $request->user()]);
    }

    public function updateProfile(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:120'],
            'phone' => ['nullable', 'regex:/^[0-9+() .-]{7,25}$/'],
            'current_password' => ['nullable', 'required_with:password', 'current_password'],
            'password' => ['nullable', 'required_with:current_password', 'string', 'min:12', 'confirmed'],
        ]);
        $customer = $request->user();
        $customer->name = $validated['name'];
        $customer->phone = $validated['phone'] ?? null;
        if (! empty($validated['password'])) {
            $customer->password = $validated['password'];
        }
        $customer->save();

        return back()->with('status', 'Your profile has been updated.');
    }

    public function showOrder(Request $request, string $orderNumber): View
    {
        $order = Order::query()->where('user_id', $request->user()->id)->where('order_number', $orderNumber)->with('items')->firstOrFail();

        return view('store.account.order', compact('order'));
    }

    public function logout(Request $request): RedirectResponse
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('home');
    }
}
