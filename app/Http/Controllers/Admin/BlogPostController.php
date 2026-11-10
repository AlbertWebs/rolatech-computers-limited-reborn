<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\BlogPost;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class BlogPostController extends Controller
{
    public function index(): View
    {
        return view('admin.blog.index', ['posts' => BlogPost::query()->latest()->paginate(25)]);
    }

    public function create(): View
    {
        return view('admin.blog.form', ['post' => new BlogPost]);
    }

    public function store(Request $request): RedirectResponse
    {
        $post = new BlogPost;
        $this->savePost($request, $post);

        return redirect()->route('admin.blog.index')->with('status', 'Article saved.');
    }

    public function edit(BlogPost $blog): View
    {
        return view('admin.blog.form', ['post' => $blog]);
    }

    public function update(Request $request, BlogPost $blog): RedirectResponse
    {
        $this->savePost($request, $blog);

        return redirect()->route('admin.blog.index')->with('status', 'Article updated.');
    }

    public function destroy(BlogPost $blog): RedirectResponse
    {
        if ($blog->cover_image_path) {
            Storage::disk('public')->delete(Str::after($blog->cover_image_path, 'storage/'));
        }
        $blog->delete();

        return back()->with('status', 'Article deleted.');
    }

    private function savePost(Request $request, BlogPost $post): void
    {
        $data = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'slug' => ['nullable', 'string', 'max:255', 'alpha_dash', Rule::unique('blog_posts', 'slug')->ignore($post->id)],
            'excerpt' => ['nullable', 'string', 'max:500'],
            'body' => ['required', 'string', 'max:50000'],
            'meta_description' => ['nullable', 'string', 'max:160'],
            'cover_image' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp,avif', 'max:5120'],
            'is_published' => ['sometimes', 'boolean'],
        ]);
        $post->title = $data['title'];
        $post->slug = ($data['slug'] ?? '') !== '' ? Str::slug($data['slug']) : Str::slug($data['title']);
        if (BlogPost::query()->where('slug', $post->slug)->when($post->exists, fn ($query) => $query->whereKeyNot($post->getKey()))->exists()) {
            throw ValidationException::withMessages(['slug' => 'This title or URL slug is already in use.']);
        }
        $post->excerpt = $data['excerpt'] ?? null;
        $post->body = $data['body'];
        $post->meta_description = $data['meta_description'] ?? null;
        $post->is_published = $request->boolean('is_published');
        $post->published_at = $post->is_published ? ($post->published_at ?? now()) : null;
        if ($request->hasFile('cover_image')) {
            $path = $request->file('cover_image')->storePublicly('blog', 'public');
            if (! $path) {
                throw ValidationException::withMessages(['cover_image' => 'The image could not be saved. Please try again.']);
            }
            if ($post->cover_image_path) {
                Storage::disk('public')->delete(Str::after($post->cover_image_path, 'storage/'));
            }
            $post->cover_image_path = 'storage/'.$path;
        }
        $post->save();
    }
}
