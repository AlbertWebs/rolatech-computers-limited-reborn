<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Royaltech Computers Limited | Technology for work and home')</title>
    <meta name="description" content="Shop computers, laptops and accessories or talk to Royaltech about IT services and equipment hire in Nairobi, Kenya.">
    <link rel="canonical" href="@yield('canonical', url()->current())">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @stack('head')
</head>
<body class="min-h-screen bg-white text-slate-900 antialiased">
    <div class="bg-navy text-white">
        <div class="mx-auto flex max-w-7xl flex-wrap items-center justify-between gap-2 px-4 py-2 text-xs sm:px-6 lg:px-8">
            <span>Technology that works for you — across Kenya</span>
            <div class="flex items-center gap-5"><a href="tel:+254724404935" class="hover:text-cyan-200">+254 724 404 935</a><a href="mailto:info@royaltech.co.ke" class="hidden hover:text-cyan-200 sm:inline">info@royaltech.co.ke</a></div>
        </div>
    </div>
    <header class="sticky top-0 z-40 border-b border-slate-200 bg-white/95 backdrop-blur">
        <div class="mx-auto flex max-w-7xl items-center gap-5 px-4 py-4 sm:px-6 lg:px-8">
            <a href="{{ route('home') }}" class="shrink-0" aria-label="Royaltech home">
                <img src="{{ asset('assets/legacy/logo/Royaltech-Original-1.png') }}" alt="Royaltech Computers Limited" class="h-11 w-auto" width="190" height="46">
            </a>
            <form action="{{ route('catalog.search') }}" method="GET" class="hidden min-w-0 flex-1 md:block" role="search">
                <label class="sr-only" for="site-search">Search products</label>
                <div class="flex rounded-lg border border-slate-300 bg-slate-50 focus-within:border-blue-700 focus-within:ring-2 focus-within:ring-blue-700/15">
                    <input id="site-search" name="q" value="{{ request('q') }}" placeholder="Search laptops, desktops, accessories…" class="min-w-0 flex-1 rounded-l-lg bg-transparent px-4 py-3 text-sm outline-none" type="search">
                    <button class="rounded-r-lg bg-blue-800 px-5 text-sm font-semibold text-white transition hover:bg-blue-900" type="submit">Search</button>
                </div>
            </form>
            <nav class="ml-auto flex items-center gap-4 text-sm font-semibold" aria-label="Main navigation">
                <a href="{{ route('catalog.index') }}" class="hidden text-slate-700 hover:text-blue-800 sm:inline">Shop</a>
                <a href="{{ route('home') }}#services" class="hidden text-slate-700 hover:text-blue-800 lg:inline">IT services</a>
                <a href="{{ route('home') }}#hire" class="hidden text-slate-700 hover:text-blue-800 lg:inline">Equipment hire</a>
                <a href="{{ auth()->check() ? route('account.orders') : route('login') }}" class="hidden text-slate-700 hover:text-blue-800 sm:inline">{{ auth()->check() ? 'Account' : 'Sign in' }}</a>
                @if(auth()->check())<a href="{{ route('account.profile') }}" class="hidden text-slate-700 hover:text-blue-800 lg:inline">Profile</a>@endif
                <a href="{{ route('wishlist.index') }}" class="hidden text-slate-700 hover:text-blue-800 sm:inline">Saved</a>
                <a href="{{ route('cart.index') }}" class="inline-flex items-center gap-2 rounded-lg border border-slate-300 px-3 py-2 hover:border-blue-700 hover:text-blue-800" aria-label="Shopping cart">
                    <svg aria-hidden="true" viewBox="0 0 24 24" class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="1.7"><path d="M3 4h2l2.2 11.1a2 2 0 0 0 2 1.6h8.3a2 2 0 0 0 1.9-1.4L21 9H6"/><circle cx="10" cy="20" r="1"/><circle cx="18" cy="20" r="1"/></svg>
                    <span class="hidden sm:inline">Cart</span>
                    @if (array_sum(session('cart', [])) > 0)<span class="grid h-5 min-w-5 place-items-center rounded-full bg-blue-800 px-1 text-[11px] text-white">{{ array_sum(session('cart', [])) }}</span>@endif
                </a>
            </nav>
        </div>
        <form action="{{ route('catalog.search') }}" method="GET" class="border-t border-slate-100 px-4 py-3 md:hidden">
            <label class="sr-only" for="mobile-search">Search products</label>
            <div class="flex overflow-hidden rounded-lg border border-slate-300"><input id="mobile-search" name="q" value="{{ request('q') }}" placeholder="Search products" class="min-w-0 flex-1 px-3 py-2.5 text-sm outline-none" type="search"><button class="bg-blue-800 px-4 text-sm font-semibold text-white" type="submit">Search</button></div>
        </form>
    </header>
    @if (session('status') || $errors->any())
        <div class="mx-auto max-w-7xl px-4 pt-4 sm:px-6 lg:px-8" role="status">
            @if (session('status'))<p class="rounded-lg border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-900">{{ session('status') }}</p>@endif
            @foreach ($errors->all() as $error)<p class="mt-2 rounded-lg border border-rose-200 bg-rose-50 px-4 py-3 text-sm text-rose-900">{{ $error }}</p>@endforeach
        </div>
    @endif
    <main>@yield('content')</main>
    <footer class="mt-20 bg-slate-950 text-slate-300">
        <div class="mx-auto grid max-w-7xl gap-10 px-4 py-14 sm:px-6 md:grid-cols-2 lg:grid-cols-4 lg:px-8">
            <div class="lg:col-span-2"><img src="{{ asset('assets/legacy/logo/Royaltech-Original-White-1.png') }}" alt="Royaltech Computers Limited" class="mb-5 h-10 w-auto" width="190" height="42"><p class="max-w-md text-sm leading-6 text-slate-400">Your technology partner for computers, equipment hire, networking and practical IT services. Supporting individuals, SMEs and corporate teams in Kenya.</p></div>
            <div><h2 class="mb-4 font-display text-sm font-bold text-white">Explore</h2><ul class="space-y-3 text-sm"><li><a class="hover:text-white" href="{{ route('catalog.index') }}">Shop computers</a></li><li><a class="hover:text-white" href="{{ route('home') }}#services">IT services</a></li><li><a class="hover:text-white" href="{{ route('home') }}#hire">Equipment hire</a></li><li><a class="hover:text-white" href="{{ route('company') }}">About Royaltech</a></li><li><a class="hover:text-white" href="{{ route('contact') }}">Contact us</a></li><li><a class="hover:text-white" href="{{ route('blog.index') }}">Articles</a></li></ul></div>
            <div><h2 class="mb-4 font-display text-sm font-bold text-white">Talk to our team</h2><ul class="space-y-3 text-sm"><li><a class="hover:text-white" href="tel:+254724404935">+254 724 404 935</a></li><li><a class="hover:text-white" href="mailto:info@royaltech.co.ke">info@royaltech.co.ke</a></li><li>Biashara Street, Nairobi, Kenya</li></ul></div>
        </div>
        <div class="border-t border-white/10"><div class="mx-auto flex max-w-7xl flex-col gap-2 px-4 py-5 text-xs text-slate-500 sm:px-6 md:flex-row md:items-center md:justify-between lg:px-8"><span>© {{ date('Y') }} Royaltech Computers Limited</span><span>Serving Kenya with experience, execution and excellence.</span></div></div>
    </footer>
</body>
</html>
