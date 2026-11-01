<?php

use App\Domain\Workouts\Models\Exercise;
use Illuminate\Database\Eloquent\Model;
use Tests\Feature\Workouts\Fixtures;

beforeEach(function () {
    Model::preventLazyLoading(); // catch N+1 / lazy loads (dev behaviour)
    [$this->tenant, $this->owner] = $this->createCoachTenant();
    $this->ex = Fixtures::library();
});

it('searches exercises in all four languages including aliases', function (string $q, string $expectedSlug) {
    $res = $this->actingAsIn($this->owner, $this->tenant)->getJson('/api/v1/exercises?q='.urlencode($q))->assertOk();
    expect(collect($res->json('data'))->pluck('slug'))->toContain($expectedSlug);
})->with([
    'english' => ['bench press', 'barbell-bench-press'],
    'english alias' => ['DB bench', 'dumbbell-bench-press'],
    'arabic' => ['ضغط البنش', 'barbell-bench-press'],
    'arabic alias' => ['ضغط صدر دمبل', 'dumbbell-bench-press'],
    'arabic without hamza variants' => ['تمرين الضغط', 'push-up'],
    'french accents' => ['Développé couché', 'barbell-bench-press'],
    'french without accents' => ['developpe couche haltere', 'dumbbell-bench-press'],
    'spanish' => ['sentadilla', 'back-squat'],
    'spanish alias' => ['press banca', 'barbell-bench-press'],
]);

it('returns the contract shape and localizes names', function () {
    $res = $this->actingAsIn($this->owner, $this->tenant)->getJson('/api/v1/exercises?q=squat&lang=ar')->assertOk();
    $item = collect($res->json('data'))->firstWhere('slug', 'back-squat');
    expect($item['name'])->toBe('سكوات خلفي بالبار')
        ->and(array_keys($item))->toEqual(['id', 'slug', 'name', 'primary_muscle', 'secondary_muscles', 'equipment', 'pattern', 'mechanics', 'force', 'level', 'is_unilateral', 'is_bodyweight', 'metrics', 'video', 'is_custom', 'is_favorite'])
        ->and($res->json('meta'))->toHaveKeys(['per_page', 'next_cursor', 'prev_cursor']);

    $this->getJson('/api/v1/exercises/'.$this->ex['bench']->id)->assertOk()
        ->assertJsonStructure(['data' => ['instructions', 'tips', 'mistakes', 'alternatives', 'video' => ['provider', 'url', 'poster_url', 'preview_url', 'status']]]);
});

it('filters by muscle, equipment, level and favourites with cursor pagination', function () {
    $this->actingAsIn($this->owner, $this->tenant);
    expect(collect($this->getJson('/api/v1/exercises?muscle=triceps')->json('data'))->pluck('slug')->sort()->values()->all())
        ->toBe(['barbell-bench-press', 'dumbbell-bench-press']);
    expect(collect($this->getJson('/api/v1/exercises?equipment=dumbbell')->json('data'))->pluck('slug')->all())->toBe(['dumbbell-bench-press']);
    expect(collect($this->getJson('/api/v1/exercises?level=beginner')->json('data'))->pluck('slug')->sort()->values()->all())->toBe(['dumbbell-bench-press', 'push-up']);

    $this->postJson('/api/v1/exercises/'.$this->ex['squat']->id.'/favorite')->assertOk()->assertJsonPath('data.is_favorite', true);
    $fav = $this->getJson('/api/v1/exercises?favorites=1')->json('data');
    expect($fav)->toHaveCount(1)->and($fav[0]['is_favorite'])->toBeTrue();

    $page1 = $this->getJson('/api/v1/exercises?per_page=2')->assertOk();
    $page2 = $this->getJson('/api/v1/exercises?per_page=2&cursor='.$page1->json('meta.next_cursor'))->assertOk();
    expect(array_merge(collect($page1->json('data'))->pluck('id')->all(), collect($page2->json('data'))->pluck('id')->all()))->toHaveCount(4)->each->toBeString();
});

it('lets a tenant create, update and delete custom exercises but never modify global ones', function () {
    $this->actingAsIn($this->owner, $this->tenant);
    $global = $this->ex['bench'];

    expectApiError($this->patchJson('/api/v1/exercises/'.$global->id, ['name' => 'Hacked']), 403, 'forbidden');
    expectApiError($this->deleteJson('/api/v1/exercises/'.$global->id), 403, 'forbidden');
    expectApiError($this->postJson('/api/v1/exercises/'.$global->id.'/video', ['url' => 'https://youtu.be/dQw4w9WgXcQ']), 403, 'forbidden');
    expect(Exercise::withoutGlobalScopes()->find($global->id)->name['en'])->toBe('Barbell Bench Press');

    $created = $this->postJson('/api/v1/exercises', [
        'name' => ['en' => 'Landmine Press', 'ar' => 'ضغط لاندماين'],
        'aliases' => ['en' => ['angled press']],
        'primary_muscle' => 'chest',
        'equipment' => ['barbell'],
        'instructions' => ['en' => ['Hold the end of the bar.', 'Press up and forward.']],
        'alternative_ids' => [$global->id],
        'video_url' => 'https://www.youtube.com/watch?v=dQw4w9WgXcQ',
    ])->assertCreated()
        ->assertJsonPath('data.is_custom', true)
        ->assertJsonPath('data.video.provider', 'youtube')
        ->assertJsonPath('data.alternatives.0.id', $global->id);
    $id = $created->json('data.id');

    $this->patchJson('/api/v1/exercises/'.$id, ['name' => 'Landmine Shoulder Press', 'level' => 'advanced'])->assertOk()
        ->assertJsonPath('data.name', 'Landmine Shoulder Press')->assertJsonPath('data.level', 'advanced');
    expect(collect($this->getJson('/api/v1/exercises?q=angled')->json('data'))->pluck('id'))->toContain($id);

    $this->deleteJson('/api/v1/exercises/'.$id)->assertOk();
    $this->getJson('/api/v1/exercises/'.$id)->assertNotFound();
});

it('hides one tenant’s custom exercises from another tenant', function () {
    $this->actingAsIn($this->owner, $this->tenant);
    $id = $this->postJson('/api/v1/exercises', ['name' => 'Secret Move'])->assertCreated()->json('data.id');

    [$tenantB, $ownerB] = $this->createCoachTenant();
    $this->actingAsIn($ownerB, $tenantB);
    expectApiError($this->getJson('/api/v1/exercises/'.$id), 404, 'not_found');
    expectApiError($this->patchJson('/api/v1/exercises/'.$id, ['name' => 'x']), 404, 'not_found');
    expect(collect($this->getJson('/api/v1/exercises?q=secret')->json('data')))->toBeEmpty();
});

it('forbids clients from creating exercises but lets them browse the library', function () {
    $client = $this->addClient($this->tenant, $this->owner);
    $this->actingAsIn($client, $this->tenant);
    $this->getJson('/api/v1/exercises?q=bench')->assertOk();
    expectApiError($this->postJson('/api/v1/exercises', ['name' => 'Nope']), 403, 'forbidden');
});

afterEach(fn () => Model::preventLazyLoading(false));
