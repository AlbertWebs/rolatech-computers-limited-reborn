<!doctype html>
<html lang="en"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1"><meta name="csrf-token" content="{{ csrf_token() }}"><title>Administrator sign in | Royaltech</title>@vite(['resources/css/app.css', 'resources/js/app.js'])</head>
<body class="grid min-h-screen place-items-center bg-slate-100 px-4 py-12">
    <main class="w-full max-w-md rounded-2xl border border-slate-200 bg-white p-7 shadow-sm sm:p-9">
        <a href="{{ route('home') }}" class="inline-flex items-center gap-3"><img src="{{ asset('assets/legacy/logo/Royaltech-Original-1.png') }}" alt="Royaltech Computers Limited" class="h-10 w-auto"><span class="h-8 border-l border-slate-200"></span><span class="text-xs font-bold uppercase tracking-wider text-slate-500">Admin</span></a>
        <h1 class="mt-8 font-display text-2xl font-extrabold text-[#142b50]">Administrator sign in</h1><p class="mt-2 text-sm text-slate-600">Sign in with an active Royaltech administrator account.</p>
        @if ($errors->any())<div class="mt-5 rounded-lg border border-rose-200 bg-rose-50 px-4 py-3 text-sm text-rose-900" role="alert">{{ $errors->first() }}</div>@endif
        <form action="{{ route('admin.login.store') }}" method="POST" class="mt-6 space-y-4">@csrf
            <div><label for="email" class="mb-1.5 block text-sm font-semibold text-slate-800">Work email</label><input id="email" name="email" type="email" value="{{ old('email') }}" autocomplete="username" required autofocus class="w-full rounded-lg border border-slate-300 px-3.5 py-3 text-sm focus:border-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-700/15"></div>
            <div><label for="password" class="mb-1.5 block text-sm font-semibold text-slate-800">Password</label><input id="password" name="password" type="password" autocomplete="current-password" required class="w-full rounded-lg border border-slate-300 px-3.5 py-3 text-sm focus:border-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-700/15"></div>
            <label class="flex items-center gap-2 text-sm text-slate-600"><input type="checkbox" name="remember" value="1" class="rounded border-slate-300 text-blue-800 focus:ring-blue-700"> Keep me signed in</label>
            <button type="submit" class="w-full rounded-lg bg-blue-800 px-5 py-3.5 text-sm font-bold text-white hover:bg-blue-900">Sign in</button>
        </form>
        <p class="mt-6 border-t border-slate-100 pt-5 text-center text-xs text-slate-500"><a href="{{ route('home') }}" class="font-semibold text-blue-800 hover:underline">Back to the Royaltech website</a></p>
    </main>
</body></html>
