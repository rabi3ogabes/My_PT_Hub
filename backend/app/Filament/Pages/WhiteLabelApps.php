<?php

namespace App\Filament\Pages;

use App\Domain\Billing\Models\SaasPlan;
use App\Domain\Shared\Support\ActivityLogger;
use App\Domain\Tenancy\Models\FeatureFlag;
use App\Domain\Tenancy\Models\Tenant;
use App\Filament\Resources\Tenants\TenantResource;
use App\Filament\Support\Locales;
use App\Filament\Support\Nav;
use BackedEnum;
use Filament\Actions\Action;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Schemas\Components\EmbeddedTable;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\ColorColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

/**
 * Branded (white-label) app builds for tenants whose plan includes
 * `branded_app` (Studio / Enterprise) or who are allow-listed on that flag.
 * Config lives in tenants.settings.whitelabel and feeds the build pipeline.
 */
class WhiteLabelApps extends Page implements HasTable
{
    use InteractsWithTable;

    public const FEATURE = 'branded_app';

    public const BUILD_STATUSES = [
        'not_started' => 'Not started',
        'config_pending' => 'Config pending',
        'queued' => 'Queued',
        'building' => 'Building',
        'testflight' => 'TestFlight / internal',
        'in_review' => 'In store review',
        'live' => 'Live',
        'rejected' => 'Rejected',
        'failed' => 'Build failed',
    ];

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedDevicePhoneMobile;

    protected static ?int $navigationSort = 5;

    public static function getNavigationGroup(): ?string
    {
        return Nav::platform();
    }

    public static function getNavigationLabel(): string
    {
        return __('White-label apps');
    }

    public function getTitle(): string
    {
        return __('White-label apps');
    }

    public function content(Schema $schema): Schema
    {
        return $schema->components([EmbeddedTable::make()]);
    }

    /** @return Builder<Tenant> */
    public static function eligibleTenants(): Builder
    {
        $plans = SaasPlan::query()->get()->filter(fn (SaasPlan $p): bool => $p->hasFeature(self::FEATURE))->pluck('code')->all();
        $flag = FeatureFlag::query()->where('key', self::FEATURE)->first();
        $allowList = $flag && $flag->enabled ? ($flag->tenants ?? []) : [];

        return Tenant::query()->where(fn (Builder $q) => $q->whereIn('plan', $plans ?: ['__none__'])->orWhereIn('id', $allowList ?: ['__none__']));
    }

    public static function statusColor(?string $state): string
    {
        return match ($state) {
            'live' => 'success', 'testflight', 'in_review' => 'info', 'queued', 'building', 'config_pending' => 'warning',
            'rejected', 'failed' => 'danger', default => 'gray',
        };
    }

    public function table(Table $table): Table
    {
        return $table
            ->query(fn (): Builder => self::eligibleTenants())
            ->defaultSort('name')
            ->columns([
                TextColumn::make('name')->label(__('Tenant'))->searchable()->sortable()->weight('semibold')
                    ->description(fn (Tenant $record): string => $record->plan)
                    ->url(fn (Tenant $record): string => TenantResource::getUrl('view', ['record' => $record])),
                TextColumn::make('settings.whitelabel.app_name')->label(__('App name'))->placeholder('—'),
                TextColumn::make('settings.whitelabel.bundle_id')->label(__('iOS bundle id'))->fontFamily('mono')->placeholder('—'),
                TextColumn::make('settings.whitelabel.android_package')->label(__('Android package'))->fontFamily('mono')->placeholder('—')->toggleable(),
                ColorColumn::make('settings.whitelabel.colors.primary')->label(__('Primary')),
                TextColumn::make('settings.whitelabel.build_status')->label(__('Build'))->badge()
                    ->formatStateUsing(fn (?string $state): string => self::BUILD_STATUSES[$state] ?? (string) $state)
                    ->color(fn (?string $state): string => self::statusColor($state))
                    ->placeholder(self::BUILD_STATUSES['not_started']),
                TextColumn::make('settings.whitelabel.version')->label(__('Version'))->placeholder('—'),
                TextColumn::make('settings.whitelabel.last_build_at')->label(__('Last build'))->dateTime()->placeholder('—')->toggleable(),
            ])
            ->filters([
                SelectFilter::make('build_status')
                    ->options(self::BUILD_STATUSES)
                    ->query(fn (Builder $query, array $data): Builder => match ($data['value'] ?? null) {
                        null, '' => $query,
                        'not_started' => $query->whereNull('settings->whitelabel->build_status'),
                        default => $query->where('settings->whitelabel->build_status', $data['value']),
                    }),
                SelectFilter::make('plan')->options(fn (): array => SaasPlan::query()->orderBy('sort')->pluck('code', 'code')->all()),
            ])
            ->recordActions([
                Action::make('editConfig')
                    ->label(__('Edit app config'))
                    ->icon(Heroicon::OutlinedPencilSquare)
                    ->modalWidth('3xl')
                    ->fillForm(function (Tenant $record): array {
                        $config = (array) $record->setting('whitelabel', []);
                        $config['colors'] = json_encode($config['colors'] ?? ['primary' => $record->brand_color], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);

                        return $config + ['app_name' => $record->name, 'default_locale' => $record->locale, 'build_status' => 'not_started'];
                    })
                    ->schema([
                        Section::make(__('Identity'))->columns(2)->schema([
                            TextInput::make('app_name')->label(__('App name'))->required()->maxLength(30)->helperText(__('Store title limit: 30 characters.')),
                            Select::make('default_locale')->options(Locales::options())->required(),
                            TextInput::make('bundle_id')->label(__('iOS bundle id'))->required()->maxLength(155)
                                ->regex('/^[A-Za-z][A-Za-z0-9\-]*(\.[A-Za-z0-9\-]+)+$/')->placeholder('com.example.coach'),
                            TextInput::make('android_package')->label(__('Android package'))->maxLength(155)
                                ->regex('/^[a-z][a-z0-9_]*(\.[a-z0-9_]+)+$/')->placeholder('com.example.coach'),
                            TextInput::make('deep_link_domain')->label(__('Deep-link domain'))->maxLength(255),
                        ]),
                        Section::make(__('Theme'))->schema([
                            Textarea::make('colors')->label(__('Colors (JSON)'))->rows(6)->required()
                                ->helperText(__('e.g. {"primary": "#D4AF6A", "background": "#0B0B0F", "onPrimary": "#0B0B0F"}'))
                                ->rules([fn (): \Closure => function (string $attribute, mixed $value, \Closure $fail): void {
                                    $decoded = json_decode((string) $value, true);
                                    if (! is_array($decoded) || array_is_list($decoded)) {
                                        $fail(__('Colors must be a JSON object of name → hex color.'));

                                        return;
                                    }
                                    foreach ($decoded as $name => $hex) {
                                        if (! is_string($hex) || ! preg_match('/^#[0-9A-Fa-f]{6}([0-9A-Fa-f]{2})?$/', $hex)) {
                                            $fail(__('Invalid hex color for “:name”.', ['name' => $name]));

                                            return;
                                        }
                                    }
                                }]),
                        ]),
                        Section::make(__('Build pipeline'))->columns(3)->schema([
                            Select::make('build_status')->options(self::BUILD_STATUSES)->required(),
                            TextInput::make('version')->maxLength(32)->placeholder('1.0.0 (1)'),
                            DateTimePicker::make('last_build_at')->label(__('Last build at')),
                            TextInput::make('store_status')->label(__('Store review status'))->maxLength(64),
                            Textarea::make('notes')->rows(2)->maxLength(1000)->columnSpan(2),
                        ]),
                    ])
                    ->action(function (Tenant $record, array $data): void {
                        $data['colors'] = json_decode((string) $data['colors'], true);
                        $settings = $record->settings ?? [];
                        $previous = $settings['whitelabel'] ?? [];
                        $settings['whitelabel'] = array_merge($previous, $data, ['updated_at' => now()->toIso8601String()]);
                        $record->forceFill(['settings' => $settings])->save();
                        ActivityLogger::log('admin.whitelabel.updated', $record, [
                            'build_status' => $data['build_status'],
                            'from_status' => $previous['build_status'] ?? null,
                        ], tenantId: $record->id);
                        Notification::make()->title(__('White-label config saved'))->success()->send();
                    }),
            ])
            ->emptyStateHeading(__('No tenant is eligible for a branded app yet'))
            ->emptyStateDescription(__('Tenants on a plan with the “branded_app” feature (Studio, Enterprise) or allow-listed on that flag appear here.'));
    }
}
