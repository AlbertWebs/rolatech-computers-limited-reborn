<?php

use App\Http\Controllers\Admin\AdminAuthController;
use App\Http\Controllers\Admin\AuditLogController;
use App\Http\Controllers\Admin\BlogPostController;
use App\Http\Controllers\Admin\CatalogTaxonomyController;
use App\Http\Controllers\Admin\ContactInquiryController;
use App\Http\Controllers\Admin\DashboardController as AdminDashboardController;
use App\Http\Controllers\Admin\HireRequestController as AdminHireRequestController;
use App\Http\Controllers\Admin\OrderController as AdminOrderController;
use App\Http\Controllers\Admin\ProductController as AdminProductController;
use App\Http\Controllers\BlogController;
use App\Http\Controllers\CartController;
use App\Http\Controllers\CustomerAccountController;
use App\Http\Controllers\HireRequestController;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\PageController;
use App\Http\Controllers\SitemapController;
use App\Http\Controllers\StorefrontController;
use App\Http\Controllers\WishlistController;
use Illuminate\Support\Facades\Route;

Route::get('/', [StorefrontController::class, 'home'])->name('home');
Route::get('/sitemap.xml', SitemapController::class)->name('sitemap');
Route::get('/the-company', [PageController::class, 'company'])->name('company');
Route::get('/contact-us', [PageController::class, 'contact'])->name('contact');
Route::post('/send-message', [PageController::class, 'message'])->middleware('throttle:5,1')->name('contact.message');
Route::get('/blog', [BlogController::class, 'index'])->name('blog.index');
Route::get('/blogs/{slug}', [BlogController::class, 'show'])->name('blogs');
Route::get('/e-commerce', [StorefrontController::class, 'index'])->name('catalog.index');
Route::get('/e-commerce/search-results', [StorefrontController::class, 'index'])->name('catalog.search');
Route::get('/e-commerce/quick-view/{slug}', [StorefrontController::class, 'quickView'])->name('catalog.quick-view');
Route::get('/e-commerce/product/{slug}', [StorefrontController::class, 'show'])->name('catalog.show');
Route::get('/e-commerce/product/brand/{slug}', [StorefrontController::class, 'brand'])->name('catalog.brand');
Route::get('/e-commerce/product/tags/{slug}', [StorefrontController::class, 'category'])->name('catalog.category');
Route::get('/cart', [CartController::class, 'index'])->name('cart.index');
Route::get('/wishlist', [WishlistController::class, 'index'])->name('wishlist.index');
Route::post('/wishlist/{product}', [WishlistController::class, 'store'])->name('wishlist.store');
Route::delete('/wishlist/{product}', [WishlistController::class, 'remove'])->name('wishlist.remove');
Route::get('/e-commerce/shopping-cart/add-to-wishlist/{slug}', [WishlistController::class, 'legacyAdd'])->name('wishlist.legacy-add');
Route::post('/cart/items/{product}', [CartController::class, 'store'])->name('cart.store');
Route::patch('/cart/items/{product}', [CartController::class, 'update'])->name('cart.update');
Route::delete('/cart/items/{product}', [CartController::class, 'destroy'])->name('cart.destroy');
Route::get('/checkout', [OrderController::class, 'create'])->name('checkout.create');
Route::post('/checkout', [OrderController::class, 'store'])->name('checkout.store')->middleware('throttle:6,1');
Route::get('/orders/{orderNumber}', [OrderController::class, 'confirmation'])->name('checkout.confirmation');
Route::get('/login', [CustomerAccountController::class, 'login'])->name('login');
Route::post('/login', [CustomerAccountController::class, 'authenticate'])->middleware('throttle:5,1')->name('login.store');
Route::get('/register', [CustomerAccountController::class, 'register'])->name('register');
Route::post('/register', [CustomerAccountController::class, 'store'])->middleware('throttle:5,1')->name('register.store');
Route::middleware('auth')->prefix('account')->name('account.')->group(function (): void {
    Route::get('/profile', [CustomerAccountController::class, 'profile'])->name('profile');
    Route::put('/profile', [CustomerAccountController::class, 'updateProfile'])->name('profile.update');
    Route::get('/orders', [CustomerAccountController::class, 'orders'])->name('orders');
    Route::get('/orders/{orderNumber}', [CustomerAccountController::class, 'showOrder'])->name('orders.show');
});
Route::post('/logout', [CustomerAccountController::class, 'logout'])->middleware('auth')->name('logout');
Route::get('/laptops-for-hire', [HireRequestController::class, 'create'])->defaults('type', 'laptop')->name('laptops-for-hire');
Route::post('/laptops-for-hire', [HireRequestController::class, 'store'])->middleware('throttle:5,1');
Route::get('/macbook-for-hire', [HireRequestController::class, 'create'])->defaults('type', 'macbook')->name('macbook');
Route::get('/tablets-for-hire', [HireRequestController::class, 'create'])->defaults('type', 'tablet')->name('tablets');
Route::get('/projectors-for-hire', [HireRequestController::class, 'create'])->defaults('type', 'projector')->name('projectors-for-hire');
Route::get('/recommended', [HireRequestController::class, 'create'])->defaults('type', 'laptop')->name('hire-secondary');
Route::get('/hire/{type?}', [HireRequestController::class, 'create'])->whereIn('type', ['laptop', 'macbook', 'tablet', 'projector'])->name('hire.create');
Route::post('/hire-requests', [HireRequestController::class, 'store'])->middleware('throttle:5,1')->name('hire.store');

Route::prefix('admin')->name('admin.')->group(function (): void {
    Route::get('/login', [AdminAuthController::class, 'create'])->name('login');
    Route::post('/login', [AdminAuthController::class, 'store'])->name('login.store')->middleware('throttle:5,1');
    Route::post('/logout', [AdminAuthController::class, 'destroy'])->name('logout')->middleware(['admin', 'auth']);

    Route::middleware(['admin', 'auth'])->group(function (): void {
        Route::get('/', [AdminDashboardController::class, 'index'])->name('dashboard');
        Route::middleware('admin.role:manager,catalog')->group(function (): void {
            Route::resource('products', AdminProductController::class)->except(['show', 'destroy']);
            Route::patch('/products/{product}/visibility', [AdminProductController::class, 'visibility'])->name('products.visibility');
            Route::get('/{taxonomy}', [CatalogTaxonomyController::class, 'index'])->name('taxonomy.index')->whereIn('taxonomy', ['categories', 'brands']);
            Route::get('/{taxonomy}/create', [CatalogTaxonomyController::class, 'create'])->name('taxonomy.create')->whereIn('taxonomy', ['categories', 'brands']);
            Route::post('/{taxonomy}', [CatalogTaxonomyController::class, 'store'])->name('taxonomy.store')->whereIn('taxonomy', ['categories', 'brands']);
            Route::get('/{taxonomy}/{record}/edit', [CatalogTaxonomyController::class, 'edit'])->name('taxonomy.edit')->whereIn('taxonomy', ['categories', 'brands'])->whereNumber('record');
            Route::put('/{taxonomy}/{record}', [CatalogTaxonomyController::class, 'update'])->name('taxonomy.update')->whereIn('taxonomy', ['categories', 'brands'])->whereNumber('record');
            Route::patch('/{taxonomy}/{record}/visibility', [CatalogTaxonomyController::class, 'visibility'])->name('taxonomy.visibility')->whereIn('taxonomy', ['categories', 'brands'])->whereNumber('record');
        });
        Route::middleware('admin.role:manager,operations')->group(function (): void {
            Route::get('/orders', [AdminOrderController::class, 'index'])->name('orders.index');
            Route::get('/orders/{order}', [AdminOrderController::class, 'show'])->name('orders.show');
            Route::patch('/orders/{order}', [AdminOrderController::class, 'update'])->name('orders.update');
            Route::get('/hire-requests', [AdminHireRequestController::class, 'index'])->name('hire.index');
            Route::get('/hire-requests/{hireRequest}', [AdminHireRequestController::class, 'show'])->name('hire.show');
            Route::patch('/hire-requests/{hireRequest}', [AdminHireRequestController::class, 'update'])->name('hire.update');
            Route::get('/contact-enquiries', [ContactInquiryController::class, 'index'])->name('contact.index');
            Route::get('/contact-enquiries/{inquiry}', [ContactInquiryController::class, 'show'])->name('contact.show');
            Route::patch('/contact-enquiries/{inquiry}', [ContactInquiryController::class, 'update'])->name('contact.update');
        });
        Route::middleware('admin.role:manager,owner')->get('/audit-log', [AuditLogController::class, 'index'])->name('audit.index');
        Route::middleware('admin.role:manager,content')->resource('blog-posts', BlogPostController::class)->names('blog')->parameters(['blog-posts' => 'blog'])->except(['show']);
    });
});
