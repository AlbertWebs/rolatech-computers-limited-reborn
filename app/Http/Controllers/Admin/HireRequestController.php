<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\LaptopHireRequest as HireRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class HireRequestController extends Controller
{
    public function index(): View
    {
        return view('admin.hire.index', ['requests' => HireRequest::query()->latest()->paginate(25)]);
    }

    public function show(HireRequest $hireRequest): View
    {
        return view('admin.hire.show', compact('hireRequest'));
    }

    public function update(Request $request, HireRequest $hireRequest): RedirectResponse
    {
        $hireRequest->update($request->validate([
            'status' => ['required', 'in:pending,contacted,confirmed,completed,cancelled'],
            'admin_notes' => ['nullable', 'string', 'max:5000'],
        ]));

        return back()->with('status', 'Hire request updated.');
    }
}
