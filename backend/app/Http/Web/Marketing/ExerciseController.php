<?php

namespace App\Http\Web\Marketing;

use App\Domain\Workouts\Models\Equipment;
use App\Domain\Workouts\Models\Exercise;
use App\Domain\Workouts\Models\MuscleGroup;
use App\View\Seo;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use Illuminate\View\View;

/** Programmatic SEO: the global exercise library (tenant_id null, published). */
class ExerciseController
{
    public static function query(): Builder
    {
        return Exercise::query()->withoutGlobalScopes()
            ->whereNull('tenant_id')->where('is_published', true)->whereNull('deleted_at');
    }

    public function index(Request $request, string $locale): View
    {
        $q = trim((string) $request->query('q', ''));
        $muscle = (string) $request->query('muscle', '');
        $exercises = self::query()
            ->when($muscle !== '', fn ($b) => $b->where('primary_muscle', $muscle))
            ->when($q !== '', function ($b) use ($q) {
                $like = '%'.mb_strtolower($q).'%';
                $b->where(fn ($w) => $w->whereRaw('LOWER(name) LIKE ?', [$like])->orWhereRaw('LOWER(aliases) LIKE ?', [$like])->orWhere('slug', 'like', '%'.str_replace(' ', '-', mb_strtolower($q)).'%'));
            })
            ->orderBy('slug')
            ->paginate(24)->withQueryString();

        $seo = Seo::make(__('marketing.exercises.meta_title'), __('marketing.exercises.meta_description'))
            ->breadcrumbs([[__('marketing.nav.res.exercises'), route('marketing.exercises.index')]]);
        // Search result pages are not indexed; muscle filters and pagination are.
        $seo->noindex($q !== '');
        if ($muscle !== '' || $exercises->currentPage() > 1) {
            $seo->canonical = $request->fullUrl();
        }

        return view('marketing.exercises.index', [
            'seo' => $seo,
            'exercises' => $exercises,
            'muscles' => $this->muscles(),
            'q' => $q,
            'muscle' => $muscle,
        ]);
    }

    public function show(string $locale, string $slug): View
    {
        $exercise = self::query()->where('slug', $slug)
            ->with(['alternatives' => fn ($q) => $q->withoutGlobalScopes()->whereNull('exercises.tenant_id')->where('is_published', true)])
            ->firstOrFail();

        $muscles = $this->muscles();
        $equipment = Cache::remember('marketing:equipment', 3600, fn () => Equipment::query()->get()->keyBy('key'));
        $steps = array_values(array_filter((array) ($exercise->translateRaw('instructions') ?? [])));
        $mistakes = array_values(array_filter((array) ($exercise->translateRaw('mistakes') ?? [])));
        $tips = array_values(array_filter((array) ($exercise->translateRaw('tips') ?? [])));
        $name = (string) $exercise->translate('name');
        $related = self::query()->where('primary_muscle', $exercise->primary_muscle)->where('id', '!=', $exercise->id)->orderBy('slug')->limit(6)->get();

        $seo = Seo::make(__('marketing.exercises.show_title', ['name' => $name]), __('marketing.exercises.show_description', ['name' => $name, 'muscle' => $this->muscleName($muscles, $exercise->primary_muscle)]))
            ->breadcrumbs([[__('marketing.nav.res.exercises'), route('marketing.exercises.index')], [$name, url()->current()]])
            ->image($exercise->poster_url)
            ->type('article');

        if ($steps !== []) {
            $seo->schema([
                '@type' => 'HowTo',
                'name' => __('marketing.exercises.howto_name', ['name' => $name]),
                'image' => $exercise->poster_url,
                'tool' => array_map(fn ($k) => ['@type' => 'HowToTool', 'name' => $equipment->get($k)?->translate('name') ?? \Illuminate\Support\Str::headline((string) $k)], (array) ($exercise->equipment ?? [])),
                'step' => array_map(fn ($s, $i) => ['@type' => 'HowToStep', 'position' => $i + 1, 'text' => is_array($s) ? implode(' ', $s) : (string) $s], $steps, array_keys($steps)),
            ]);
        }
        if ($exercise->video_url) {
            $seo->schema([
                '@type' => 'VideoObject',
                'name' => $name,
                'description' => __('marketing.exercises.show_description', ['name' => $name, 'muscle' => $this->muscleName($muscles, $exercise->primary_muscle)]),
                'thumbnailUrl' => $exercise->poster_url ?: route('marketing.og'),
                'uploadDate' => $exercise->created_at?->toIso8601String(),
                'contentUrl' => $exercise->video_url,
            ]);
        }

        return view('marketing.exercises.show', compact('seo', 'exercise', 'muscles', 'equipment', 'steps', 'mistakes', 'tips', 'name', 'related'));
    }

    /** @return Collection<string, MuscleGroup> */
    private function muscles(): Collection
    {
        return Cache::remember('marketing:muscles', 3600, fn () => MuscleGroup::query()->get()->keyBy('key'));
    }

    public static function muscleName(Collection $muscles, ?string $key): string
    {
        if (! $key) {
            return '';
        }

        return $muscles->get($key)?->translate('name') ?? \Illuminate\Support\Str::headline($key);
    }
}
