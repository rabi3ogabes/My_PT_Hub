<?php

namespace App\Filament\Resources\BlogPosts\Tables;

use App\Domain\Marketing\Models\BlogPost;
use App\Filament\Support\Locales;
use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class BlogPostsTable
{
    public static function status(BlogPost $post): string
    {
        return match (true) {
            $post->published_at === null => 'draft',
            $post->published_at->isFuture() => 'scheduled',
            default => 'published',
        };
    }

    public static function configure(Table $table): Table
    {
        return $table
            ->defaultSort('published_at', 'desc')
            ->columns([
                TextColumn::make('title')->searchable()->sortable()->weight('semibold')->limit(70)
                    ->description(fn (BlogPost $record): string => '/'.$record->locale.'/blog/'.$record->slug),
                TextColumn::make('locale')->badge()->sortable(),
                TextColumn::make('category')->badge()->color('gray')->toggleable(),
                TextColumn::make('status')->badge()
                    ->state(fn (BlogPost $record): string => self::status($record))
                    ->color(fn (string $state): string => match ($state) {
                        'published' => 'success', 'scheduled' => 'info', default => 'gray',
                    }),
                TextColumn::make('published_at')->dateTime()->sortable()->placeholder('—'),
                IconColumn::make('noindex')->boolean()->trueColor('warning')->falseColor('gray'),
                TextColumn::make('updated_at')->dateTime()->since()->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('locale')->options(Locales::options()),
                SelectFilter::make('status')
                    ->options(['draft' => __('Draft'), 'scheduled' => __('Scheduled'), 'published' => __('Published')])
                    ->query(fn (Builder $query, array $data): Builder => match ($data['value'] ?? null) {
                        'draft' => $query->whereNull('published_at'),
                        'scheduled' => $query->where('published_at', '>', now()),
                        'published' => $query->where('published_at', '<=', now()),
                        default => $query,
                    }),
                TernaryFilter::make('noindex'),
            ])
            ->recordActions([
                Action::make('publishNow')
                    ->label(__('Publish now'))
                    ->icon(Heroicon::OutlinedRocketLaunch)
                    ->color('success')
                    ->visible(fn (BlogPost $record): bool => self::status($record) !== 'published')
                    ->action(fn (BlogPost $record) => $record->update(['published_at' => now()])),
                Action::make('unpublish')
                    ->label(__('Unpublish'))
                    ->icon(Heroicon::OutlinedEyeSlash)
                    ->color('gray')
                    ->requiresConfirmation()
                    ->visible(fn (BlogPost $record): bool => $record->published_at !== null)
                    ->action(fn (BlogPost $record) => $record->update(['published_at' => null])),
                EditAction::make(),
                DeleteAction::make(),
            ])
            ->toolbarActions([BulkActionGroup::make([DeleteBulkAction::make()])]);
    }
}
