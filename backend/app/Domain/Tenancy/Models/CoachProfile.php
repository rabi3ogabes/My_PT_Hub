<?php

namespace App\Domain\Tenancy\Models;

use App\Domain\Identity\Models\User;
use App\Domain\Marketing\Models\Review;
use App\Domain\Shared\Models\BaseModel;
use App\Domain\Shared\Models\HasTranslations;
use App\Domain\Shared\Tenancy\BelongsToTenant;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class CoachProfile extends BaseModel
{
    use BelongsToTenant, HasTranslations;

    protected $table = 'coach_profiles';

    /** @var list<string> */
    protected array $translatable = ['bio'];

    protected function casts(): array
    {
        return [
            'bio' => 'array',
            'specialties' => 'array',
            'languages' => 'array',
            'certifications' => 'array',
            'offers_online' => 'boolean',
            'offers_in_person' => 'boolean',
            'is_published' => 'boolean',
            'is_verified' => 'boolean',
            'is_featured' => 'boolean',
            'rating_avg' => 'float',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function reviews(): HasMany
    {
        return $this->hasMany(Review::class);
    }
}
