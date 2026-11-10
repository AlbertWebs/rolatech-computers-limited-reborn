<?php

namespace App\Http\Controllers;

use App\Models\ContactInquiry;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class PageController extends Controller
{
    public function company(): View
    {
        return view('store.company');
    }

    public function contact(): View
    {
        return view('store.contact');
    }

    public function message(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:120'],
            'email' => ['required', 'email:rfc', 'max:190'],
            'phone' => ['nullable', 'regex:/^[0-9+() .-]{7,25}$/'],
            'subject' => ['required', 'string', 'max:160'],
            'message' => ['required', 'string', 'max:5000'],
        ]);
        ContactInquiry::query()->create($validated);

        return back()->with('status', 'Your message has been recorded. Our team will follow up using the contact details you provided.');
    }
}
