<?php

namespace App\Http\Api\V1\Workouts;

use App\Domain\Workouts\Models\Exercise;
use App\Domain\Workouts\Services\ExerciseLibrary;
use App\Http\Api\ApiController;
use App\Http\Api\V1\Requests\Workouts\ExerciseRequest;
use App\Http\Api\V1\Resources\Workouts\ExerciseResource;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ExerciseController extends ApiController
{
    use AuthorizesWorkoutStaff;

    public function __construct(private readonly ExerciseLibrary $library) {}

    /** GET /exercises?q=&muscle=&equipment=&level=&pattern=&favorites=1&custom=0|1&cursor= */
    public function index(Request $request): JsonResponse
    {
        $filters = $request->validate([
            'q' => ['nullable', 'string', 'max:100'],
            'muscle' => ['nullable', 'string', 'max:32'],
            'equipment' => ['nullable', 'string', 'max:200'],
            'level' => ['nullable', 'string', 'max:64'],
            'pattern' => ['nullable', 'string', 'max:24'],
            'favorites' => ['nullable', 'boolean'],
            'custom' => ['nullable', 'boolean'],
            'per_page' => ['nullable', 'integer', 'min:1', 'max:100'],
        ]);
        $filters['favorites'] = $request->boolean('favorites');
        $filters['custom'] = $request->has('custom') ? $request->boolean('custom') : null;

        $paginator = $this->library->query($filters, $this->user())
            ->orderBy('slug')->orderBy('id')
            ->cursorPaginate($this->perPage(30))
            ->withQueryString();

        return $this->paginated($paginator, ExerciseResource::class);
    }

    public function show(Request $request, Exercise $exercise): JsonResponse
    {
        $exercise->load('alternatives');
        $staff = $this->user()->isStaffIn();

        return $this->ok((new ExerciseResource($exercise))->detailed($staff)->toArray($request));
    }

    public function store(ExerciseRequest $request): JsonResponse
    {
        $this->authorizeManage('exercises.manage');
        $exercise = $this->library->create($request->validated());

        return $this->created((new ExerciseResource($exercise->load('alternatives')))->detailed(true)->toArray($request));
    }

    public function update(ExerciseRequest $request, Exercise $exercise): JsonResponse
    {
        $this->authorizeManage('exercises.manage');
        $exercise = $this->library->update($exercise, $request->validated());

        return $this->ok((new ExerciseResource($exercise->load('alternatives')))->detailed(true)->toArray($request));
    }

    public function destroy(Exercise $exercise): JsonResponse
    {
        $this->authorizeManage('exercises.manage');
        $this->library->delete($exercise);

        return $this->noContent();
    }

    public function favorite(Exercise $exercise): JsonResponse
    {
        $state = $this->library->toggleFavorite($this->user(), $exercise);

        return $this->ok(['id' => $exercise->id, 'is_favorite' => $state]);
    }

    /** Multipart `video` (mp4/mov) or JSON `{url}` (YouTube / Vimeo). */
    public function uploadVideo(Request $request, Exercise $exercise): JsonResponse
    {
        $this->authorizeManage('exercises.manage');
        $this->library->assertEditable($exercise);
        $request->validate([
            'video' => ['required_without:url', 'file', 'mimetypes:video/mp4,video/quicktime', 'max:'.(int) config('workouts.max_video_kb', 512000)],
            'url' => ['required_without:video', 'nullable', 'url', 'max:500'],
        ]);

        $exercise = $request->hasFile('video')
            ? $this->library->uploadVideo($exercise, $request->file('video'))
            : $this->library->attachEmbed($exercise, (string) $request->input('url'));

        return $this->ok((new ExerciseResource($exercise->load('alternatives')))->detailed(true)->toArray($request));
    }
}
