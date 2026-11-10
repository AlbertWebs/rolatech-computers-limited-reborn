<?php

namespace Tests\Feature;

use App\Models\BlogPost;
use App\Models\Brand;
use App\Models\Category;
use App\Models\Order;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Tests\TestCase;

class StorefrontCatalogTest extends TestCase
{
    use LazilyRefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutVite();
    }

    public function test_homepage_renders_royaltech_content_and_empty_catalogue_state(): void
    {
        $this->get('/')
            ->assertOk()
            ->assertSee('Technology that moves')
            ->assertSee('Our product catalogue is being prepared.')
            ->assertSee('assets/legacy/logo/Royaltech-Original-1.png');
    }

    public function test_sitemap_lists_public_catalog_and_excludes_private_workflows(): void
    {
        $product = Product::factory()->create(['slug' => 'sitemap-laptop']);

        $this->get(route('sitemap'))->assertOk()->assertHeader('content-type', 'application/xml; charset=UTF-8')
            ->assertSee(route('catalog.show', $product->slug), false)
            ->assertDontSee('/admin/login');
    }

    public function test_catalog_search_and_price_sort_return_matching_products(): void
    {
        Product::factory()->create(['name' => 'Royaltech Study Laptop', 'price' => 40000]);
        Product::factory()->create(['name' => 'Royaltech Pro Laptop', 'price' => 90000]);
        Product::factory()->create(['name' => 'Royaltech Desktop', 'price' => 55000]);

        $this->get(route('catalog.search', ['q' => 'Laptop', 'sort' => 'price_asc']))
            ->assertOk()
            ->assertSeeInOrder(['Royaltech Study Laptop', 'Royaltech Pro Laptop'])
            ->assertDontSee('Royaltech Desktop');
    }

    public function test_catalog_rejects_unapproved_sort_parameters(): void
    {
        $this->from(route('catalog.index'))
            ->get(route('catalog.index', ['sort' => 'name;drop table products']))
            ->assertRedirect(route('catalog.index'))
            ->assertSessionHasErrors('sort');
    }

    public function test_product_detail_renders_escaped_description_and_structured_data(): void
    {
        $product = Product::factory()->create([
            'name' => 'Royaltech Test Laptop',
            'description' => '<script>alert("xss")</script>Quality laptop for work.',
            'sku' => 'RT-TEST-01',
        ]);

        $this->get(route('catalog.show', $product->slug))
            ->assertOk()
            ->assertSee('RT-TEST-01')
            ->assertSee('alert("xss")Quality laptop for work.')
            ->assertDontSee('&lt;script&gt;', false)
            ->assertDontSee('<script>alert("xss")</script>', false)
            ->assertSee('"@type":"Product"', false);
    }

    public function test_legacy_quick_view_url_renders_product_and_points_canonical_to_detail(): void
    {
        $product = Product::factory()->create(['name' => 'Royaltech Quick View Laptop']);

        $this->get(route('catalog.quick-view', $product->slug))
            ->assertOk()->assertSee('Royaltech Quick View Laptop')
            ->assertSee('noindex,follow', false)
            ->assertSee(route('catalog.show', $product->slug), false);
    }

    public function test_add_to_cart_stores_quantity_and_refuses_more_than_known_stock(): void
    {
        $product = Product::factory()->create(['stock_quantity' => 3]);

        $this->post(route('cart.store', $product), ['quantity' => 2])
            ->assertRedirect()
            ->assertSessionHas('cart', [$product->id => 2]);

        $this->from(route('catalog.show', $product->slug))
            ->post(route('cart.store', $product), ['quantity' => 2])
            ->assertRedirect(route('catalog.show', $product->slug))
            ->assertSessionHasErrors('quantity')
            ->assertSessionHas('cart', [$product->id => 2]);
    }

    public function test_wishlist_preserves_legacy_add_url_and_is_deduplicated_in_session(): void
    {
        $product = Product::factory()->create(['slug' => 'wishlist-laptop']);

        $this->get(route('wishlist.legacy-add', $product->slug))
            ->assertOk()->assertJsonPath('name', $product->name)
            ->assertSessionHas('wishlist', [$product->id]);
        $this->post(route('wishlist.store', $product))->assertRedirect()
            ->assertSessionHas('wishlist', [$product->id]);
        $this->get(route('wishlist.index'))->assertOk()->assertSee($product->name);
        $this->delete(route('wishlist.remove', $product))->assertRedirect()->assertSessionHas('wishlist', []);
    }

    public function test_cart_updates_and_removes_items(): void
    {
        $product = Product::factory()->create(['stock_quantity' => 4]);

        $this->withSession(['cart' => [$product->id => 1]])
            ->patch(route('cart.update', $product), ['quantity' => 3])
            ->assertRedirect()
            ->assertSessionHas('cart', [$product->id => 3]);

        $this->delete(route('cart.destroy', $product))
            ->assertRedirect()
            ->assertSessionHas('cart', []);
    }

    public function test_inactive_products_are_not_public_or_addable_to_cart(): void
    {
        $product = Product::factory()->create(['is_active' => false]);

        $this->get(route('catalog.show', $product->slug))->assertNotFound();
        $this->post(route('cart.store', $product))->assertNotFound();
    }

    public function test_brand_and_category_filters_only_show_matching_products(): void
    {
        $brand = Brand::factory()->create(['name' => 'Royaltech Brand']);
        $category = Category::factory()->create(['name' => 'Laptops']);
        Product::factory()->create(['name' => 'Matching Product', 'brand_id' => $brand->id, 'category_id' => $category->id]);
        Product::factory()->create(['name' => 'Other Product']);

        $this->get(route('catalog.brand', $brand->slug))
            ->assertOk()
            ->assertSee('Matching Product')
            ->assertDontSee('Other Product');

        $this->get(route('catalog.category', $category->slug))
            ->assertOk()
            ->assertSee('Matching Product')
            ->assertDontSee('Other Product');
    }

    public function test_catalog_import_reads_only_catalog_tables_and_is_repeatable(): void
    {
        $sqlFile = base_path('tests/Fixtures/legacy-catalog.sql');

        $this->artisan('catalog:import-legacy', ['sql-file' => $sqlFile])
            ->expectsOutputToContain('Catalog import completed')
            ->assertSuccessful();

        $this->artisan('catalog:import-legacy', ['sql-file' => $sqlFile])
            ->assertSuccessful();

        $this->assertDatabaseCount('categories', 1);
        $this->assertDatabaseCount('brands', 1);
        $this->assertDatabaseCount('products', 1);
        $this->assertDatabaseHas('products', [
            'legacy_id' => 88,
            'name' => 'Work Laptop',
            'brand_id' => 1,
            'category_id' => 1,
            'price' => '74999.00',
            'description' => "Work, study and O'Reilly; ready.",
        ]);
    }

    public function test_guest_checkout_creates_an_unpaid_order_with_price_snapshots(): void
    {
        $product = Product::factory()->create(['price' => 1250.50, 'sku' => 'RT-CHECKOUT-1']);

        $this->withSession(['cart' => [$product->id => 2]])
            ->post(route('checkout.store'), [
                'full_name' => 'Customer Name',
                'email' => 'customer@example.com',
                'phone' => '+254 712 345 678',
                'delivery_address' => 'Biashara Street, Building 2',
                'delivery_city' => 'Nairobi',
                'customer_notes' => 'Call on arrival.',
            ])
            ->assertRedirect();

        $order = Order::query()->with('items')->where('email', 'customer@example.com')->firstOrFail();
        $this->assertDatabaseHas('orders', [
            'id' => $order->id,
            'status' => 'awaiting_confirmation',
            'payment_status' => 'unpaid',
            'currency' => 'KES',
            'subtotal' => '2501.00',
        ]);
        $this->assertDatabaseHas('order_items', [
            'order_id' => $order->id,
            'product_id' => $product->id,
            'product_name' => $product->name,
            'quantity' => 2,
            'unit_price' => '1250.50',
            'line_total' => '2501.00',
        ]);

        $this->get(route('checkout.confirmation', $order->order_number))
            ->assertOk()
            ->assertSee('No payment has been taken.');
    }

    public function test_checkout_rejects_invalid_customer_details_without_creating_an_order(): void
    {
        $product = Product::factory()->create();

        $this->withSession(['cart' => [$product->id => 1]])
            ->from(route('checkout.create'))
            ->post(route('checkout.store'), [
                'full_name' => 'Customer Name',
                'email' => 'not-an-email',
                'phone' => 'x',
                'delivery_address' => 'x',
                'delivery_city' => 'x',
            ])
            ->assertRedirect(route('checkout.create'))
            ->assertSessionHasErrors(['email', 'phone', 'delivery_address', 'delivery_city']);

        $this->assertDatabaseCount('orders', 0);
    }

    public function test_checkout_rejects_unavailable_cart_items(): void
    {
        $product = Product::factory()->create(['stock_quantity' => 1]);

        $this->withSession(['cart' => [$product->id => 2]])
            ->get(route('checkout.create'))
            ->assertRedirect(route('cart.index'));

        $this->assertDatabaseCount('orders', 0);
    }

    public function test_legacy_laptop_hire_form_creates_request_after_arithmetic_check(): void
    {
        $this->get('/laptops-for-hire')->assertOk()->assertSee('Request laptop hire');
        $answer = session('hire_challenge.answer');

        $this->post('/laptops-for-hire', [
            'equipment_type' => 'laptop', 'name' => 'Hire Customer', 'email' => 'hire@example.com',
            'phone' => '+254 712 345 678', 'pickup_date' => now()->addDays(3)->toDateString(),
            'number_of_items' => 4, 'desired_specs' => 'Four laptops for training.', 'verify_contact_input' => $answer,
        ])->assertRedirect(route('hire.create', ['type' => 'laptop']))->assertSessionHas('status');

        $this->assertDatabaseHas('laptop_hire_requests', ['email' => 'hire@example.com', 'status' => 'pending', 'number_of_items' => 4]);
    }

    public function test_hire_form_rejects_wrong_arithmetic_answer(): void
    {
        $this->get('/laptops-for-hire');
        $this->post('/laptops-for-hire', [
            'equipment_type' => 'laptop', 'name' => 'Hire Customer', 'email' => 'hire@example.com',
            'phone' => '+254 712 345 678', 'pickup_date' => now()->addDays(3)->toDateString(),
            'number_of_items' => 4, 'desired_specs' => 'Four laptops.', 'verify_contact_input' => -1,
        ])->assertRedirect()->assertSessionHasErrors('verify_contact_input');
        $this->assertDatabaseCount('laptop_hire_requests', 0);
    }

    public function test_contact_page_records_valid_enquiry_for_staff_follow_up(): void
    {
        $this->get('/the-company')->assertOk()->assertSee('Technology for work and home');
        $this->get('/contact-us')->assertOk()->assertSee('+254 724 404 935');
        $this->post('/send-message', [
            'name' => 'Contact Customer', 'email' => 'contact@example.com', 'phone' => '+254 712 345 678',
            'subject' => 'Laptop enquiry', 'message' => 'Please advise on available business laptops.',
        ])->assertRedirect()->assertSessionHas('status');

        $this->assertDatabaseHas('contact_inquiries', ['email' => 'contact@example.com', 'status' => 'new']);
    }

    public function test_customer_can_register_and_access_account_order_history(): void
    {
        $this->get('/account/orders')->assertRedirect(route('login'));
        $this->post(route('register.store'), [
            'name' => 'Account Customer', 'email' => 'account@example.com',
            'password' => 'Correct-Horse-Battery-9!', 'password_confirmation' => 'Correct-Horse-Battery-9!',
        ])->assertRedirect(route('account.orders'))->assertSessionHas('status');

        $this->assertAuthenticated();
        $this->get(route('account.orders'))->assertOk()->assertSee('Your order requests');
    }

    public function test_customer_order_history_is_limited_to_the_authenticated_account(): void
    {
        $customer = User::factory()->create();
        $other = User::factory()->create();
        Order::factory()->create(['user_id' => $customer->id, 'order_number' => 'RT-OWNED-ORDER']);
        Order::factory()->create(['user_id' => $other->id, 'order_number' => 'RT-OTHER-ORDER']);

        $this->actingAs($customer)->get(route('account.orders'))
            ->assertOk()->assertSee('RT-OWNED-ORDER')->assertDontSee('RT-OTHER-ORDER');
        $this->actingAs($customer)->get(route('account.orders.show', 'RT-OTHER-ORDER'))->assertNotFound();
    }

    public function test_customer_can_update_profile_and_change_password_with_current_password(): void
    {
        $customer = User::factory()->create(['name' => 'Old Name', 'password' => 'old-password-value']);

        $this->actingAs($customer)->get(route('account.profile'))->assertOk()->assertSee($customer->email);
        $this->put(route('account.profile.update'), [
            'name' => 'Updated Customer', 'phone' => '+254 722 123 456',
            'current_password' => 'old-password-value', 'password' => 'New-Secure-Password-9!',
            'password_confirmation' => 'New-Secure-Password-9!',
        ])->assertRedirect()->assertSessionHas('status', 'Your profile has been updated.');

        $this->assertDatabaseHas('users', ['id' => $customer->id, 'name' => 'Updated Customer', 'phone' => '+254 722 123 456']);
        $this->post(route('login.store'), ['email' => $customer->email, 'password' => 'New-Secure-Password-9!'])->assertRedirect(route('account.orders'));
    }

    public function test_blog_shows_published_articles_only_and_escapes_body_text(): void
    {
        BlogPost::query()->create(['title' => 'Published Article', 'slug' => 'published-article', 'body' => '<script>alert(1)</script> Safe text', 'is_published' => true, 'published_at' => now()->subDay()]);
        BlogPost::query()->create(['title' => 'Draft Article', 'slug' => 'draft-article', 'body' => 'Draft text', 'is_published' => false]);

        $this->get(route('blog.index'))->assertOk()->assertSee('Published Article')->assertDontSee('Draft Article');
        $this->get(route('blogs', 'published-article'))->assertOk()->assertSee('&lt;script&gt;', false)->assertDontSee('<script>alert(1)</script>', false);
        $this->get(route('blogs', 'draft-article'))->assertNotFound();
    }

    public function test_legacy_blog_import_is_repeatable_and_strips_executable_markup(): void
    {
        $file = base_path('tests/Fixtures/legacy-blog.sql');
        $this->artisan('catalog:import-legacy', ['sql-file' => $file, '--include-blog' => true])->assertSuccessful();
        $this->artisan('catalog:import-legacy', ['sql-file' => $file, '--include-blog' => true])->assertSuccessful();

        $this->assertDatabaseCount('blog_posts', 1);
        $post = BlogPost::query()->firstOrFail();
        $this->assertSame('Hello world alert(1)', trim(preg_replace('/\s+/', ' ', $post->body)));
    }
}
