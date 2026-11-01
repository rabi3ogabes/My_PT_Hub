<?php

namespace App\Filament\Actions;

use App\Domain\Identity\Models\User;
use App\Filament\Support\Impersonator;
use Closure;
use Filament\Actions\Action;
use Filament\Notifications\Notification;
use Filament\Support\Icons\Heroicon;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Database\Eloquent\Model;

/** "Log in as" action. The resolver maps the row (User, Tenant...) to the target user. */
final class ImpersonateAction
{
    /**
     * @param  Closure(Model): ?User|null  $resolveTarget
     */
    public static function make(?Closure $resolveTarget = null, string $name = 'impersonate'): Action
    {
        $resolveTarget ??= fn (Model $record): ?User => $record instanceof User ? $record : null;

        return Action::make($name)
            ->label(__('Impersonate'))
            ->icon(Heroicon::OutlinedFingerPrint)
            ->color('warning')
            ->requiresConfirmation()
            ->modalDescription(__('You will be logged in as this user. Every action is audited. Use "Stop impersonating" to return.'))
            ->visible(function (Model $record) use ($resolveTarget): bool {
                $target = $resolveTarget($record);
                $admin = auth()->user();

                return $target !== null && $admin instanceof User && app(Impersonator::class)->canImpersonate($admin, $target);
            })
            ->action(function (Model $record, Action $action) use ($resolveTarget) {
                $target = $resolveTarget($record);
                $admin = auth()->user();
                if (! $target || ! $admin instanceof User) {
                    $action->failure();

                    return null;
                }

                try {
                    app(Impersonator::class)->start($admin, $target);
                } catch (AuthorizationException $e) {
                    Notification::make()->title($e->getMessage())->danger()->send();
                    $action->halt();
                }

                return redirect()->to('/app');
            });
    }
}
