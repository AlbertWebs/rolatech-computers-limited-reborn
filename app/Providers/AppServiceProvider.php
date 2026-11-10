<?php

namespace App\Providers;

use App\Models\BlogPost;
use App\Models\Brand;
use App\Models\Category;
use App\Models\ContactInquiry;
use App\Models\LaptopHireRequest;
use App\Models\Order;
use App\Models\Product;
use App\Observers\AdminAuditObserver;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        foreach ([BlogPost::class, Brand::class, Category::class, ContactInquiry::class, LaptopHireRequest::class, Order::class, Product::class] as $model) {
            $model::observe(AdminAuditObserver::class);
        }
    }
}
