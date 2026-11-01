<?php

namespace App\Http\Api\V1\Resources\Checkins;

use App\Http\Api\V1\Support\ViewerContext;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin \App\Domain\Checkins\Models\CheckinForm */
class CheckinFormResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $staff = ViewerContext::isStaff($request);

        return [
            'id' => $this->id,
            'name' => $this->name,
            'intro' => $this->intro,
            'questions' => self::questions($this->questions ?? [], $staff),
            'include_metrics' => (bool) $this->include_metrics,
            'include_photos' => (bool) $this->include_photos,
            'updated_at' => $this->updated_at?->toIso8601String(),
        ];
    }

    /**
     * Localises question labels; staff also receive `label_translations`.
     *
     * @param  list<array<string, mixed>>  $questions
     * @return list<array<string, mixed>>
     */
    public static function questions(array $questions, bool $withTranslations = false): array
    {
        $locale = app()->getLocale();

        return array_map(function (array $q) use ($locale, $withTranslations) {
            $label = $q['label'] ?? '';
            $out = $q;
            $out['label'] = is_array($label) ? ($label[$locale] ?? $label['en'] ?? (array_values($label)[0] ?? '')) : (string) $label;
            if ($withTranslations) {
                $out['label_translations'] = (object) (is_array($label) ? $label : [$locale => $label]);
            }

            return $out;
        }, array_values($questions));
    }
}
