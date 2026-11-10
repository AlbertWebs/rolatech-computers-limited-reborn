<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ContactInquiry;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ContactInquiryController extends Controller
{
    public function index(): View
    {
        return view('admin.contact.index', ['inquiries' => ContactInquiry::query()->latest()->paginate(25)]);
    }

    public function show(ContactInquiry $inquiry): View
    {
        return view('admin.contact.show', compact('inquiry'));
    }

    public function update(Request $request, ContactInquiry $inquiry): RedirectResponse
    {
        $inquiry->update($request->validate([
            'status' => ['required', 'in:new,contacted,resolved,spam'],
            'admin_notes' => ['nullable', 'string', 'max:5000'],
        ]));

        return back()->with('status', 'Contact enquiry updated.');
    }
}
