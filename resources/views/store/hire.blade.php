@extends('store.layout')
@section('title', ucfirst($type).' hire | Royaltech Computers Limited')
@section('content')
<section class="mx-auto max-w-3xl px-4 py-14 sm:px-6 lg:px-8">
    <p class="text-xs font-bold uppercase tracking-[.15em] text-blue-700">Equipment hire</p>
    <h1 class="mt-2 font-display text-4xl font-extrabold text-[#142b50]">Request {{ $type }} hire</h1>
    <p class="mt-3 text-slate-600">Tell us what you need and when. Our team will contact you to confirm availability, pricing and pickup or delivery details.</p>
    <form method="POST" action="{{ route('laptops-for-hire') }}" class="mt-8 grid gap-5 rounded-2xl border border-slate-200 bg-white p-6 shadow-sm sm:grid-cols-2">
        @csrf<input type="hidden" name="equipment_type" value="{{ $type }}">
        <label class="grid gap-2 text-sm font-semibold">Full name<input required name="name" value="{{ old('name') }}" autocomplete="name" class="rounded-lg border border-slate-300 px-3 py-3 font-normal"></label>
        <label class="grid gap-2 text-sm font-semibold">Email<input required type="email" name="email" value="{{ old('email') }}" autocomplete="email" class="rounded-lg border border-slate-300 px-3 py-3 font-normal"></label>
        <label class="grid gap-2 text-sm font-semibold">Phone<input required type="tel" name="phone" value="{{ old('phone') }}" autocomplete="tel" class="rounded-lg border border-slate-300 px-3 py-3 font-normal"></label>
        <label class="grid gap-2 text-sm font-semibold">Hire start date<input required type="date" min="{{ now()->toDateString() }}" name="pickup_date" value="{{ old('pickup_date') }}" class="rounded-lg border border-slate-300 px-3 py-3 font-normal"></label>
        <label class="grid gap-2 text-sm font-semibold">Quantity<input required type="number" min="1" max="1000" name="number_of_items" value="{{ old('number_of_items', 1) }}" class="rounded-lg border border-slate-300 px-3 py-3 font-normal"></label>
        <label class="grid gap-2 text-sm font-semibold sm:col-span-2">Equipment and requirements<textarea required name="desired_specs" rows="4" class="rounded-lg border border-slate-300 px-3 py-3 font-normal">{{ old('desired_specs') }}</textarea></label>
        <label class="grid gap-2 text-sm font-semibold sm:col-span-2">Spam check: {{ $challenge }} = ?<input required type="number" name="verify_contact_input" class="w-32 rounded-lg border border-slate-300 px-3 py-3 font-normal"></label>
        <div class="absolute -left-[10000px]" aria-hidden="true"><label>Website<input name="website" tabindex="-1" autocomplete="off"></label></div>
        <button class="rounded-lg bg-blue-800 px-5 py-3 font-bold text-white hover:bg-blue-900 sm:col-span-2">Send hire request</button>
    </form>
</section>
@endsection
