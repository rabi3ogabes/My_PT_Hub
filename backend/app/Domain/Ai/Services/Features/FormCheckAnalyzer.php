<?php

namespace App\Domain\Ai\Services\Features;

use App\Domain\Ai\Data\AiTask;
use App\Domain\Ai\Services\AiGateway;
use App\Domain\Ai\Services\AiOutputService;
use App\Domain\Ai\Services\ClientDataService;
use App\Domain\Ai\Services\ExerciseRetriever;
use App\Domain\Ai\Support\FormRules;
use App\Domain\Ai\Support\ImagePrep;
use App\Domain\Ai\Support\Schemas;
use App\Domain\Identity\Models\User;
use App\Domain\Shared\Support\ApiException;
use App\Domain\Tenancy\Models\Tenant;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Process;
use Illuminate\Support\Str;

/**
 * H. AI form check (client upload): a ≤30 s set video (frames extracted with
 * ffmpeg when available) or up to 6 frames → vision commentary plus a rule
 * checklist for the exercise's movement pattern → draft feedback for the
 * coach. Business-tenant clients see the result only after coach review;
 * self-guided clients get it directly. Always carries a disclaimer.
 */
final class FormCheckAnalyzer
{
    public function __construct(
        private readonly AiGateway $gateway,
        private readonly AiOutputService $outputs,
        private readonly ClientDataService $clients,
        private readonly ExerciseRetriever $exercises,
    ) {}

    /**
     * @param  list<UploadedFile>  $frames
     * @return array<string, mixed>
     */
    public function analyse(Tenant $tenant, User $client, string $exerciseId, ?UploadedFile $video, array $frames, ?string $note = null): array
    {
        $exercise = $this->exercises->find($exerciseId);
        if (! $exercise) {
            throw new ApiException('not_found', __('api.not_found'), 404);
        }
        $images = $video ? $this->framesFromVideo($video) : array_map(fn (UploadedFile $f) => ImagePrep::fromUpload($f), array_slice($frames, 0, (int) config('ai.form_check.max_frames', 6)));
        if ($images === []) {
            throw ApiException::invalid('frames_required', __('ai.errors.frames_required'));
        }

        $rules = FormRules::for($exercise->pattern);
        $language = $this->clients->language($client, null, $tenant);
        $exerciseName = (string) $exercise->translate('name', 'en');

        $result = $this->gateway->run(new AiTask(
            feature: 'form_check',
            tenant: $tenant,
            user: $client,
            kind: 'vision',
            variables: [
                'exercise' => $exerciseName,
                'pattern' => $exercise->pattern ?? '-',
                'rules' => $rules,
                'frame_count' => count($images),
                'language' => $language,
                'note' => $note ?? '-',
            ],
            context: ['rules' => $rules, 'exercise_name' => $exerciseName, 'language' => $language, 'frames' => count($images)],
            schema: Schemas::formCheck(),
            images: $images,
            postProcess: fn (array $data) => $this->normalize($data, $rules, count($images)),
        ));

        $payload = $result->data + [
            'exercise' => ['id' => $exercise->id, 'name' => $exercise->translate('name', $language)],
            'rules' => array_map(fn (string $r) => ['key' => $r, 'label' => __("ai.form_rules.{$r}", [], $language)], $rules),
            'frame_count' => count($images),
            'disclaimer' => __('ai.disclaimer.form_check', [], $language),
            'language' => $language,
        ];

        $output = $this->outputs->draft(
            'form_check',
            $client,
            $client,
            ['exercise_id' => $exercise->id, 'source' => $video ? 'video' : 'frames', 'note' => $note],
            $payload,
            ['frames' => count($images), 'label' => __('ai.grounding_frames', ['count' => count($images)])],
            $result->data['overall'] === 'unsafe' ? [['type' => 'unsafe_form', 'severity' => 'high', 'source' => 'form_check']] : [],
            $result->requestId,
        );

        if ($tenant->isPersonal()) {
            return ['ai_output_id' => $output->id, 'status' => 'ready', 'feedback' => $payload, 'disclaimer' => $payload['disclaimer']];
        }

        return ['ai_output_id' => $output->id, 'status' => 'pending_coach_review', 'feedback' => null, 'message' => __('ai.form_check.sent_to_coach', [], $language), 'disclaimer' => $payload['disclaimer']];
    }

    /**
     * @param  array<string, mixed>  $data
     * @param  list<string>  $rules
     * @return array{0: array<string, mixed>, 1: list<string>}
     */
    public function normalize(array $data, array $rules, int $frames): array
    {
        $errors = [];
        $seen = [];
        foreach ($data['checklist'] as $i => &$item) {
            if (! in_array($item['rule_key'], $rules, true)) {
                $errors[] = "checklist[{$i}].rule_key '{$item['rule_key']}' is not one of: ".implode(', ', $rules);
            }
            if ($item['frame_index'] !== null && $item['frame_index'] >= $frames) {
                $item['frame_index'] = null;
            }
            $seen[] = $item['rule_key'];
        }
        unset($item);
        foreach (array_diff($rules, $seen) as $missing) {
            $data['checklist'][] = ['rule_key' => $missing, 'status' => 'unclear', 'frame_index' => null, 'comment' => ''];
        }

        return [$data, $errors];
    }

    /** @return list<array{media_type: string, data: string}> */
    private function framesFromVideo(UploadedFile $video): array
    {
        $ffmpeg = (string) config('ai.form_check.ffmpeg', 'ffmpeg');
        $ffprobe = (string) config('ai.form_check.ffprobe', 'ffprobe');
        if (! $this->binaryExists($ffmpeg) || ! $this->binaryExists($ffprobe)) {
            throw ApiException::invalid('frames_required', __('ai.errors.video_unsupported'));
        }

        $path = $video->getRealPath();
        $probe = Process::timeout(30)->run([$ffprobe, '-v', 'error', '-show_entries', 'format=duration', '-of', 'default=noprint_wrappers=1:nokey=1', $path]);
        $duration = (float) trim($probe->output());
        if (! $probe->successful() || $duration <= 0) {
            throw ApiException::invalid('invalid_video', __('ai.errors.invalid_video'));
        }
        if ($duration > (int) config('ai.form_check.max_video_seconds', 30)) {
            throw ApiException::invalid('video_too_long', __('ai.errors.video_too_long', ['seconds' => config('ai.form_check.max_video_seconds', 30)]));
        }

        $count = (int) config('ai.form_check.max_frames', 6);
        $dir = storage_path('app/tmp/form-check-'.Str::ulid());
        @mkdir($dir, 0700, true);
        try {
            $fps = max(0.05, $count / $duration);
            $run = Process::timeout(60)->run([$ffmpeg, '-v', 'error', '-i', $path, '-vf', "fps={$fps},scale=768:-2", '-frames:v', (string) $count, "{$dir}/frame_%02d.jpg"]);
            if (! $run->successful()) {
                throw ApiException::invalid('invalid_video', __('ai.errors.invalid_video'));
            }
            $files = glob("{$dir}/frame_*.jpg") ?: [];
            sort($files);

            return array_map(fn (string $f) => ImagePrep::fromBytes((string) file_get_contents($f), 'image/jpeg'), array_slice($files, 0, $count));
        } finally {
            array_map('unlink', glob("{$dir}/*") ?: []);
            @rmdir($dir);
        }
    }

    private function binaryExists(string $binary): bool
    {
        if (str_contains($binary, '/')) {
            return is_executable($binary);
        }

        return Process::run(['sh', '-c', 'command -v '.escapeshellarg($binary)])->successful();
    }
}
