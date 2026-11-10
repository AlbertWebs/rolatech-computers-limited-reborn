# Legacy audit

## Scope and evidence

Reviewed the downloaded `royaltech-old` Laravel 12 application, its routes, controllers, models, Blade views, migrations, public uploads, and the root SQL dump schema. The dump contains sensitive operational and personal data; only table definitions were inspected. Live pages were checked through search indexing and the public storefront pages. A live authenticated checkout or administration session was not available.

## Architecture observed

- Laravel 12 / PHP 8.2 application with Blade, session cart (`darryldecode/cart`), Sanctum, and SEO Tools.
- MySQL production schema includes catalog, customer/order, CMS, hire request, messaging, payment, M-Pesa, Pesapal, and administration-related tables.
- Public routes cover the corporate site, service pages, blog, e-commerce catalog/search/product, cart, checkout, customer order history, and payment callbacks.
- Admin routes in `routes/web.php` expose product/category/brand/content/user management, reports and payment operations behind the legacy `is_admin` middleware.
- Public uploads contain 440 files (about 27.8 MB), including products, categories, brands, services, logos, blog art, client marks, and a hire brochure.

## Verified business context

Royaltech Computers Limited serves individuals, SMEs, and corporate clients in Kenya. The verified offer includes laptops, desktops, accessories, networking, IT services, and computer equipment hire. The public site lists Nairobi contact and storefront details. Live shop indexing confirms current product pages and KES pricing. Product availability and payment credentials require confirmation from the business before production cutover.

## Risks and gaps

- Legacy catalog prices and stock are represented as strings; normalize and validate during import.
- Payment code and callback routes exist, but their current external configuration and production behavior were not exercised.
- Legacy admin route coverage is broad; some mutation endpoints use GET and need safer methods in the replacement.
- Database dump contains sensitive tables. No customer, order, payment, user, or credential records are copied into the new project.
- The new project is an unmodified Laravel 13 skeleton at audit start. This is an implementation in progress, not a production replacement yet.

## Live verification limitation

Public search results exposed the home page, company page, hire pages, and product catalog. Browser checkout, account flows, mobile interaction, and admin pages could not be verified because no authenticated account or device session was supplied.
