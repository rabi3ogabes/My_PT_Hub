<?php

namespace App\Filament\Resources\MarketingLeads\Tables;

use App\Domain\Marketing\Models\MarketingLead;
use App\Domain\Shared\Support\ActivityLogger;
use App\Filament\Support\DateRangeFilter;
use App\Filament\Support\Locales;
use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Symfony\Component\HttpFoundation\StreamedResponse;

class MarketingLeadsTable
{
    public const KINDS = ['template_download' => 'Template download', 'waitlist' => 'Waitlist', 'demo' => 'Demo request', 'newsletter' => 'Newsletter'];

    /** Streams the given (filtered) query as CSV. Cells are neutralised against spreadsheet formula injection. */
    public static function csv(Builder $query): StreamedResponse
    {
        $safe = fn (?string $v): string => $v !== null && preg_match('/^[=+\-@\t\r]/', $v) ? "'".$v : (string) $v;

        return response()->streamDownload(function () use ($query, $safe): void {
            $out = fopen('php://output', 'w');
            fputcsv($out, ['email', 'kind', 'resource', 'locale', 'consent', 'created_at']);
            $query->clone()->reorder()->orderBy('created_at')->chunk(1000, function ($leads) use ($out, $safe): void {
                foreach ($leads as $lead) {
                    /** @var MarketingLead $lead */
                    fputcsv($out, [
                        $safe($lead->email), $lead->kind, $safe($lead->resource), $lead->locale,
                        $lead->consent ? 'yes' : 'no', $lead->created_at?->toIso8601String(),
                    ]);
                }
            });
            fclose($out);
        }, 'marketing-leads-'.now()->format('Y-m-d-His').'.csv', ['Content-Type' => 'text/csv; charset=UTF-8']);
    }

    public static function configure(Table $table): Table
    {
        return $table
            ->defaultSort('created_at', 'desc')
            ->columns([
                TextColumn::make('email')->searchable()->copyable()->weight('semibold'),
                TextColumn::make('kind')->badge()->formatStateUsing(fn (string $state): string => self::KINDS[$state] ?? $state),
                TextColumn::make('resource')->placeholder('—')->searchable()->limit(40),
                TextColumn::make('locale')->badge()->color('gray')->placeholder('—'),
                IconColumn::make('consent')->boolean(),
                TextColumn::make('created_at')->dateTime()->since()->sortable(),
            ])
            ->filters([
                SelectFilter::make('kind')->options(self::KINDS),
                SelectFilter::make('locale')->options(Locales::options()),
                TernaryFilter::make('consent')->label(__('Marketing consent')),
                DateRangeFilter::make(),
            ])
            ->headerActions([
                Action::make('exportCsv')
                    ->label(__('Export CSV'))
                    ->icon(Heroicon::OutlinedArrowDownTray)
                    ->color('gray')
                    ->action(function (HasTable $livewire): StreamedResponse {
                        $query = $livewire->getFilteredTableQuery() ?? MarketingLead::query();
                        ActivityLogger::log('admin.marketing_leads.exported', null, ['count' => $query->clone()->count()]);

                        return self::csv($query);
                    }),
            ])
            ->recordActions([DeleteAction::make()])
            ->toolbarActions([BulkActionGroup::make([DeleteBulkAction::make()])]);
    }
}
