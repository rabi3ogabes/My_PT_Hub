<?php

namespace App\Http\Api\V1\Client;

use App\Domain\Workouts\Services\ProgramSchedule;
use App\Domain\Workouts\Services\TodayService;
use App\Http\Api\ApiController;
use Carbon\CarbonImmutable;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class TodayController extends ApiController
{
    public function __construct(private readonly TodayService $today) {}

    /** GET /me/today?date=YYYY-MM-DD (client-local date; defaults to today in the user's timezone). */
    public function show(Request $request): JsonResponse
    {
        $data = $request->validate(['date' => ['nullable', 'date_format:Y-m-d']]);
        $user = $this->user();
        $date = isset($data['date'])
            ? CarbonImmutable::parse($data['date'], $user->timezone ?: 'UTC')->startOfDay()
            : ProgramSchedule::today($user);

        return $this->ok($this->today->build($user, $date));
    }
}
