<?php

namespace App\Filament\Resources\SaasSubscriptions\Actions;

use App\Domain\Billing\Gateways\SaasGateway;
use App\Domain\Billing\Models\SaasInvoice;
use App\Domain\Billing\Models\SaasSubscription;
use App\Domain\Billing\Services\SaasBillingService;
use App\Domain\Shared\Support\ActivityLogger;
use App\Domain\Shared\Support\ApiException;
use App\Domain\Shared\Support\Money;
use Filament\Actions\Action;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Support\Icons\Heroicon;
use Illuminate\Support\Facades\DB;

final class SubscriptionActions
{
    private static function isCurrent(SaasSubscription $record): bool
    {
        return $record->isActive() && $record->tenant?->subscription?->is($record) === true;
    }

    /** Two-click cancel through the billing service (end of period monthly, pro-rata refund annual). */
    public static function cancel(): Action
    {
        return Action::make('cancel')
            ->label(__('Cancel subscription'))
            ->icon(Heroicon::OutlinedXCircle)
            ->color('danger')
            ->requiresConfirmation()
            ->modalDescription(fn (SaasSubscription $record): string => $record->interval === 'year' && $record->status !== 'trialing'
                ? __('Annual plan: cancels now and refunds :amount pro-rata.', ['amount' => Money::format(app(SaasBillingService::class)->proRataRefund($record), $record->currency)])
                : __('Monthly plan / trial: access continues until the period end (trials end now).'))
            ->schema([
                TextInput::make('reason')->default('admin_cancel')->maxLength(255),
            ])
            ->visible(fn (SaasSubscription $record): bool => self::isCurrent($record) && ! $record->cancel_at_period_end)
            ->action(function (SaasSubscription $record, array $data): void {
                try {
                    $result = app(SaasBillingService::class)->cancel($record->tenant, $data['reason'] ?: 'admin_cancel');
                } catch (ApiException $e) {
                    Notification::make()->title($e->getMessage())->danger()->send();

                    return;
                }
                Notification::make()
                    ->title(__('Subscription cancelled'))
                    ->body($result['refund_minor'] > 0 ? __('Refunded :amount', ['amount' => Money::format($result['refund_minor'], $record->currency)]) : null)
                    ->success()->send();
            });
    }

    public static function resume(): Action
    {
        return Action::make('resume')
            ->label(__('Resume'))
            ->icon(Heroicon::OutlinedArrowPath)
            ->color('success')
            ->requiresConfirmation()
            ->visible(fn (SaasSubscription $record): bool => self::isCurrent($record) && $record->cancel_at_period_end && $record->current_period_end?->isFuture())
            ->action(function (SaasSubscription $record): void {
                try {
                    app(SaasBillingService::class)->resume($record->tenant);
                } catch (ApiException $e) {
                    Notification::make()->title($e->getMessage())->danger()->send();

                    return;
                }
                ActivityLogger::log('admin.subscription.resumed', $record, tenantId: $record->tenant_id);
                Notification::make()->title(__('Subscription resumed'))->success()->send();
            });
    }

    /**
     * Goodwill / partial refund through the gateway. Defaults to the pro-rata
     * amount computed by the billing service; recorded as a negative invoice.
     */
    public static function refund(): Action
    {
        return Action::make('refund')
            ->label(__('Refund'))
            ->icon(Heroicon::OutlinedReceiptRefund)
            ->color('warning')
            ->requiresConfirmation()
            ->visible(fn (SaasSubscription $record): bool => $record->price_minor > 0 && $record->status !== 'trialing')
            ->fillForm(fn (SaasSubscription $record): array => ['amount_minor' => app(SaasBillingService::class)->proRataRefund($record)])
            ->schema(fn (SaasSubscription $record): array => [
                TextInput::make('amount_minor')
                    ->label(__('Amount (minor units, :currency)', ['currency' => $record->currency]))
                    ->numeric()->integer()->minValue(1)->maxValue($record->price_minor)->required(),
                TextInput::make('reason')->maxLength(255)->required(),
            ])
            ->action(function (SaasSubscription $record, array $data): void {
                $amount = (int) $data['amount_minor'];
                try {
                    $gatewayId = app(SaasGateway::class)->refund($record, $amount);
                } catch (\Throwable $e) {
                    report($e);
                    Notification::make()->title(__('Refund failed'))->body($e->getMessage())->danger()->send();

                    return;
                }

                DB::transaction(function () use ($record, $amount, $gatewayId, $data): void {
                    $seq = SaasInvoice::query()->lockForUpdate()->count() + 1;
                    SaasInvoice::create([
                        'tenant_id' => $record->tenant_id,
                        'saas_subscription_id' => $record->id,
                        'number' => sprintf('AUR-%s-%06d', now()->format('Y'), $seq),
                        'amount_minor' => -$amount,
                        'currency' => $record->currency,
                        'status' => 'refunded',
                        'description' => __('Refund').': '.$data['reason'],
                        'gateway_invoice_id' => $gatewayId,
                    ]);
                });

                ActivityLogger::log('billing.refunded', $record, ['amount_minor' => $amount, 'reason' => $data['reason'], 'by_admin' => true], tenantId: $record->tenant_id);
                Notification::make()->title(__('Refunded :amount', ['amount' => Money::format($amount, $record->currency)]))->success()->send();
            });
    }
}
