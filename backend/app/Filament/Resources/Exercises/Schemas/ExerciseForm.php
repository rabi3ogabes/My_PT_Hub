<?php

namespace App\Filament\Resources\Exercises\Schemas;

use App\Domain\Workouts\Models\Equipment;
use App\Domain\Workouts\Models\MuscleGroup;
use App\Filament\Support\Locales;
use Filament\Forms\Components\CheckboxList;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TagsInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Illuminate\Validation\Rules\Unique;

class ExerciseForm
{
    public const PATTERNS = ['squat', 'hinge', 'push_h', 'push_v', 'pull_h', 'pull_v', 'lunge', 'carry', 'rotation', 'core', 'cardio', 'mobility'];

    public const METRICS = ['weight', 'reps', 'time', 'distance', 'rpe', 'calories', 'heart_rate'];

    /** @param  list<string>  $values  @return array<string, string> */
    private static function opts(array $values): array
    {
        return array_combine($values, array_map(fn (string $v): string => str_replace('_', ' ', ucfirst($v)), $values));
    }

    /** @return list<string> */
    private static function muscleKeys(): array
    {
        return MuscleGroup::query()->orderBy('key')->pluck('key')->all();
    }

    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make(__('Exercise'))
                    ->columns(3)
                    ->schema([
                        TextInput::make('slug')->required()->maxLength(255)->alphaDash()
                            ->unique(ignoreRecord: true, modifyRuleUsing: fn (Unique $rule, $get): Unique => $get('tenant_id') ? $rule->where('tenant_id', $get('tenant_id')) : $rule->whereNull('tenant_id')),
                        Select::make('tenant_id')
                            ->label(__('Owner'))
                            ->relationship('tenant', 'name')
                            ->searchable()
                            ->placeholder(__('Global library'))
                            ->helperText(__('Empty = curated global library visible to every tenant.')),
                        Toggle::make('is_published')->default(true)->inline(false),
                        TextInput::make('primary_muscle')->maxLength(32)->datalist(fn (): array => self::muscleKeys()),
                        TagsInput::make('secondary_muscles')->suggestions(fn (): array => self::muscleKeys()),
                        TagsInput::make('equipment')->suggestions(fn (): array => Equipment::query()->orderBy('key')->pluck('key')->all()),
                        Select::make('pattern')->options(self::opts(self::PATTERNS)),
                        Select::make('mechanics')->options(self::opts(['compound', 'isolation'])),
                        Select::make('force')->options(self::opts(['push', 'pull', 'static'])),
                        Select::make('level')->options(self::opts(['beginner', 'intermediate', 'advanced']))->default('beginner')->required(),
                        Toggle::make('is_unilateral')->inline(false),
                        Toggle::make('is_bodyweight')->inline(false),
                        CheckboxList::make('metrics')->options(self::opts(self::METRICS))->columns(4)->columnSpanFull(),
                    ]),
                Section::make(__('Content per language'))
                    ->schema([
                        Locales::tabs('exercise_locales', fn (string $locale): array => [
                            TextInput::make("name.{$locale}")->label(__('Name'))->required($locale === 'en')->maxLength(255),
                            Repeater::make("instructions.{$locale}")->label(__('Instructions (steps)'))
                                ->simple(Textarea::make('step')->rows(2)->required())
                                ->reorderable()->defaultItems(0),
                            Repeater::make("tips.{$locale}")->label(__('Coaching tips'))
                                ->simple(TextInput::make('tip')->required())
                                ->defaultItems(0),
                            Repeater::make("mistakes.{$locale}")->label(__('Common mistakes'))
                                ->simple(TextInput::make('mistake')->required())
                                ->defaultItems(0),
                            TagsInput::make("aliases.{$locale}")->label(__('Search aliases')),
                        ]),
                    ]),
                Section::make(__('Media'))
                    ->columns(2)
                    ->schema([
                        Select::make('video_provider')->options(self::opts(['hls', 'youtube', 'vimeo', 'upload'])),
                        Select::make('video_status')->options(self::opts(['pending', 'processing', 'ready', 'failed'])),
                        TextInput::make('video_url')->url()->maxLength(255)->columnSpanFull(),
                        TextInput::make('poster_url')->url()->maxLength(255),
                        TextInput::make('preview_url')->url()->maxLength(255),
                    ]),
            ]);
    }
}
