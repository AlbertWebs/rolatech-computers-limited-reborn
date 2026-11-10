# Implementation progress

Updated 10 November 2026. This is a substantial local rebuild; it has not been deployed and is not yet production ready.

## Implemented locally

- Laravel 13 / PHP 8.4 application setup, responsive Tailwind storefront, legacy media copy (440 originals) plus 100 WebP derivatives, and a local SQLite content/catalog copy (13 categories, 12 brands, 68 products, 7 blog articles).
- Server-rendered homepage, catalog search/sort, brand/category listings, product details and structured data, cart quantity management, guest order request checkout and order confirmation. Orders are saved unpaid; no payment is taken.
- Preserved product quick-view URL, with a canonical link to the main product page and `noindex` to prevent duplicate indexing.
- Dynamic public sitemap, crawl exclusions for private workflows, and noindex/canonical handling for catalog query variants.
- Session-based wish list, including the legacy AJAX add URL, add/remove actions, and saved product view.
- Admin sign-in with throttling, scoped owner/manager/catalog/operations/content roles, role console commands, role-aware navigation/dashboard privacy, product/category/brand management, order review/status updates, hire/contact inboxes with internal notes, blog publishing, and an audit log of admin changes.
- Dedicated company and contact pages with verified business details; contact messages are recorded for staff follow-up.
- Customer registration/sign-in, editable name/phone, password change with current-password validation, account-owned order history, and individual order detail. Orders placed while signed in are linked to that account; guest checkout remains available.
- Blog index/detail and admin article CRUD with safe plain-text display, draft/publish controls, metadata, cover image upload, and optional sanitizing import of public legacy blog rows.
- Legacy laptop, MacBook, tablet and projector hire landing URLs, request validation, arithmetic spam check, honeypot and rate limiting. Requests are recorded for staff follow-up; no email is sent.
- Automated coverage for storefront, cart, checkout, catalog/blog import, admin access/management, order status, hire/contact requests, customer account ownership and SEO routes.

## Still required before production

- Implement or deliberately retire password recovery/email verification, product reviews, static page CMS, support conversations, inventory automation, payment/refund flows, and other legacy admin capabilities. Wish lists are session-based and are not yet synchronized across devices. See [feature-inventory.md](feature-inventory.md).
- Decide and implement payment and mail providers, then validate them in sandbox with approved credentials. No provider credentials or transactional data were migrated.
- Complete non-catalog asset classification, broader missing/broken media audit and accessibility alt-text review. 15 duplicate-content groups in the original upload tree remain for business/media review; originals remain intact.
- Review privacy/terms and other content with the business, accessibility and SEO crawl, security review, load/backup/restore checks, production database migration and host-specific deployment validation.
- Configure production secrets, HTTPS, monitoring, backups, queues/scheduler as needed, and staging. No DNS, live payment configuration, or deployment action was performed.

## Verification

- `php artisan test`: 38 tests passed, 182 assertions (local SQLite test environment), with no PHPUnit risky-test warnings.
- `vendor/bin/pint --format agent`: passed after formatting.
- `npm run build`: passed during frontend setup; rerun after future asset or CSS changes.
- `php artisan assets:generate-responsive`: generated 100 WebP variants for mapped catalog and hero images.
- `php artisan migrate --force` and repeatable catalog import were run against the local development database only.
