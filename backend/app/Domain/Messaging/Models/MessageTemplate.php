<?php

namespace App\Domain\Messaging\Models;

use App\Domain\Shared\Models\BaseModel;
use App\Domain\Shared\Models\HasTranslations;
use App\Domain\Shared\Tenancy\BelongsToTenant;

class MessageTemplate extends BaseModel
{
    use BelongsToTenant, HasTranslations;

    protected $table = 'message_templates';

    /** @var list<string> */
    protected array $translatable = ['body'];

    protected function casts(): array
    {
        return [
            'body' => 'array',
        ];
    }
}
