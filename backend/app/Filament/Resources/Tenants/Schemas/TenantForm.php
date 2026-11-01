<?php

namespace App\Filament\Resources\Tenants\Schemas;

use App\Domain\Billing\Models\SaasPlan;
use App\Filament\Support\Locales;
use Filament\Forms\Components\ColorPicker;
use Filament\Forms\Components\KeyValue;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Illuminate\Validation\Rules\Unique;

class TenantForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make(__('Business'))
                    ->columns(2)
                    ->schema([
                        TextInput::make('name')->required()->maxLength(255),
                        TextInput::make('slug')->required()->maxLength(255)->alphaDash()
                            ->unique(ignoreRecord: true, modifyRuleUsing: fn (Unique $rule): Unique => $rule->withoutTrashed()),
                        Select::make('type')->options(['business' => __('Business'), 'personal' => __('Personal')])->required()->default('business'),
                        Select::make('plan')
                            ->options(fn (): array => SaasPlan::query()->orderBy('sort')->pluck('code', 'code')->all())
                            ->required()
                            ->default('free')
                            ->disabledOn('edit')
                            ->helperText(__('Use the "Change plan" action on existing tenants so the change is audited.')),
                    ]),
                Section::make(__('Localisation & branding'))
                    ->columns(3)
                    ->schema([
                        Select::make('locale')->options(Locales::options())->required()->default('en'),
                        Select::make('timezone')->options(fn (): array => array_combine(timezone_identifiers_list(), timezone_identifiers_list()))->searchable()->required()->default('UTC'),
                        TextInput::make('currency')->required()->length(3)->default('USD')->dehydrateStateUsing(fn (?string $state): ?string => $state ? strtoupper($state) : null),
                        TextInput::make('country')->length(2)->dehydrateStateUsing(fn (?string $state): ?string => $state ? strtoupper($state) : null),
                        ColorPicker::make('brand_color')->default('#D4AF6A')->regex('/^#[0-9A-Fa-f]{6}([0-9A-Fa-f]{2})?$/'),
                    ]),
                Section::make(__('Legal (invoices)'))
                    ->collapsed()
                    ->schema([
                        KeyValue::make('legal')->keyLabel(__('Field'))->valueLabel(__('Value')),
                    ]),
            ]);
    }
}
