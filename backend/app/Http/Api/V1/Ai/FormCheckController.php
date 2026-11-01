<?php

namespace App\Http\Api\V1\Ai;

use App\Domain\Ai\Services\Features\FormCheckAnalyzer;
use App\Http\Api\ApiController;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/** POST /me/ai/form-check — set video (≤30 s) or up to 6 frames. */
class FormCheckController extends ApiController
{
    public function store(Request $request, FormCheckAnalyzer $analyzer): JsonResponse
    {
        $data = $request->validate([
            'exercise_id' => ['required', 'string', 'max:26'],
            'video' => ['required_without:frames', 'nullable', 'file', 'mimetypes:video/mp4,video/quicktime,video/webm,video/x-m4v', 'max:51200'],
            'frames' => ['required_without:video', 'nullable', 'array', 'max:'.(int) config('ai.form_check.max_frames', 6)],
            'frames.*' => ['file', 'image', 'mimes:jpeg,jpg,png,webp', 'max:8192'],
            'note' => ['nullable', 'string', 'max:300'],
        ]);

        return $this->created($analyzer->analyse(
            $this->tenant(),
            $this->user(),
            $data['exercise_id'],
            $request->file('video'),
            array_values($request->file('frames', [])),
            $data['note'] ?? null,
        ));
    }
}
