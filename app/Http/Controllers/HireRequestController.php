<?php

namespace App\Http\Controllers;

use App\Models\LaptopHireRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class HireRequestController extends Controller
{
    public function create(string $type = 'laptop'): View
    {
        $type = ['laptops' => 'laptop', 'macbooks' => 'macbook', 'tablets' => 'tablet', 'projectors' => 'projector'][$type] ?? $type;
        abort_unless(in_array($type, ['laptop', 'macbook', 'tablet', 'projector'], true), 404);
        $challenge = session('hire_challenge');
        if (! $challenge) {
            $a = random_int(2, 9);
            $b = random_int(1, 9);
            session(['hire_challenge' => ['answer' => $a + $b, 'question' => "$a + $b"]]);
        }

        return view('store.hire', ['type' => $type, 'challenge' => session('hire_challenge.question')]);
    }

    public function store(Request $request): RedirectResponse
    {
        if ($request->filled('website')) {
            return back()->with('status', 'Thank you. Your request has been received.');
        }
        $validated = $request->validate([
            'equipment_type' => ['required', 'in:laptop,macbook,tablet,projector'],
            'name' => ['required', 'string', 'max:120'], 'email' => ['required', 'email:rfc', 'max:190'],
            'phone' => ['required', 'regex:/^[0-9+() .-]{7,25}$/'], 'pickup_date' => ['required', 'date', 'after_or_equal:today'],
            'number_of_items' => ['required', 'integer', 'between:1,1000'], 'desired_specs' => ['required', 'string', 'max:3000'],
            'verify_contact_input' => ['required', 'integer'],
        ]);
        if ((int) $validated['verify_contact_input'] !== (int) session('hire_challenge.answer', -1)) {
            return back()->withErrors(['verify_contact_input' => 'Please solve the arithmetic check correctly.'])->withInput();
        }
        unset($validated['verify_contact_input']);
        LaptopHireRequest::create($validated);
        session()->forget('hire_challenge');

        return redirect()->route('hire.create', ['type' => $validated['equipment_type']])->with('status', 'Your hire request is recorded. Our team will contact you to confirm availability and details.');
    }
}
