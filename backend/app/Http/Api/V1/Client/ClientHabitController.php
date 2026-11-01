<?php

namespace App\Http\Api\V1\Client;

use App\Domain\Habits\Models\HabitAssignment;
use App\Domain\Habits\Services\HabitService;
use App\Domain\Shared\Support\ApiException;
use App\Http\Api\ApiController;
use Carbon\CarbonImmutable;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ClientHabitController extends ApiController
{
    public function __construct(private readonly HabitService $habits) {}

    /** GET /me/habits?date= */
    public function index(Request $request): JsonResponse
    {
        $today = $this->habits->today($this->user());
        $date = $request->validate(['date' => ['nullable', 'date_format:Y-m-d']])['date'] ?? $today;
        $items = $this->habits->activeFor($this->user()->id, $date)
            ->map(fn (HabitAssignment $a) => $this->habits->item($a, $date, $today))
            ->values()->all();

        return $this->ok($items);
    }

    /** POST /me/habits/{assignment}/log {date, value?, completed?} */
    public function log(Request $request, HabitAssignment $assignment): JsonResponse
    {
        if ($assignment->client_id !== $this->user()->id) {
            throw new ApiException('not_found', __('api.not_found'), 404);
        }
        $today = $this->habits->today($this->user());
        $data = $request->validate([
            'date' => ['required', 'date_format:Y-m-d'],
            'value' => ['nullable', 'numeric', 'min:0', 'max:1000000'],
            'completed' => ['nullable', 'boolean'],
        ]);
        $date = $data['date'];
        if ($date > CarbonImmutable::parse($today)->addDay()->toDateString() || $date < $assignment->start_date->toDateString()
            || ($assignment->end_date && $date > $assignment->end_date->toDateString())) {
            throw ApiException::invalid('validation_failed', __('api.validation_failed'), ['date' => [__('habits.errors.date_out_of_range')]]);
        }

        $this->habits->log($assignment, $date, isset($data['value']) ? (float) $data['value'] : null, isset($data['completed']) ? (bool) $data['completed'] : null);

        return $this->ok($this->habits->item($assignment->load(['habit', 'logs']), $date, $today));
    }
}
