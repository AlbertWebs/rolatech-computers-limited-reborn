<?php

namespace App\Support;

class ResponsiveImages
{
    /** Return only the derivatives that exist, each with its actual width descriptor. */
    public static function srcset(?string $sourcePath): string
    {
        if (! $sourcePath || ! str_starts_with($sourcePath, 'assets/legacy/')) {
            return '';
        }

        $relative = substr($sourcePath, strlen('assets/legacy/'));
        $extension = pathinfo($relative, PATHINFO_EXTENSION);
        $stem = substr($relative, 0, -(strlen($extension) + 1));
        $candidates = [];
        foreach ([320, 640, 960] as $width) {
            $path = "assets/optimized/{$stem}-{$width}.webp";
            if (is_file(public_path($path))) {
                $candidates[] = asset($path).' '.$width.'w';
            }
        }

        return implode(', ', $candidates);
    }
}
