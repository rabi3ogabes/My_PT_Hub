<?php

namespace App\Domain\Ai\Services;

use App\Domain\Ai\Models\AiOutput;
use App\Domain\Ai\Services\Features\MealPlanGenerator;
use App\Domain\Ai\Services\Features\ProgramGenerator;
use App\Domain\Ai\Services\Features\ProgressionAssistant;
use App\Domain\Ai\Support\AiFeatures;
use App\Domain\Identity\Models\User;
use App\Domain\Shared\Support\ActivityLogger;
use App\Domain\Shared\Support\ApiException;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

/**
 * AI drafts and their review lifecycle: draft -> approved | edited -> applied,
 * or rejected. Applying turns an approved draft into a real tenant artifact
 * (program, meal plan, progression changes). Nothing a coach-facing feature
 * produces reaches a client before this approval.
 */
final class AiOutputService
{
    /**
     * @param  array<string, mixed>  $input
     * @param  array<string, mixed>  $payload
     * @param  array<string, mixed>  $grounding
     * @param  list<array<string, mixed>>  $flags
     */
    public function draft(string $feature, ?User $author, ?User $client, array $input, array $payload, array $grounding = [], array $flags = [], ?string $requestId = null): AiOutput
    {
        return AiOutput::query()->create([
            'ai_request_id' => $requestId,
            'user_id' => $author?->id,
            'client_id' => $client?->id,
            'feature' => $feature,
            'input' => $input,
            'payload' => $payload,
            'grounding' => $grounding,
            'flags' => array_values($flags),
            'status' => 'draft',
        ]);
    }

    /** @param array<string, mixed>|null $edited */
    public function approve(AiOutput $output, User $reviewer, ?array $edited = null, bool $apply = false): AiOutput
    {
        if (! in_array($output->status, ['draft', 'approved', 'edited'], true)) {
            throw ApiException::conflict('ai_output_closed', __('ai.errors.output_closed'));
        }
        if ($apply && ! in_array($output->feature, AiFeatures::APPLICABLE, true)) {
            throw ApiException::invalid('not_applicable', __('ai.errors.not_applicable'));
        }

        return DB::transaction(function () use ($output, $reviewer, $edited, $apply) {
            $changed = $edited !== null && $edited !== $output->payload;
            if ($changed) {
                $this->applyEditValidation($output, $edited);
            }
            $output->fill([
                'status' => $changed || $output->status === 'edited' ? 'edited' : 'approved',
                'edited_payload' => $changed ? $edited : $output->edited_payload,
                'edited_diff' => $changed ? $this->diff($output->payload ?? [], $edited) : $output->edited_diff,
                'reviewed_by' => $reviewer->id,
                'reviewed_at' => now(),
            ])->save();

            if ($apply) {
                $artifact = $this->applier($output->feature)->apply($output, $reviewer);
                $output->fill([
                    'status' => 'applied',
                    'applied_type' => $output->feature,
                    'applied_id' => $artifact instanceof Model ? $artifact->getKey() : null,
                ])->save();
            }

            ActivityLogger::log('ai_output.'.$output->status, $output, ['feature' => $output->feature]);

            return $output->refresh();
        });
    }

    public function reject(AiOutput $output, User $reviewer, ?string $note = null): AiOutput
    {
        if ($output->status === 'applied') {
            throw ApiException::conflict('ai_output_closed', __('ai.errors.output_closed'));
        }
        $output->fill(['status' => 'rejected', 'review_note' => $note, 'reviewed_by' => $reviewer->id, 'reviewed_at' => now()])->save();
        ActivityLogger::log('ai_output.rejected', $output, ['feature' => $output->feature]);

        return $output;
    }

    /** @param array<string, mixed> $edited */
    private function applyEditValidation(AiOutput $output, array $edited): void
    {
        if (in_array($output->feature, AiFeatures::APPLICABLE, true)) {
            $errors = $this->applier($output->feature)->validateEdited($output, $edited);
            if ($errors !== []) {
                throw ApiException::invalid('validation_failed', __('ai.errors.edit_invalid'), ['edited_payload' => array_slice($errors, 0, 10)]);
            }
        }
    }

    private function applier(string $feature): ProgramGenerator|MealPlanGenerator|ProgressionAssistant
    {
        return match ($feature) {
            'program' => app(ProgramGenerator::class),
            'meal_plan' => app(MealPlanGenerator::class),
            'progression' => app(ProgressionAssistant::class),
        };
    }

    /**
     * Path-level diff of two payloads (max 200 entries).
     *
     * @param  array<mixed>  $before
     * @param  array<mixed>  $after
     * @return list<array{path: string, from: mixed, to: mixed}>
     */
    public function diff(array $before, array $after, string $path = ''): array
    {
        $out = [];
        foreach (array_unique([...array_keys($before), ...array_keys($after)]) as $key) {
            $p = $path === '' ? (string) $key : "{$path}.{$key}";
            $a = $before[$key] ?? null;
            $b = $after[$key] ?? null;
            if (is_array($a) && is_array($b)) {
                array_push($out, ...$this->diff($a, $b, $p));
            } elseif ($a !== $b) {
                $out[] = ['path' => $p, 'from' => $a, 'to' => $b];
            }
            if (count($out) >= 200) {
                break;
            }
        }

        return array_slice($out, 0, 200);
    }
}
