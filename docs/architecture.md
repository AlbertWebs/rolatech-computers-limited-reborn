# Architecture

The replacement retains the existing Laravel 13 / PHP 8.3+ application stack and uses a modular monolith with Blade-rendered public pages. The new skeleton starts with SQLite for local development; production database choice is not inferred from the legacy dump and must be configured per deployment.

Implemented modules currently include catalog (products, categories, brands, search), session cart/wishlist, guest/customer-linked order requests, customer registration/sign-in/order history, contact enquiries, equipment hire requests, blog publishing, scoped admin roles, and administrator audit logging. Password recovery/email verification, static-page CMS, and payment operations remain planned. Controllers validate HTTP input; authorization is enforced through auth middleware for customer data and role middleware for protected admin areas. Dedicated policy classes and further service-layer extraction remain follow-up work.

External services remain environment-configured. No live payment credentials or endpoints are copied. Queue/cache drivers should be selected to match the actual host. Public pages render on the server for crawlability; assets are stored under `public/assets` with the original migration tree retained.
