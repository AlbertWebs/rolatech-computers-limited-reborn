<?php

namespace App\Console\Commands;

use App\Models\BlogPost;
use App\Models\Brand;
use App\Models\Category;
use App\Models\Product;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use RuntimeException;

class ImportLegacyCatalog extends Command
{
    protected $signature = 'catalog:import-legacy {sql-file? : Path to the legacy SQL dump} {--dry-run : Report row counts without changing the database} {--include-blog : Also import public blog articles as escaped plain text}';

    protected $description = 'Import categories, brands, and products from the legacy Royaltech SQL dump';

    public function handle(): int
    {
        $file = $this->argument('sql-file') ?: dirname(base_path()).DIRECTORY_SEPARATOR.'royalte2_royal (2).sql';

        if (! is_file($file) || ! is_readable($file)) {
            $this->error('The SQL dump could not be read. Provide a readable dump path.');

            return self::FAILURE;
        }

        $statements = $this->catalogStatements(file_get_contents($file));
        $categoryRows = $statements['categories'] ?? [];
        $brandRows = $statements['brands'] ?? [];
        $productRows = $statements['products'] ?? [];
        $blogRows = $statements['blogs'] ?? [];
        $usableProductRows = collect($productRows)->filter(fn (array $row): bool => $this->priceFor($row) !== null && filled($row['name'] ?? null));
        $productsWithImageReference = $usableProductRows->filter(fn (array $row): bool => collect(['image_one', 'image_two', 'image_three', 'image_four'])->contains(fn (string $column): bool => filled($row[$column] ?? null)));
        $productsWithMatchedImage = $productsWithImageReference->filter(fn (array $row): bool => $this->firstProductImage($row) !== null);

        $this->table(['Catalog table', 'Rows read'], [
            ['Categories', count($categoryRows)],
            ['Brands', count($brandRows)],
            ['Products', count($productRows)],
            ['Products with a name and valid price', $usableProductRows->count()],
            ['Products with any legacy image reference', $productsWithImageReference->count()],
            ['Products with a matched migrated image', $productsWithMatchedImage->count()],
            ['Blog articles (not imported unless --include-blog is set)', count($blogRows)],
        ]);

        if ($this->option('dry-run')) {
            $this->info('Dry run complete. No database records were changed.');

            return self::SUCCESS;
        }

        DB::transaction(function () use ($categoryRows, $brandRows, $usableProductRows, $blogRows): void {
            $categoryIds = $this->importCategories($categoryRows);
            $brandIds = $this->importBrands($brandRows);
            $this->importProducts($usableProductRows->all(), $categoryIds, $brandIds);
            if ($this->option('include-blog')) {
                $this->importBlogs($blogRows);
            }
        });

        $this->info('Catalog import completed. Customer, order, payment, and credential tables were not read.');

        return self::SUCCESS;
    }

    /** @return array<string, list<array<string, mixed>>> */
    private function catalogStatements(string $sql): array
    {
        $pattern = '/INSERT\s+INTO\s+`(?<table>categories|brands|products|blogs)`(?<columns>\s*\([^)]*\))?\s+VALUES\s*/i';
        preg_match_all($pattern, $sql, $matches, PREG_SET_ORDER | PREG_OFFSET_CAPTURE);
        $tables = [];

        foreach ($matches as $match) {
            $table = strtolower($match['table'][0]);
            $columnsText = $match['columns'][0] ?? '';
            preg_match_all('/`([^`]+)`/', $columnsText, $columnMatches);
            $columns = $columnMatches[1] ?? [];

            if ($columns === []) {
                throw new RuntimeException("The {$table} INSERT does not declare its columns; import stopped safely.");
            }

            $bodyOffset = $match[0][1] + strlen($match[0][0]);
            $statementEnd = $this->statementEnd($sql, $bodyOffset);
            $tables[$table] = array_merge($tables[$table] ?? [], $this->parseTuples(substr($sql, $bodyOffset, $statementEnd - $bodyOffset), $columns));
        }

        return $tables;
    }

    private function statementEnd(string $sql, int $offset): int
    {
        $inString = false;
        $length = strlen($sql);

        for ($index = $offset; $index < $length; $index++) {
            $character = $sql[$index];

            if ($inString && $character === '\\') {
                $index++;

                continue;
            }

            if ($character === "'") {
                if ($inString && ($sql[$index + 1] ?? null) === "'") {
                    $index++;

                    continue;
                }

                $inString = ! $inString;
            } elseif ($character === ';' && ! $inString) {
                return $index;
            }
        }

        throw new RuntimeException('An INSERT statement is incomplete; import stopped safely.');
    }

    /** @param list<string> $columns
     * @return list<array<string, mixed>>
     */
    private function parseTuples(string $statement, array $columns): array
    {
        $rows = [];
        $length = strlen($statement);
        $cursor = 0;

        while ($cursor < $length) {
            while ($cursor < $length && (ctype_space($statement[$cursor]) || $statement[$cursor] === ',')) {
                $cursor++;
            }

            if ($cursor >= $length) {
                break;
            }

            if ($statement[$cursor] !== '(') {
                throw new RuntimeException('The catalog SQL contains an unsupported value expression; import stopped safely.');
            }

            $cursor++;
            $values = [];

            while (true) {
                while ($cursor < $length && ctype_space($statement[$cursor])) {
                    $cursor++;
                }

                $values[] = $this->parseValue($statement, $cursor);

                while ($cursor < $length && ctype_space($statement[$cursor])) {
                    $cursor++;
                }

                if (($statement[$cursor] ?? null) === ')') {
                    $cursor++;
                    break;
                }

                if (($statement[$cursor] ?? null) !== ',') {
                    throw new RuntimeException('The catalog SQL tuple is malformed; import stopped safely.');
                }

                $cursor++;
            }

            if (count($values) !== count($columns)) {
                throw new RuntimeException('A catalog row has a different number of values than columns; import stopped safely.');
            }

            $rows[] = array_combine($columns, $values);
        }

        return $rows;
    }

    private function parseValue(string $statement, int &$cursor): mixed
    {
        $length = strlen($statement);
        $character = $statement[$cursor] ?? '';

        if ($character === "'") {
            $cursor++;
            $value = '';

            while ($cursor < $length) {
                $character = $statement[$cursor++];

                if ($character === '\\' && $cursor < $length) {
                    $escapedCharacter = $statement[$cursor++];
                    $value .= match ($escapedCharacter) {
                        '0' => "\0",
                        'b' => "\x08",
                        'n' => "\n",
                        'r' => "\r",
                        't' => "\t",
                        'Z' => "\x1a",
                        default => $escapedCharacter,
                    };

                    continue;
                }

                if ($character === "'") {
                    if (($statement[$cursor] ?? null) === "'") {
                        $value .= "'";
                        $cursor++;

                        continue;
                    }

                    return $value;
                }

                $value .= $character;
            }

            throw new RuntimeException('The catalog SQL contains an unterminated string; import stopped safely.');
        }

        $start = $cursor;
        while ($cursor < $length && ! in_array($statement[$cursor], [',', ')'], true)) {
            $cursor++;
        }

        $value = trim(substr($statement, $start, $cursor - $start));

        if (strcasecmp($value, 'NULL') === 0) {
            return null;
        }

        if ($value === '' || ! preg_match('/^-?\d+(?:\.\d+)?$/', $value)) {
            throw new RuntimeException('The catalog SQL contains an unsupported unquoted value; import stopped safely.');
        }

        return $value;
    }

    /** @param list<array<string, mixed>> $rows
     * @return array<string, int>
     */
    private function importCategories(array $rows): array
    {
        $idsByName = [];

        foreach ($rows as $row) {
            $legacyId = (int) ($row['id'] ?? 0);
            $name = trim((string) ($row['title'] ?? ''));

            if ($legacyId === 0 || $name === '') {
                continue;
            }

            $slug = $this->uniqueSlug(Category::class, (string) ($row['slung'] ?? ''), $name, $legacyId);
            $category = Category::query()->updateOrCreate(['legacy_id' => $legacyId], [
                'name' => $name,
                'slug' => $slug,
                'description' => $row['content'] ?? null,
                'image_path' => $this->assetPath($row['image'] ?? null, 'categories'),
                'is_active' => true,
            ]);
            $idsByName[$this->nameKey($name)] = $category->id;
            $idsByName[$this->nameKey($slug)] = $category->id;
        }

        return $idsByName;
    }

    /** @param list<array<string, mixed>> $rows
     * @return array<string, int>
     */
    private function importBrands(array $rows): array
    {
        $idsByName = [];

        foreach ($rows as $row) {
            $legacyId = (int) ($row['id'] ?? 0);
            $name = trim((string) ($row['title'] ?? ''));

            if ($legacyId === 0 || $name === '') {
                continue;
            }

            $slug = $this->uniqueSlug(Brand::class, '', $name, $legacyId);
            $brand = Brand::query()->updateOrCreate(['legacy_id' => $legacyId], [
                'name' => $name,
                'slug' => $slug,
                'logo_path' => $this->assetPath($row['image'] ?? null, 'brands'),
                'is_active' => true,
            ]);
            $idsByName[$this->nameKey($name)] = $brand->id;
        }

        return $idsByName;
    }

    /** @param list<array<string, mixed>> $rows
     * @param  array<string, int>  $categoryIds
     * @param  array<string, int>  $brandIds
     */
    private function importProducts(array $rows, array $categoryIds, array $brandIds): void
    {
        foreach ($rows as $row) {
            $legacyId = (int) ($row['id'] ?? 0);
            $name = trim((string) ($row['name'] ?? ''));
            $price = $this->priceFor($row);

            if ($legacyId === 0 || $name === '' || $price === null) {
                continue;
            }

            $slug = $this->uniqueSlug(Product::class, (string) ($row['slung'] ?? ''), $name, $legacyId);
            $sku = trim((string) ($row['sku'] ?? '')) ?: null;
            if ($sku && Product::query()->where('sku', $sku)->where('legacy_id', '!=', $legacyId)->exists()) {
                $sku = null;
            }

            $categoryName = $this->nameKey((string) ($row['category'] ?? ''));
            $brandName = $this->nameKey((string) ($row['brand'] ?? ''));
            Product::query()->updateOrCreate(['legacy_id' => $legacyId], [
                'category_id' => $categoryIds[$categoryName] ?? null,
                'brand_id' => $brandIds[$brandName] ?? null,
                'name' => $name,
                'slug' => $slug,
                'sku' => $sku,
                'short_description' => null,
                'description' => $row['content'] ?? null,
                'legacy_meta' => $row['meta'] ?? null,
                'image_path' => $this->firstProductImage($row),
                'price' => $price,
                'compare_at_price' => null,
                'stock_quantity' => null,
                'stock_label' => trim((string) ($row['stock'] ?? '')) ?: null,
                'is_featured' => (bool) ($row['featured'] ?? false),
                'is_active' => true,
            ]);
        }
    }

    /** @param list<array<string, mixed>> $rows */
    private function importBlogs(array $rows): void
    {
        foreach ($rows as $index => $row) {
            $legacyId = (int) ($row['id'] ?? $index + 1);
            $title = trim((string) ($row['title'] ?? ''));
            if ($legacyId < 1 || $title === '') {
                continue;
            }

            $html = (string) ($row['content'] ?? '');
            $text = preg_replace('/<\s*br\s*\/?>|<\s*\/\s*(p|div|h[1-6]|li)\s*>/i', "\n", $html) ?? $html;
            $body = html_entity_decode(strip_tags($text), ENT_QUOTES | ENT_HTML5, 'UTF-8');
            $body = trim(preg_replace('/\n{3,}/', "\n\n", preg_replace('/[\t ]+/', ' ', $body) ?? $body) ?? $body);
            if ($body === '') {
                continue;
            }
            $metaText = trim(strip_tags((string) ($row['meta'] ?? '')));
            $publishedAt = filled($row['created_at'] ?? null) ? $row['created_at'] : now();

            BlogPost::query()->updateOrCreate(['legacy_id' => $legacyId], [
                'title' => $title,
                'slug' => $this->uniqueSlug(BlogPost::class, (string) ($row['slung'] ?? ''), $title, $legacyId),
                'excerpt' => Str::limit($body, 320),
                'body' => $body,
                'meta_description' => $metaText !== '' ? Str::limit($metaText, 160, '') : Str::limit($body, 155),
                'cover_image_path' => null,
                'is_published' => true,
                'published_at' => $publishedAt,
            ]);
        }
    }

    private function uniqueSlug(string $model, string $legacySlug, string $name, int $legacyId): string
    {
        $baseSlug = Str::slug($legacySlug ?: $name) ?: 'item';
        $slug = $baseSlug;

        if ($model::query()->where('slug', $slug)->where(function ($query) use ($legacyId): void {
            $query->whereNull('legacy_id')->orWhere('legacy_id', '!=', $legacyId);
        })->exists()) {
            $slug = $baseSlug.'-'.$legacyId;
        }

        return $slug;
    }

    private function firstProductImage(array $row): ?string
    {
        foreach (['image_one', 'image_two', 'image_three', 'image_four'] as $column) {
            $path = $this->assetPath($row[$column] ?? null, 'products');

            if ($path !== null) {
                return $path;
            }
        }

        return null;
    }

    private function assetPath(mixed $value, ?string $preferredDirectory = null): ?string
    {
        $path = trim((string) $value);

        if ($path === '') {
            return null;
        }

        if (filter_var($path, FILTER_VALIDATE_URL)) {
            $host = parse_url($path, PHP_URL_HOST);
            if (! in_array(strtolower((string) $host), ['royaltech.co.ke', 'www.royaltech.co.ke'], true)) {
                return null;
            }

            $path = (string) parse_url($path, PHP_URL_PATH);
        }

        $path = str_replace('\\', '/', $path);
        $path = ltrim(preg_replace('#^(?:public/)?#', '', $path) ?? $path, '/');
        if (str_starts_with($path, 'uploads/')) {
            $path = substr($path, strlen('uploads/'));
        }

        if (str_starts_with($path, 'assets/legacy/')) {
            $assetPath = $path;
        } else {
            $assetPath = 'assets/legacy/'.$path;
        }

        $fullPath = public_path($assetPath);

        if (! is_file($fullPath) && $preferredDirectory !== null && ! str_contains($path, '/')) {
            $preferredPath = 'assets/legacy/'.$preferredDirectory.'/'.$path;
            if (is_file(public_path($preferredPath))) {
                return $preferredPath;
            }
        }

        return is_file($fullPath) ? $assetPath : null;
    }

    private function priceFor(array $row): ?float
    {
        foreach (['price', 'price_raw'] as $column) {
            $raw = trim((string) ($row[$column] ?? ''));
            $normalized = preg_replace('/[^0-9.]/', '', str_replace(',', '', $raw));

            if ($normalized !== null && is_numeric($normalized) && (float) $normalized > 0) {
                return round((float) $normalized, 2);
            }
        }

        return null;
    }

    private function nameKey(string $name): string
    {
        return mb_strtolower(trim($name));
    }
}
