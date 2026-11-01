<?php

namespace App\Filament\Resources\Reviews;

use App\Domain\Marketing\Models\Review;
use App\Domain\Shared\Support\ActivityLogger;
use App\Domain\Tenancy\Models\CoachProfile;
use Filament\Actions\Action;
use Filament\Actions\BulkAction;
use Filament\Support\Icons\Heroicon;
use Illuminate\Database\Eloquent\Collection;

/** Approve / reject reviews and keep the coach profile's rating aggregate in sync. */
final class ReviewModeration
{
    public static function set(Review $review, string $status): void
    {
        $review->update(['status' => $status, 'moderated_at' => now()]);
        self::refreshRating($review->coach_profile_id);
        ActivityLogger::log('admin.review.'.$status, null, ['review_id' => $review->id, 'coach_profile_id' => $review->coach_profile_id], tenantId: $review->tenant_id);
    }

    public static function refreshRating(string $coachProfileId): void
    {
        $approved = Review::query()->withoutGlobalScopes()->where('coach_profile_id', $coachProfileId)->where('status', 'approved');
        $count = (clone $approved)->count();
        $avg = $count > 0 ? round((float) (clone $approved)->avg('rating'), 2) : 0;
        CoachProfile::query()->withoutGlobalScopes()->whereKey($coachProfileId)->update(['rating_avg' => $avg, 'rating_count' => $count]);
    }

    public static function approve(): Action
    {
        return Action::make('approve')
            ->label(__('Approve'))
            ->icon(Heroicon::OutlinedCheck)
            ->color('success')
            ->visible(fn (Review $record): bool => $record->status !== 'approved')
            ->action(fn (Review $record) => self::set($record, 'approved'));
    }

    public static function reject(): Action
    {
        return Action::make('reject')
            ->label(__('Reject'))
            ->icon(Heroicon::OutlinedXMark)
            ->color('danger')
            ->requiresConfirmation()
            ->visible(fn (Review $record): bool => $record->status !== 'rejected')
            ->action(fn (Review $record) => self::set($record, 'rejected'));
    }

    /** @return list<BulkAction> */
    public static function bulk(): array
    {
        return [
            BulkAction::make('approveSelected')->label(__('Approve'))->icon(Heroicon::OutlinedCheck)->color('success')
                ->action(fn (Collection $records) => $records->each(fn (Review $r) => self::set($r, 'approved')))
                ->deselectRecordsAfterCompletion(),
            BulkAction::make('rejectSelected')->label(__('Reject'))->icon(Heroicon::OutlinedXMark)->color('danger')->requiresConfirmation()
                ->action(fn (Collection $records) => $records->each(fn (Review $r) => self::set($r, 'rejected')))
                ->deselectRecordsAfterCompletion(),
        ];
    }
}
