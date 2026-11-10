# Asset migration

Copied all 440 files from `royaltech-old/public/uploads` into `public/assets/legacy/`, preserving the legacy folder tree and filenames. This is a byte-preserving source archive and keeps the original assets available without modifying the old application.

The legacy source includes product, category, brand, service, blog, client, icon, logo, and people assets; common formats include JPEG, PNG, WebP, SVG, and AVIF. The folder includes `Laptops-Hire-Brochure.pdf` and the Royaltech logo variants.

The importer maps 68 of 68 product image references, 12 of 13 category image records, and 10 of 12 brand image records to existing copied files; zero mapped paths are broken. The one category and two brands without mapped images had no usable matching file. A SHA-256 audit found 15 duplicate-content groups among the original uploads; the originals remain untouched for review.

Generated 100 WebP derivatives at 320, 640 and 960 pixel widths for eligible mapped product/category/brand images and the homepage hero. The storefront serves responsive `srcset` candidates while preserving each original as a fallback. The repeatable command is `php artisan assets:generate-responsive`; it skips existing outputs unless `--force` is given. The image service supports GD/WebP. A full accessibility alt-text review and a broader audit of non-catalog media remain follow-up work.
