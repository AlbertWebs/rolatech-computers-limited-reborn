<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Admin') | Royaltech</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-screen bg-slate-100 text-slate-900 antialiased">
    @php
        $role = auth()->user()->admin_role;
        $menu = [['Overview', 'admin.dashboard']];
        if (in_array($role, ['owner', 'manager', 'catalog'], true)) {
            $menu = array_merge($menu, [['Products', 'admin.products.index'], ['Categories', 'admin.taxonomy.index', 'categories'], ['Brands', 'admin.taxonomy.index', 'brands']]);
        }
        if (in_array($role, ['owner', 'manager', 'operations'], true)) {
            $menu = array_merge($menu, [['Orders', 'admin.orders.index'], ['Hire requests', 'admin.hire.index'], ['Contact enquiries', 'admin.contact.index']]);
        }
        if (in_array($role, ['owner', 'manager', 'content'], true)) {
            $menu[] = ['Articles', 'admin.blog.index'];
        }
        if (in_array($role, ['owner', 'manager'], true)) {
            $menu[] = ['Audit log', 'admin.audit.index'];
        }
    @endphp
    <header class="bg-[#142b50] text-white"><div class="mx-auto flex max-w-[1500px] items-center justify-between gap-4 px-4 py-3 sm:px-6"><a href="{{ route('admin.dashboard') }}" class="flex items-center gap-3"><img src="{{ asset('assets/legacy/logo/Royaltech-Original-White-1.png') }}" alt="Royaltech" class="h-9 w-auto"><span class="border-l border-white/20 pl-3 text-xs font-bold uppercase tracking-wider text-blue-100">Administration</span></a><div class="flex items-center gap-4"><a href="{{ route('home') }}" class="hidden text-xs font-semibold text-blue-100 hover:text-white sm:inline">View website ↗</a><span class="hidden text-xs text-blue-100 md:inline">{{ auth()->user()->name }} · {{ ucfirst($role) }}</span><form action="{{ route('admin.logout') }}" method="POST">@csrf<button class="rounded-lg border border-white/20 px-3 py-2 text-xs font-bold hover:bg-white/10">Sign out</button></form></div></div></header>
    <div class="mx-auto grid max-w-[1500px] gap-5 px-4 py-5 sm:px-6 lg:grid-cols-[230px_minmax(0,1fr)] lg:py-8">
        <aside class="h-fit rounded-xl border border-slate-200 bg-white p-3"><nav aria-label="Administration"><p class="px-3 pb-2 pt-2 text-[10px] font-bold uppercase tracking-[.15em] text-slate-400">Store management</p><div class="flex gap-2 overflow-x-auto lg:flex-col">@foreach ($menu as $item)@php($active = request()->routeIs($item[1].'*') && (!isset($item[2]) || request()->route('taxonomy') === $item[2]))<a href="{{ route($item[1], isset($item[2]) ? ['taxonomy' => $item[2]] : []) }}" @class(['shrink-0 rounded-lg px-3 py-2.5 text-sm font-semibold transition', 'bg-blue-50 text-blue-900' => $active, 'text-slate-600 hover:bg-slate-50 hover:text-blue-900' => ! $active])>{{ $item[0] }}</a>@endforeach</div><p class="px-3 pb-1 pt-5 text-[10px] font-bold uppercase tracking-[.15em] text-slate-400">Website</p><a href="{{ route('catalog.index') }}" class="block rounded-lg px-3 py-2.5 text-sm font-semibold text-slate-600 transition hover:bg-slate-50 hover:text-blue-900">Open shop ↗</a></nav></aside>
        <main class="min-w-0">@if (session('status'))<p class="mb-4 rounded-lg border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-900" role="status">{{ session('status') }}</p>@endif @if ($errors->any())<div class="mb-4 rounded-lg border border-rose-200 bg-rose-50 px-4 py-3 text-sm text-rose-900" role="alert">@foreach ($errors->all() as $error)<p>{{ $error }}</p>@endforeach</div>@endif @yield('content')</main>
    </div>
</body>
</html>
