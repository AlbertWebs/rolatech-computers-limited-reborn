<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;

#[Fillable(['legacy_id', 'title', 'slug', 'excerpt', 'body', 'meta_description', 'cover_image_path', 'is_published', 'published_at'])]
class BlogPost extends Model
{
    protected function casts(): array
    {
        return ['is_published' => 'boolean', 'published_at' => 'datetime'];
    }
}
