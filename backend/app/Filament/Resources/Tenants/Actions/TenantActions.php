<?php

namespace App\Filament\Resources\Tenants\Actions;

use App\Domain\Billing\Models\SaasPlan;
use App\Domain\Billing\Services\SaasBillingService;
use App\Domain\Identity\Models\User;
use App\Domain\Shared\Support\ActivityLogger;
use App\Domain\Shared\Support\ApiException;
use App\Domain\Tenancy\Models\Tenant;
use App\Filament\Actions\ImpersonateAction;
use Filament\Actions\Action;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Toggle;
use Filament\Notifications\Notification;
use Filament\Support\Icons\Heroicon;
use Illuminate\Support\Facades\Cache;

final class TenantActions
{
    public static function changePlan(): Action
    {
        return Action::make('changePlan')
            ->label(__('Change plan'))
            ->icon(Heroicon::OutlinedArrowsRightLeft)
            ->color('primary')
            ->fillForm(fn (Tenant $record): array => ['plan' => $record->plan, 'interval' => 'month', 'bill' => false])
            ->schema([
                Select::make('plan')
                    ->options(fn (): array => SaasPlan::query()->orderBy('sort')->get()->mapWithKeys(fn (SaasPlan $p): array => [$p->code => $p->translate('name').' ('.$p->code.')'])->all())
                    ->required(),
                Toggle::make('bill')
                    ->label(__('Create a billed subscription'))
                    ->helperText(__('Off: complimentary plan change (no invoice). On: activates a subscription through the billing service and records an invoice.'))
                    ->live(),
                Select::make('interval')
                    ->options(['month' => __('Monthly'), 'year' => __('Annual')])
                    ->visible(fn ($get): bool => (bool) $get('bill'))
                    ->required(fn ($get): bool => (bool) $get('bill')),
            ])
            ->action(function (Tenant $record, array $data): void {
                $billing = app(SaasBillingService::class);
                $plan = SaasPlan::query()->where('code', $data['plan'])->firstOrFail();
                $from = $record->plan;

                try {
                    if ($plan->code === 'free') {
                        if ($record->subscription?->isActive()) {
                            $billing->cancel($record, 'admin_downgrade');
                        }
                        $record->forceFill(['plan' => 'free', 'trial_ends_at' => null])->save();
                    } elseif (! empty($data['bill'])) {
                        $billing->activate($record, $plan, $data['interval'] ?? 'month');
                    } else {
                        $record->forceFill(['plan' => $plan->code])->save();
                    }
                } catch (ApiException $e) {
                    Notification::make()->title($e->getMessage())->danger()->send();

                    return;
                }

                Cache::forget("plan:{$from}");
                Cache::forget("plan:{$plan->code}");
                ActivityLogger::log('admin.tenant.plan_changed', $record, ['from' => $from, 'to' => $plan->code, 'billed' => ! empty($data['bill'])], tenantId: $record->id);
                Notification::make()->title(__('Plan changed to :plan', ['plan' => $plan->code]))->success()->send();
            });
    }

    public static function suspend(): Action
    {
        return Action::make('suspend')
            ->label(__('Suspend'))
            ->icon(Heroicon::OutlinedNoSymbol)
            ->color('danger')
            ->requiresConfirmation()
            ->visible(fn (Tenant $record): bool => blank($record->setting('suspended_at')))
            ->schema([
                Textarea::make('reason')->required()->maxLength(500),
            ])
            ->action(function (Tenant $record, array $data): void {
                $settings = $record->settings ?? [];
                $settings['suspended_at'] = now()->toIso8601String();
                $settings['suspended_reason'] = $data['reason'];
                $settings['suspended_by'] = auth()->id();
                $record->forceFill(['settings' => $settings])->save();
                ActivityLogger::log('admin.tenant.suspended', $record, ['reason' => $data['reason']], tenantId: $record->id);
                Notification::make()->title(__('Tenant suspended'))->success()->send();
            });
    }

    public static function unsuspend(): Action
    {
        return Action::make('unsuspend')
            ->label(__('Reactivate'))
            ->icon(Heroicon::OutlinedCheckCircle)
            ->color('success')
            ->requiresConfirmation()
            ->visible(fn (Tenant $record): bool => filled($record->setting('suspended_at')))
            ->action(function (Tenant $record): void {
                $settings = $record->settings ?? [];
                unset($settings['suspended_at'], $settings['suspended_reason'], $settings['suspended_by']);
                $record->forceFill(['settings' => $settings])->save();
                ActivityLogger::log('admin.tenant.reactivated', $record, tenantId: $record->id);
                Notification::make()->title(__('Tenant reactivated'))->success()->send();
            });
    }

    public static function impersonateOwner(): Action
    {
        return ImpersonateAction::make(
            fn (Tenant $record): ?User => $record->users()->wherePivot('role', 'owner')->wherePivot('status', 'active')->orderBy('tenant_user.created_at')->first(),
            'impersonateOwner',
        )->label(__('Impersonate owner'));
    }
}
