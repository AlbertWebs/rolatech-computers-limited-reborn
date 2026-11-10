@extends('store.layout')

@section('title', 'Royaltech Computers Limited | Technology that works for you')

@section('content')
    <section class="overflow-hidden bg-[#f2f6fb]">
        <div class="mx-auto grid max-w-7xl items-center gap-5 px-4 py-12 sm:px-6 sm:py-16 lg:grid-cols-[1.05fr_.95fr] lg:px-8 lg:py-20">
            <div class="relative z-10 py-2">
                <p class="mb-5 inline-flex items-center gap-2 rounded-full border border-blue-200 bg-white px-3 py-1.5 text-xs font-bold uppercase tracking-[.12em] text-blue-900"><span class="h-2 w-2 rounded-full bg-teal-500"></span> Royaltech Computers Limited</p>
                <h1 class="max-w-2xl font-display text-4xl font-extrabold leading-[1.1] tracking-tight text-[#142b50] sm:text-5xl lg:text-6xl">Technology that moves <span class="text-blue-700">you forward.</span></h1>
                <p class="mt-6 max-w-xl text-base leading-7 text-slate-600 sm:text-lg">Computers, equipment hire and dependable IT services for the people and businesses building what’s next.</p>
                <div class="mt-8 flex flex-wrap gap-3"><a href="{{ route('catalog.index') }}" class="inline-flex items-center gap-2 rounded-lg bg-blue-800 px-5 py-3.5 text-sm font-bold text-white shadow-sm transition hover:bg-blue-900">Explore the shop <span aria-hidden="true">→</span></a><a href="#hire" class="inline-flex items-center rounded-lg border border-slate-300 bg-white px-5 py-3.5 text-sm font-bold text-[#142b50] transition hover:border-blue-700">Ask about equipment hire</a></div>
                <div class="mt-9 flex flex-wrap gap-x-7 gap-y-3 text-xs font-semibold text-slate-600"><span class="inline-flex items-center gap-2"><span class="text-teal-600">✓</span> Business and personal solutions</span><span class="inline-flex items-center gap-2"><span class="text-teal-600">✓</span> Nairobi based, Kenya focused</span></div>
            </div>
            <div class="relative mx-auto w-full max-w-[620px]">
                <div class="absolute -right-6 -top-5 h-40 w-40 rounded-full bg-cyan-200/60 blur-3xl"></div><div class="absolute -bottom-8 -left-6 h-44 w-44 rounded-full bg-blue-200/70 blur-3xl"></div>
                <div class="relative overflow-hidden rounded-2xl border border-white bg-white p-2 shadow-[0_25px_80px_-35px_rgba(20,43,80,.42)]">
                    @php($heroSrcset = \App\Support\ResponsiveImages::srcset('assets/legacy/pexels-pixabay-221011.jpg'))<picture>@if($heroSrcset)<source type="image/webp" srcset="{{ $heroSrcset }}" sizes="(min-width: 1024px) 48vw, 100vw">@endif<img src="{{ asset('assets/legacy/pexels-pixabay-221011.jpg') }}" alt="Laptop and tablet set up for study and everyday work" width="1920" height="1080" fetchpriority="high" class="aspect-[1.28] w-full rounded-xl object-cover"></picture>
                    <div class="absolute bottom-6 left-6 right-6 rounded-xl border border-white/60 bg-white/95 p-4 shadow-lg backdrop-blur sm:bottom-8 sm:left-8 sm:right-auto sm:min-w-64"><p class="text-xs font-bold uppercase tracking-wider text-blue-800">Technology, made practical</p><p class="mt-1 font-display text-lg font-bold text-slate-900">The right setup starts here.</p><p class="mt-1 text-xs text-slate-500">Hardware • Hire • IT support</p></div>
                </div>
            </div>
        </div>
    </section>

    <section class="mx-auto max-w-7xl px-4 py-14 sm:px-6 lg:px-8 lg:py-16" aria-labelledby="shop-by-category">
        <div class="mb-7 flex items-end justify-between gap-4"><div><p class="text-xs font-bold uppercase tracking-[.15em] text-blue-700">Find your fit</p><h2 id="shop-by-category" class="mt-2 font-display text-2xl font-extrabold text-[#142b50] sm:text-3xl">Shop by category</h2></div><a href="{{ route('catalog.index') }}" class="text-sm font-bold text-blue-800 hover:text-blue-950">View the shop <span aria-hidden="true">→</span></a></div>
        @if ($categories->isNotEmpty())
            <div class="grid grid-cols-2 gap-3 sm:grid-cols-3 lg:grid-cols-6">
                @foreach ($categories as $category)
                    <a href="{{ route('catalog.category', $category->slug) }}" class="group rounded-xl border border-slate-200 bg-white p-4 transition hover:-translate-y-0.5 hover:border-blue-300 hover:shadow-lg">
                        @if ($category->image_path)@php($categorySrcset = \App\Support\ResponsiveImages::srcset($category->image_path))<picture class="mb-4 block">@if($categorySrcset)<source type="image/webp" srcset="{{ $categorySrcset }}" sizes="(min-width: 1024px) 16vw, 50vw">@endif<img src="{{ asset($category->image_path) }}" alt="" loading="lazy" class="aspect-square w-full rounded-lg bg-slate-50 object-contain"></picture>@else<div class="mb-4 grid aspect-square place-items-center rounded-lg bg-slate-50 text-blue-800"><span class="font-display text-3xl font-extrabold">{{ strtoupper(substr($category->name, 0, 1)) }}</span></div>@endif
                        <h3 class="font-display text-sm font-bold text-slate-900 group-hover:text-blue-800">{{ $category->name }}</h3><p class="mt-1 text-xs text-slate-500">Explore collection</p>
                    </a>
                @endforeach
            </div>
        @else
            <div class="rounded-xl border border-dashed border-slate-300 bg-slate-50 px-6 py-8 text-center"><p class="font-semibold text-slate-700">Our product catalogue is being prepared.</p><p class="mt-1 text-sm text-slate-500">Call <a href="tel:+254724404935" class="font-semibold text-blue-800">+254 724 404 935</a> for current availability.</p></div>
        @endif
    </section>

    @if ($featuredProducts->isNotEmpty())
        <section class="bg-slate-50 py-14 sm:py-16" aria-labelledby="featured-products"><div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8"><div class="mb-7 flex items-end justify-between gap-4"><div><p class="text-xs font-bold uppercase tracking-[.15em] text-blue-700">Selected for you</p><h2 id="featured-products" class="mt-2 font-display text-2xl font-extrabold text-[#142b50] sm:text-3xl">Featured technology</h2></div><a href="{{ route('catalog.index') }}" class="text-sm font-bold text-blue-800 hover:text-blue-950">Browse products <span aria-hidden="true">→</span></a></div><div class="grid grid-cols-2 gap-3 sm:grid-cols-2 sm:gap-5 lg:grid-cols-4">@foreach ($featuredProducts as $product)@include('store.partials.product-card', ['product' => $product])@endforeach</div></div></section>
    @endif

    <section id="services" class="mx-auto max-w-7xl px-4 py-14 sm:px-6 lg:px-8 lg:py-16">
        <div class="mb-8 max-w-2xl"><p class="text-xs font-bold uppercase tracking-[.15em] text-blue-700">Beyond the device</p><h2 class="mt-2 font-display text-2xl font-extrabold text-[#142b50] sm:text-3xl">Technology support that fits your goals.</h2><p class="mt-3 text-sm leading-6 text-slate-600 sm:text-base">From sourcing and delivery to connectivity and maintenance, our team helps turn technology requirements into working solutions.</p></div>
        <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
            @foreach ([['Computer sales', 'Laptops, desktops and accessories selected for your needs.', 'products'], ['Equipment hire', 'Flexible access to laptops and IT equipment for projects and teams.', 'hire'], ['Networking', 'Structured cabling and practical connectivity solutions.', 'services'], ['IT support', 'Service, maintenance and consultancy for your technology.', 'services']] as [$title, $body, $anchor])
                <a href="{{ $anchor === 'products' ? route('catalog.index') : '#'.$anchor }}" class="rounded-xl border border-slate-200 bg-white p-5 transition hover:border-blue-300 hover:shadow-md"><span class="grid h-10 w-10 place-items-center rounded-lg bg-blue-50 text-lg font-extrabold text-blue-800">{{ strtoupper(substr($title, 0, 1)) }}</span><h3 class="mt-4 font-display font-bold text-slate-900">{{ $title }}</h3><p class="mt-2 text-sm leading-6 text-slate-600">{{ $body }}</p><span class="mt-4 inline-flex items-center gap-1 text-sm font-bold text-blue-800">Learn more <span aria-hidden="true">→</span></span></a>
            @endforeach
        </div>
    </section>

    <section id="hire" class="mx-auto max-w-7xl px-4 pb-16 sm:px-6 lg:px-8"><div class="relative overflow-hidden rounded-2xl bg-[#142b50] px-6 py-9 text-white sm:px-10 sm:py-12 lg:px-14"><div class="absolute -right-10 -top-24 h-72 w-72 rounded-full border-[40px] border-white/5"></div><div class="relative flex flex-col gap-6 sm:flex-row sm:items-center sm:justify-between"><div class="max-w-2xl"><p class="text-xs font-bold uppercase tracking-[.15em] text-cyan-300">Need equipment for a project?</p><h2 class="mt-2 font-display text-2xl font-extrabold sm:text-3xl">Get the technology you need, when you need it.</h2><p class="mt-3 text-sm leading-6 text-blue-100">Talk with our team about laptop and equipment hire for events, teams, training and short-term projects.</p></div><a href="{{ route('laptops-for-hire') }}" class="inline-flex shrink-0 items-center justify-center gap-2 rounded-lg bg-white px-5 py-3.5 text-sm font-bold text-[#142b50] transition hover:bg-cyan-50">Request a conversation <span aria-hidden="true">→</span></a></div></div></section>

    <section id="about" class="border-t border-slate-100 bg-slate-50"><div class="mx-auto grid max-w-7xl gap-7 px-4 py-12 sm:px-6 md:grid-cols-[.8fr_1.2fr] lg:px-8"><div><p class="text-xs font-bold uppercase tracking-[.15em] text-blue-700">Experience. Execution. Excellence.</p><h2 class="mt-2 font-display text-2xl font-extrabold text-[#142b50]">Your technology partner in Kenya.</h2></div><p class="text-sm leading-7 text-slate-600">Royaltech Computers Limited is a locally registered technology solutions company serving individuals, SMEs and corporate teams. We bring together computer sales, equipment hire, product sourcing, networking and IT support to help customers get more from technology.</p></div></section>
@endsection
