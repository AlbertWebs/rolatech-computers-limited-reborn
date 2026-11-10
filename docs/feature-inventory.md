# Feature inventory and migration checklist

Status uses `Verified` for observed legacy functionality, `In progress` for replacement work, and `Pending` where configuration or validation remains.

| Area | Legacy evidence | Replacement status |
|---|---|---|
| Corporate home, company, contact | HomeController and front views | Homepage/company/contact implemented; contact messages enter the admin inbox |
| Service/center-of-excellence pages | Service model and slug route | Pending |
| Laptop, MacBook, tablet, projector hire | Hire views and request table | Hire URLs, request capture and admin inbox implemented; outbound email pending |
| Blog, terms, privacy, copyright | Models, views, routes | Blog publishing plus optional public article import implemented; legal/copyright pages await current business-reviewed copy |
| Product catalog and search | ShopController, Product model, live listing | Implemented for imported catalog; production reconciliation pending |
| Brands, categories, tags | ShopController and corresponding tables | Brand/category pages implemented; tag parity pending |
| Product detail and quick view | ShopController and product views | Product detail and legacy quick-view URL implemented |
| Cart and quantity updates | Session cart routes | Session cart and quantity/stock validation implemented |
| Checkout and payment | Checkout routes; Pesapal/M-Pesa implementation | Guest order request implemented; payment pending configuration and sandbox validation |
| Customer login/profile/order history | Auth and dashboard routes | Registration, sign-in, profile/phone/password changes and account-owned order history implemented; recovery and email verification pending |
| Wishlist and reviews | Legacy models/routes | Session wish list with legacy add URL implemented; reviews pending |
| Admin catalog and CMS | AdminsController; is_admin middleware | Scoped admin roles, catalog and blog publishing implemented; static page CMS and promotions pending |
| Admin orders, payment operations, reports | Legacy routes/models | Order review/status implemented; payments and reports pending |
| SEO metadata and URL continuity | SEO Tools, slug routes, public URLs | In progress |
| Legacy media | 440 public upload files | Originals copied to `public/assets/legacy/`; 100 responsive WebP derivatives; product image references fully matched; remaining media review pending |
| Import of catalog data | SQL schema available | Repeatable selected-table importer; local import has 13 categories, 12 brands, 68 products and 7 public blog articles |

No payment integration or customer/order data is considered migrated or production ready until independently validated. Imported legacy catalog/blog records are local development content and must be reconciled before deployment.
