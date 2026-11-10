<?php

namespace App\Http\Controllers;

use App\Models\BlogPost;
use App\Models\Brand;
use App\Models\Category;
use App\Models\Product;
use Illuminate\Http\Response;

class SitemapController extends Controller
{
    public function __invoke(): Response
    {
        $urls = collect([
            ['loc' => route('home'), 'lastmod' => now()->toAtomString()],
            ['loc' => route('catalog.index'), 'lastmod' => now()->toAtomString()],
            ['loc' => route('company'), 'lastmod' => now()->toAtomString()],
            ['loc' => route('contact'), 'lastmod' => now()->toAtomString()],
            ['loc' => route('laptops-for-hire'), 'lastmod' => now()->toAtomString()],
            ['loc' => route('macbook'), 'lastmod' => now()->toAtomString()],
            ['loc' => route('tablets'), 'lastmod' => now()->toAtomString()],
            ['loc' => route('projectors-for-hire'), 'lastmod' => now()->toAtomString()],
        ]);
        Product::query()->where('is_active', true)->select(['slug', 'updated_at'])->chunk(500, function ($products) use ($urls): void {
            foreach ($products as $product) {
                $urls->push(['loc' => route('catalog.show', $product->slug), 'lastmod' => $product->updated_at?->toAtomString()]);
            }
        });
        Category::query()->where('is_active', true)->select(['slug', 'updated_at'])->chunk(500, function ($categories) use ($urls): void {
            foreach ($categories as $category) {
                $urls->push(['loc' => route('catalog.category', $category->slug), 'lastmod' => $category->updated_at?->toAtomString()]);
            }
        });
        Brand::query()->where('is_active', true)->select(['slug', 'updated_at'])->chunk(500, function ($brands) use ($urls): void {
            foreach ($brands as $brand) {
                $urls->push(['loc' => route('catalog.brand', $brand->slug), 'lastmod' => $brand->updated_at?->toAtomString()]);
            }
        });
        BlogPost::query()->where('is_published', true)->where('published_at', '<=', now())->select(['slug', 'updated_at'])->chunk(500, function ($posts) use ($urls): void {
            foreach ($posts as $post) {
                $urls->push(['loc' => route('blogs', $post->slug), 'lastmod' => $post->updated_at?->toAtomString()]);
            }
        });

        return response()->view('seo.sitemap', ['urls' => $urls])->header('Content-Type', 'application/xml; charset=UTF-8');
    }
}
