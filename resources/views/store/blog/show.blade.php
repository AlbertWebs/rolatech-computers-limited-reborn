@extends('store.layout')
@section('title', $post->title.' | Royaltech')
@section('canonical', route('blogs', $post->slug))
@section('content')
@push('head')@if($post->meta_description)<meta name="description" content="{{ $post->meta_description }}">@elseif($post->excerpt)<meta name="description" content="{{ Str::limit($post->excerpt, 155) }}">@endif<meta property="og:type" content="article"><meta property="og:title" content="{{ $post->title }}">@if($post->cover_image_path)<meta property="og:image" content="{{ asset($post->cover_image_path) }}">@endif
@endpush
<article class="mx-auto max-w-3xl px-4 py-12 sm:px-6"><a href="{{ route('blog.index') }}" class="text-sm font-semibold text-blue-800">← Articles</a><p class="mt-6 text-xs text-slate-500">{{ $post->published_at?->format('d M Y') }}</p><h1 class="mt-2 font-display text-4xl font-extrabold leading-tight text-[#142b50]">{{ $post->title }}</h1>@if($post->excerpt)<p class="mt-4 text-lg leading-7 text-slate-600">{{ $post->excerpt }}</p>@endif @if($post->cover_image_path)<img src="{{ asset($post->cover_image_path) }}" alt="{{ $post->title }}" class="mt-8 w-full rounded-xl">@endif<div class="mt-8 whitespace-pre-line text-base leading-8 text-slate-700">{{ $post->body }}</div></article>
@endsection
