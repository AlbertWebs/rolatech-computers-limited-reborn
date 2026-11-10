<?php

namespace App\Models;

use Database\Factories\ProductFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['legacy_id', 'category_id', 'brand_id', 'name', 'slug', 'sku', 'short_description', 'description', 'legacy_meta', 'image_path', 'price', 'compare_at_price', 'stock_quantity', 'stock_label', 'is_featured', 'is_active'])]
class Product extends Model
{
    /** @use HasFactory<ProductFactory> */
    use HasFactory;

    protected function casts(): array
    {
        return ['price' => 'decimal:2', 'compare_at_price' => 'decimal:2', 'stock_quantity' => 'integer', 'is_featured' => 'boolean', 'is_active' => 'boolean'];
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function brand(): BelongsTo
    {
        return $this->belongsTo(Brand::class);
    }

    public function isOutOfStock(): bool
    {
        return $this->stock_quantity === 0 || str_contains(mb_strtolower((string) $this->stock_label), 'out of stock');
    }

    public function descriptionText(): string
    {
        $content = (string) $this->description;
        $content = preg_replace('/<\s*li\b[^>]*>/i', '• ', $content) ?? $content;
        $content = preg_replace('/<\s*(br\b[^>]*|\/\s*(p|div|li|h[1-6])\s*)>/i', "\n", $content) ?? $content;
        $content = html_entity_decode(strip_tags($content), ENT_QUOTES | ENT_HTML5, 'UTF-8');
        $content = preg_replace('/[\t ]+\n/', "\n", $content) ?? $content;
        $content = preg_replace('/\n{3,}/', "\n\n", $content) ?? $content;

        return trim($content);
    }

    public function schemaAvailability(): ?string
    {
        if ($this->isOutOfStock()) {
            return 'https://schema.org/OutOfStock';
        }

        if ($this->stock_quantity > 0 || str_contains(mb_strtolower((string) $this->stock_label), 'in stock')) {
            return 'https://schema.org/InStock';
        }

        return null;
    }
}
