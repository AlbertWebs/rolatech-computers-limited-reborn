<?php

namespace Tests\Feature;

use App\Models\BlogPost;
use App\Models\Brand;
use App\Models\Category;
use App\Models\ContactInquiry;
use App\Models\LaptopHireRequest;
use App\Models\Order;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Tests\TestCase;

class AdminAccessTest extends TestCase
{
    use LazilyRefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutVite();
    }

    public function test_guest_is_redirected_to_admin_sign_in(): void
    {
        $this->get(route('admin.dashboard'))->assertRedirect(route('admin.login'));
    }

    public function test_non_admin_user_is_forbidden_from_the_dashboard(): void
    {
        $user = User::factory()->create(['is_admin' => false]);

        $this->actingAs($user)->get(route('admin.dashboard'))->assertForbidden();
    }

    public function test_catalog_admin_role_cannot_access_order_or_content_operations(): void
    {
        $admin = User::factory()->create(['is_admin' => true, 'admin_role' => 'catalog']);

        $this->actingAs($admin)->get(route('admin.products.index'))->assertOk();
        $this->get(route('admin.orders.index'))->assertForbidden();
        $this->get(route('admin.blog.index'))->assertForbidden();
        $this->get(route('admin.audit.index'))->assertForbidden();
    }

    public function test_inactive_admin_cannot_sign_in(): void
    {
        $admin = User::factory()->create(['is_admin' => true, 'is_active' => false]);

        $this->post(route('admin.login.store'), ['email' => $admin->email, 'password' => 'password'])
            ->assertRedirect()
            ->assertSessionHasErrors('email');

        $this->assertGuest();
    }

    public function test_active_admin_can_sign_in_and_see_live_dashboard_counts(): void
    {
        $admin = User::factory()->create(['is_admin' => true]);
        Product::factory()->create();

        $this->post(route('admin.login.store'), ['email' => $admin->email, 'password' => 'password'])
            ->assertRedirect(route('admin.dashboard'));

        $this->get(route('admin.dashboard'))
            ->assertOk()
            ->assertSee('Store overview')
            ->assertSee('Catalogue products')
            ->assertSee('1');
    }

    public function test_admin_can_create_a_product_from_validated_catalogue_data(): void
    {
        $admin = User::factory()->create(['is_admin' => true]);
        $category = Category::factory()->create();
        $brand = Brand::factory()->create();

        $this->actingAs($admin)
            ->post(route('admin.products.store'), [
                'name' => 'Royaltech Admin Created Laptop',
                'sku' => 'RT-ADMIN-1',
                'category_id' => $category->id,
                'brand_id' => $brand->id,
                'price' => 72500,
                'stock_quantity' => 4,
                'stock_label' => 'In Stock',
                'is_active' => '1',
                'is_featured' => '1',
            ])
            ->assertRedirect(route('admin.products.index'));

        $this->assertDatabaseHas('products', [
            'name' => 'Royaltech Admin Created Laptop',
            'slug' => 'royaltech-admin-created-laptop',
            'sku' => 'RT-ADMIN-1',
            'category_id' => $category->id,
            'brand_id' => $brand->id,
            'price' => '72500.00',
            'stock_quantity' => 4,
            'is_active' => true,
            'is_featured' => true,
        ]);
    }

    public function test_admin_can_update_an_order_request_status(): void
    {
        $admin = User::factory()->create(['is_admin' => true]);
        $order = Order::factory()->create();

        $this->actingAs($admin)
            ->patch(route('admin.orders.update', $order), ['status' => 'confirmed'])
            ->assertRedirect()
            ->assertSessionHas('status', 'Order status updated.');

        $this->assertDatabaseHas('orders', ['id' => $order->id, 'status' => 'confirmed', 'payment_status' => 'unpaid']);
        $this->assertDatabaseHas('admin_audit_logs', [
            'actor_id' => $admin->id,
            'event' => 'updated',
            'subject_type' => Order::class,
            'subject_id' => $order->id,
        ]);
        $this->get(route('admin.audit.index'))->assertOk()->assertSee('Order #'.$order->id)->assertSee('status');
    }

    public function test_admin_cannot_set_an_unrecognized_order_status(): void
    {
        $admin = User::factory()->create(['is_admin' => true]);
        $order = Order::factory()->create();

        $this->actingAs($admin)
            ->from(route('admin.orders.show', $order))
            ->patch(route('admin.orders.update', $order), ['status' => 'paid_and_sent'])
            ->assertSessionHasErrors('status');

        $this->assertDatabaseHas('orders', ['id' => $order->id, 'status' => 'awaiting_confirmation']);
    }

    public function test_admin_can_create_a_store_category(): void
    {
        $admin = User::factory()->create(['is_admin' => true]);

        $this->actingAs($admin)
            ->post(route('admin.taxonomy.store', ['taxonomy' => 'categories']), [
                'name' => 'Networking Equipment',
                'description' => 'Routers and connectivity products.',
                'sort_order' => 2,
                'is_active' => '1',
            ])
            ->assertRedirect(route('admin.taxonomy.index', ['taxonomy' => 'categories']));

        $this->assertDatabaseHas('categories', [
            'name' => 'Networking Equipment',
            'slug' => 'networking-equipment',
            'description' => 'Routers and connectivity products.',
            'sort_order' => 2,
            'is_active' => true,
        ]);
    }

    public function test_admin_can_create_a_brand_and_hide_it_without_deleting_product_links(): void
    {
        $admin = User::factory()->create(['is_admin' => true]);

        $this->actingAs($admin)
            ->post(route('admin.taxonomy.store', ['taxonomy' => 'brands']), [
                'name' => 'Royaltech Partner Brand',
                'is_active' => '1',
            ])
            ->assertRedirect(route('admin.taxonomy.index', ['taxonomy' => 'brands']));

        $brand = Brand::query()->where('slug', 'royaltech-partner-brand')->firstOrFail();
        Product::factory()->create(['brand_id' => $brand->id]);

        $this->patch(route('admin.taxonomy.visibility', ['taxonomy' => 'brands', 'record' => $brand->id]))
            ->assertRedirect()
            ->assertSessionHas('status', 'Hidden from the shop.');

        $this->assertDatabaseHas('products', ['brand_id' => $brand->id]);
        $this->assertDatabaseHas('brands', ['id' => $brand->id, 'is_active' => false]);
    }

    public function test_admin_can_review_and_update_hire_request(): void
    {
        $admin = User::factory()->create(['is_admin' => true]);
        $hire = LaptopHireRequest::factory()->create();

        $this->actingAs($admin)->get(route('admin.hire.show', $hire))->assertOk()->assertSee($hire->email);
        $this->patch(route('admin.hire.update', $hire), ['status' => 'contacted', 'admin_notes' => 'Called customer.'])
            ->assertRedirect()->assertSessionHas('status', 'Hire request updated.');

        $this->assertDatabaseHas('laptop_hire_requests', ['id' => $hire->id, 'status' => 'contacted', 'admin_notes' => 'Called customer.']);
    }

    public function test_admin_can_review_and_resolve_contact_enquiry(): void
    {
        $admin = User::factory()->create(['is_admin' => true]);
        $inquiry = ContactInquiry::query()->create([
            'name' => 'Contact Customer', 'email' => 'contact@example.com', 'subject' => 'Laptop enquiry',
            'message' => 'Please advise.',
        ]);

        $this->actingAs($admin)->get(route('admin.contact.show', $inquiry))->assertOk()->assertSee('contact@example.com');
        $this->patch(route('admin.contact.update', $inquiry), ['status' => 'resolved', 'admin_notes' => 'Replied by phone.'])
            ->assertRedirect()->assertSessionHas('status', 'Contact enquiry updated.');

        $this->assertDatabaseHas('contact_inquiries', ['id' => $inquiry->id, 'status' => 'resolved']);
    }

    public function test_admin_can_publish_an_article_using_validated_plain_text_content(): void
    {
        $admin = User::factory()->create(['is_admin' => true]);

        $this->actingAs($admin)->post(route('admin.blog.store'), [
            'title' => 'Business laptop guide', 'body' => "Choose a laptop for your work.\nCompare support and battery life.",
            'meta_description' => 'A short business laptop guide.', 'is_published' => '1',
        ])->assertRedirect(route('admin.blog.index'))->assertSessionHas('status', 'Article saved.');

        $post = BlogPost::query()->where('slug', 'business-laptop-guide')->firstOrFail();
        $this->assertTrue($post->is_published);
        $this->assertNotNull($post->published_at);
        $this->get(route('blogs', $post->slug))->assertOk()->assertSee('Compare support and battery life.');
    }
}
