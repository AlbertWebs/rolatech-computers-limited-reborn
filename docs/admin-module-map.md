# Administration module map

Legacy admin route/controller inventory confirms management areas for catalog, categories, brands, blogs and CMS pages, users/roles, orders, reports, inventory, and M-Pesa/payment operations. Current replacement admin uses dedicated sign-in, server-side role middleware, validated changes, safe HTTP verbs, and an audit trail of administrator action names/changed fields. Administrator roles are assigned from the server console with `admin:create-user --role=...` or `admin:set-role`.

| Module | Legacy coverage | Replacement |
|---|---|---|
| Dashboard / sales | Reports and transactions | Live catalog/order/hire counts; financial reporting pending |
| Catalog / categories / brands | CRUD routes | Product, category and brand management implemented |
| Orders / fulfillment | Order and billing records | Order review and status updates implemented; fulfillment/payment operations pending |
| Customers / roles | User/admin routes | Customer registration/sign-in and private order history implemented; profile controls and granular roles pending |
| Content / promotions | Banners, blogs, pages, settings | Blog article drafting/publishing implemented; banners, static CMS and site settings pending |
| Payments / refunds | Pesapal and M-Pesa records | Pending provider validation |
| Hire requests / support | Hire requests, messages | Hire and contact enquiry inboxes, status and internal notes implemented; ongoing support case management pending |
| Audit / configuration | Activity logs and settings | Admin changes to catalog, orders, enquiries and articles are logged; settings administration pending |

These are migration targets, not claims that a finished admin system is present.
