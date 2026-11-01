<?php

namespace App\Filament\Resources\FeatureFlags\Schemas;

use App\Domain\Billing\Models\SaasPlan;
use App\Domain\Tenancy\Models\Tenant;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class FeatureFlagForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make(__('Flag'))
                    ->columns(2)
                    ->schema([
                        TextInput::make('key')->required()->maxLength(255)->regex('/^[a-z0-9_.\-]+$/')->unique(ignoreRecord: true)
                            ->helperText(__('Feature key checked by PlanGate::feature(), e.g. ai_copilot.')),
                        Toggle::make('enabled')->helperText(__('Off = kill switch for everyone.'))->inline(false),
                        TextInput::make('description')->maxLength(255)->columnSpanFull(),
                    ]),
                Section::make(__('Targeting'))
                    ->description(__('Resolution: kill switch → tenant allow-list → plan restriction → rollout % → plan features.'))
                    ->columns(2)
                    ->schema([
                        Select::make('plans')
                            ->multiple()
                            ->options(fn (): array => SaasPlan::query()->orderBy('sort')->pluck('code', 'code')->all())
                            ->helperText(__('Empty = all plans.')),
                        TextInput::make('rollout')
                            ->label(__('Rollout %'))
                            ->numeric()->integer()->minValue(0)->maxValue(100)->default(100)->suffix('%')->required(),
                        Select::make('tenants')
                            ->label(__('Tenant allow-list'))
                            ->multiple()
                            ->searchable()
                            ->getSearchResultsUsing(fn (string $search): array => Tenant::query()
                                ->where(fn ($q) => $q->where('name', 'like', "%{$search}%")->orWhere('slug', 'like', "%{$search}%"))
                                ->limit(25)->pluck('name', 'id')->all())
                            ->getOptionLabelsUsing(fn (array $values): array => Tenant::query()->whereIn('id', $values)->pluck('name', 'id')->all())
                            ->helperText(__('These tenants always get the feature while the flag is enabled.'))
                            ->columnSpanFull(),
                    ]),
            ]);
    }
}
