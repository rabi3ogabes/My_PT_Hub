<?php

namespace App\Http\Api\V1\Coach;

use App\Domain\Tenancy\Models\ClientProfile;
use App\Domain\Workouts\Models\Exercise;
use App\Domain\Workouts\Models\Program;
use App\Domain\Workouts\Models\Workout;
use App\Http\Api\ApiController;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/** Global ⌘K search across clients, programs, workouts and exercises. */
class SearchController extends ApiController
{
    public function index(Request $request): JsonResponse
    {
        $q = trim((string) $request->validate(['q' => ['required', 'string', 'min:2', 'max:80']])['q']);
        $like = '%'.str_replace(['%', '_'], ['\%', '\_'], $q).'%';

        $clients = ClientProfile::query()->with('user')->whereHas('user', fn ($u) => $u->where('name', 'like', $like)->orWhere('email', 'like', $like))->limit(6)->get()
            ->map(fn ($c) => ['type' => 'client', 'id' => $c->user_id, 'title' => $c->user->name, 'subtitle' => $c->user->email]);
        $programs = Program::query()->where('tenant_id', $this->tenant()->id)->where('name', 'like', $like)->limit(5)->get()
            ->map(fn ($p) => ['type' => 'program', 'id' => $p->id, 'title' => $p->translate('name'), 'subtitle' => null]);
        $workouts = Workout::query()->where('tenant_id', $this->tenant()->id)->where('name', 'like', $like)->limit(5)->get()
            ->map(fn ($w) => ['type' => 'workout', 'id' => $w->id, 'title' => $w->name, 'subtitle' => $w->estimated_minutes ? $w->estimated_minutes.' min' : null]);
        $exercises = Exercise::query()->where(fn ($w) => $w->where('name', 'like', $like)->orWhere('aliases', 'like', $like))->limit(6)->get()
            ->map(fn ($e) => ['type' => 'exercise', 'id' => $e->id, 'title' => $e->translate('name'), 'subtitle' => $e->primary_muscle]);

        return $this->ok($clients->concat($programs)->concat($workouts)->concat($exercises)->values());
    }
}
