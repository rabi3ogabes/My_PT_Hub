<?php

namespace App\Domain\Marketing\Models;

use App\Domain\Shared\Models\BaseModel;

class BlogPost extends BaseModel
{
    protected $table = 'blog_posts';

    protected function casts(): array
    {
        return [
            'published_at' => 'datetime',
            'noindex' => 'boolean',
        ];
    }
}
