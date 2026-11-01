<?php

namespace App\Domain\Ai\Services\Features;

use App\Domain\Ai\Data\AiTask;
use App\Domain\Ai\Services\AiGateway;
use App\Domain\Ai\Services\AiOutputService;
use App\Domain\Ai\Services\ClientDataService;
use App\Domain\Ai\Services\FoodMatcher;
use App\Domain\Ai\Support\ImagePrep;
use App\Domain\Ai\Support\Schemas;
use App\Domain\Identity\Models\User;
use App\Domain\Nutrition\Models\Food;
use App\Domain\Tenancy\Models\Tenant;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;

/**
 * D. Photo food logging (client tool): vision identifies foods and portions,
 * each is mapped to a DB food with grams, confidence and macros. Nothing is
 * logged here — the client confirms/edits and logs via the normal food-log
 * endpoint (source "photo").
 */
final class PhotoFoodLogger
{
    public const LOW_CONFIDENCE = 0.6;

    public function __construct(
        private readonly AiGateway $gateway,
        private readonly AiOutputService $outputs,
        private readonly ClientDataService $clients,
        private readonly FoodMatcher $matcher,
    ) {}

    /** @return array{candidates: list<array<string, mixed>>, ai_output_id: string} */
    public function analyse(Tenant $tenant, User $client, UploadedFile $photo, ?string $slot = null, ?string $note = null): array
    {
        $language = $this->clients->language($client, null, $tenant);

        $result = $this->gateway->run(new AiTask(
            feature: 'photo_food',
            tenant: $tenant,
            user: $client,
            kind: 'vision',
            variables: ['language' => $language, 'slot' => $slot ?? '-', 'note' => $note ?? '-'],
            context: ['language' => $language, 'note' => $note],
            schema: Schemas::photoFood(),
            images: [ImagePrep::fromUpload($photo)],
        ));

        $favorites = DB::table('food_favorites')->where('user_id', $client->id)->pluck('food_id')->all();
        $candidates = [];
        foreach ($result->data['items'] as $item) {
            $match = $this->matcher->match($item['name']);
            $grams = round((float) $item['estimated_grams']);
            $confidence = round((float) $item['confidence'] * ($match ? min(1.0, 0.5 + $match['score'] / 2) : 0.6), 2);
            if ($match) {
                /** @var Food $food */
                $food = $match['food'];
                $macros = [
                    'kcal' => round($food->kcal * $grams / 100, 1),
                    'protein_g' => round($food->protein_g * $grams / 100, 1),
                    'carbs_g' => round($food->carbs_g * $grams / 100, 1),
                    'fat_g' => round($food->fat_g * $grams / 100, 1),
                ];
            } else {
                $food = null;
                $macros = [
                    'kcal' => round((float) ($item['kcal'] ?? 0), 1),
                    'protein_g' => round((float) ($item['protein_g'] ?? 0), 1),
                    'carbs_g' => round((float) ($item['carbs_g'] ?? 0), 1),
                    'fat_g' => round((float) ($item['fat_g'] ?? 0), 1),
                ];
            }
            $candidates[] = [
                'food' => $food ? $this->foodListItem($food, $favorites) : null,
                'name' => $food ? (string) ($food->translate('name', $language) ?? $item['name']) : $item['name'],
                'grams' => $grams,
                'confidence' => $confidence,
                'low_confidence' => $confidence < self::LOW_CONFIDENCE,
            ] + $macros;
        }

        $output = $this->outputs->draft(
            'photo_food',
            $client,
            $client,
            ['slot' => $slot, 'note' => $note],
            ['candidates' => $candidates, 'notes' => $result->data['notes'] ?? null, 'language' => $language],
            ['label' => __('ai.grounding_photo'), 'image_count' => 1],
            [],
            $result->requestId,
        );

        return ['candidates' => $candidates, 'ai_output_id' => $output->id];
    }

    /**
     * Food in the API contract's list form.
     *
     * @param  list<string>  $favorites
     * @return array<string, mixed>
     */
    private function foodListItem(Food $food, array $favorites): array
    {
        return [
            'id' => $food->id,
            'name' => $food->translate('name'),
            'brand' => $food->brand,
            'barcode' => $food->barcode,
            'source' => $food->source,
            'region' => $food->region,
            'category' => $food->category,
            'per_100g' => [
                'kcal' => (float) $food->kcal,
                'protein_g' => (float) $food->protein_g,
                'carbs_g' => (float) $food->carbs_g,
                'fat_g' => (float) $food->fat_g,
                'fiber_g' => (float) $food->fiber_g,
                'sugar_g' => (float) $food->sugar_g,
                'sodium_mg' => (float) $food->sodium_mg,
            ],
            'servings' => array_values($food->servings ?? []),
            'diet_tags' => array_values($food->diet_tags ?? []),
            'allergens' => array_values($food->allergens ?? []),
            'is_verified' => (bool) $food->is_verified,
            'is_custom' => $food->tenant_id !== null,
            'is_favorite' => in_array($food->id, $favorites, true),
        ];
    }
}
