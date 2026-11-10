<?php

namespace App\Console\Commands;

use App\Models\Brand;
use App\Models\Category;
use App\Models\Product;
use Illuminate\Console\Command;
use Illuminate\Support\Str;
use RuntimeException;

class GenerateResponsiveImages extends Command
{
    protected $signature = 'assets:generate-responsive {--force : Replace existing responsive WebP derivatives}';

    protected $description = 'Create responsive WebP derivatives for catalog media without changing originals';

    public function handle(): int
    {
        if (! function_exists('imagewebp') || ! function_exists('imagecreatefromstring')) {
            $this->error('GD with WebP support is required to generate image derivatives.');

            return self::FAILURE;
        }

        $sources = collect(['assets/legacy/pexels-pixabay-221011.jpg'])
            ->merge(Product::query()->pluck('image_path'))
            ->merge(Category::query()->pluck('image_path'))
            ->merge(Brand::query()->pluck('logo_path'))
            ->filter(fn (?string $path): bool => $path !== null && str_starts_with($path, 'assets/legacy/'))
            ->unique()
            ->values();
        $created = 0;
        $skipped = 0;
        $unreadable = 0;
        $failures = [];

        foreach ($sources as $sourcePath) {
            $sourceFile = public_path($sourcePath);
            $info = @getimagesize($sourceFile);
            $bytes = @file_get_contents($sourceFile);
            $image = $bytes === false ? false : @imagecreatefromstring($bytes);
            if ($info === false || $image === false) {
                $unreadable++;

                continue;
            }

            $relative = Str::after($sourcePath, 'assets/legacy/');
            $stem = substr($relative, 0, -(strlen(pathinfo($relative, PATHINFO_EXTENSION)) + 1));
            $sourceWidth = imagesx($image);
            foreach ([320, 640, 960] as $targetWidth) {
                if ($sourceWidth < $targetWidth) {
                    continue;
                }
                $targetPath = "assets/optimized/{$stem}-{$targetWidth}.webp";
                $targetFile = public_path($targetPath);
                if (is_file($targetFile) && ! $this->option('force')) {
                    $skipped++;

                    continue;
                }
                $targetHeight = max(1, (int) round(imagesy($image) * $targetWidth / $sourceWidth));
                $derivative = imagecreatetruecolor($targetWidth, $targetHeight);
                imagealphablending($derivative, false);
                imagesavealpha($derivative, true);
                imagefill($derivative, 0, 0, imagecolorallocatealpha($derivative, 0, 0, 0, 127));
                imagecopyresampled($derivative, $image, 0, 0, 0, 0, $targetWidth, $targetHeight, $sourceWidth, imagesy($image));

                if (! is_dir(dirname($targetFile)) && ! mkdir(dirname($targetFile), 0775, true) && ! is_dir(dirname($targetFile))) {
                    imagedestroy($derivative);
                    imagedestroy($image);
                    throw new RuntimeException('Unable to create the responsive image output directory.');
                }
                if (! imagewebp($derivative, $targetFile, 82)) {
                    $failures[] = $relative.' ('.$targetWidth.'px)';
                } else {
                    $created++;
                }
                imagedestroy($derivative);
            }
            imagedestroy($image);
        }

        $this->info("Responsive WebP derivatives created: {$created}; existing derivatives skipped: {$skipped}; unreadable source files: {$unreadable}.");
        if ($failures !== []) {
            $this->error('Some WebP derivatives could not be written: '.implode(', ', $failures));

            return self::FAILURE;
        }

        return self::SUCCESS;
    }
}
