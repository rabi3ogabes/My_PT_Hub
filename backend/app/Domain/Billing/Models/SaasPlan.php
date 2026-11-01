<?php

namespace App\Domain\Billing\Models;

use App\Domain\Shared\Models\BaseModel;
use App\Domain\Shared\Models\HasTranslations;

class SaasPlan extends BaseModel
{
    use HasTranslations;

    protected $table = 'saas_plans';

    /** @var list<string> */
    protected array $translatable = ['name'];

    protected function casts(): array
    {
        return [
            'name' => 'array',
            'limits' => 'array',
            'features' => 'array',
            'is_public' => 'boolean',
        ];
    }

    public function limit(string $key): ?int
    {
        $v = $this->limits[$key] ?? null;

        return $v === null ? null : (int) $v;
    }

    public function hasFeature(string $feature): bool
    {
        return in_array($feature, $this->features ?? [], true);
    }
}
