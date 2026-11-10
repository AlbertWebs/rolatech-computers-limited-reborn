<?php

namespace App\Http\Controllers;

use App\Models\BlogPost;
use Illuminate\View\View;

class BlogController extends Controller
{
    public function index(): View
    {
        return view('store.blog.index', ['posts' => BlogPost::query()->where('is_published', true)->where('published_at', '<=', now())->latest('published_at')->paginate(12)]);
    }

    public function show(string $slug): View
    {
        $post = BlogPost::query()->where('slug', $slug)->where('is_published', true)->where('published_at', '<=', now())->firstOrFail();

        return view('store.blog.show', compact('post'));
    }
}
