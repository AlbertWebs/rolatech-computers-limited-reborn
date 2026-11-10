INSERT INTO `categories` (`id`, `title`, `slung`, `content`, `image`, `created_at`, `updated_at`) VALUES
(21, 'Laptops', 'laptops', 'Portable computers', NULL, NULL, NULL);

INSERT INTO `brands` (`id`, `title`, `image`, `created_at`, `updated_at`) VALUES
(4, 'Royaltech Test Brand', NULL, NULL, NULL);

INSERT INTO `products` (`id`, `name`, `slung`, `brand`, `category`, `sku`, `price`, `price_raw`, `meta`, `content`, `image_one`, `stock`, `featured`) VALUES
(88, 'Work Laptop', 'work-laptop', 'Royaltech Test Brand', 'Laptops', 'RT-TEST-88', 'KES 74,999', '74999', NULL, 'Work, study and O\'Reilly; ready.', NULL, 'In Stock', 1);
