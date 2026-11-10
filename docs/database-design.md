# Database design and migration strategy

The legacy SQL dump defines (among others) `products`, `categories`, `brands`, `orders`, `orders_product`, `users`, `banners`, `blogs`, `laptop_hire_requests`, `payments`, `payment`, and M-Pesa transaction tables. Legacy product name, brand, category, SKU, stock, prices, descriptions, and four image paths are stored as strings; prices and stock need explicit parsing and review.

The dedicated importer reads catalog rows and can optionally import public blog articles as escaped plain text. It is repeatable, idempotent, transactional, and dry-run capable. A local SQLite copy currently contains 13 categories, 12 brands, 68 products, and 7 blog articles. Catalog/blog records retain source IDs for reconciliation and map legacy slugs/assets where available. Customer, order, payment, admin, and credential records were not imported and require a separately approved and backed-up migration plan.

The new project uses the starter `users` table extended with admin activation flags and roles, `orders.user_id` for optional customer ownership, and local SQLite for development. New tables cover product catalog, orders/items, equipment hire requests, contact enquiries, blog posts, and admin audit events. Do not point local migration commands at production.
