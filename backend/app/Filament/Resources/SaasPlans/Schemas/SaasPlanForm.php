<?php

namespace App\Filament\Resources\SaasPlans\Schemas;

use App\Filament\Support\Locales;
use Filament\Forms\Components\KeyValue;
use Filament\Forms\Components\TagsInput;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class SaasPlanForm
{
    /** Feature keys referenced by PlanGate across modules (free-form keys are allowed too). */
    public const KNOWN_FEATURES = [
        'workouts', 'nutrition', 'habits', 'checkins', 'messaging', 'calendar', 'payments', 'ai_copilot',
        'automations', 'community', 'integrations', 'api', 'zapier', 'custom_domain_profile', 'branded_app',
        'team', 'commissions', 'locations', 'sso', 'white_label',
    ];

    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make(__('Plan'))
                    ->columns(3)
                    ->schema([
                        TextInput::make('code')->required()->maxLength(32)->alphaDash()->unique(ignoreRecord: true)
                            ->disabledOn('edit')
                            ->helperText(__('Stable identifier stored on tenants (free, pro, studio, enterprise).')),
                        TextInput::make('sort')->numeric()->minValue(0)->maxValue(255)->default(0),
                        Toggle::make('is_public')->label(__('Shown on pricing page'))->default(true)->inline(false),
                    ]),
                Section::make(__('Name'))
                    ->schema([
                        Locales::tabs('name_tabs', fn (string $locale): array => [
                            TextInput::make("name.{$locale}")->label(__('Name'))->required($locale === 'en')->maxLength(64),
                        ]),
                    ]),
                Section::make(__('Prices'))
                    ->description(__('Amounts in minor units (cents). 2900 = 29.00.'))
                    ->columns(3)
                    ->schema([
                        TextInput::make('currency')->required()->length(3)->default('USD')
                            ->dehydrateStateUsing(fn (?string $state): ?string => $state ? strtoupper($state) : null),
                        TextInput::make('monthly_price_minor')->label(__('Monthly price (minor)'))->numeric()->integer()->minValue(0)->required()->default(0),
                        TextInput::make('annual_price_minor')->label(__('Annual price (minor)'))->numeric()->integer()->minValue(0)->required()->default(0),
                        TextInput::make('trial_days')->numeric()->integer()->minValue(0)->maxValue(255)->default(14),
                        TextInput::make('stripe_monthly_price_id')->label(__('Stripe monthly price id'))->maxLength(255),
                        TextInput::make('stripe_annual_price_id')->label(__('Stripe annual price id'))->maxLength(255),
                    ]),
                Section::make(__('Limits & features'))
                    ->columns(2)
                    ->schema([
                        KeyValue::make('limits')
                            ->keyLabel(__('Limit'))
                            ->valueLabel(__('Value (empty = unlimited)'))
                            ->default(['clients' => '3', 'coaches' => '1', 'ai_requests_month' => '0', 'video_mb' => '200'])
                            ->dehydrateStateUsing(fn (?array $state): array => collect($state ?? [])
                                ->map(fn ($v) => ($v === null || $v === '') ? null : (int) $v)
                                ->all()),
                        TagsInput::make('features')
                            ->suggestions(self::KNOWN_FEATURES)
                            ->default([])
                            ->dehydrateStateUsing(fn (?array $state): array => array_values(array_unique($state ?? []))),
                    ]),
            ]);
    }
}
