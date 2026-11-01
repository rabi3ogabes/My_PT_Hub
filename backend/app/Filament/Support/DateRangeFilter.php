<?php

namespace App\Filament\Support;

use Filament\Forms\Components\DatePicker;
use Filament\Tables\Filters\Filter;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Carbon;

final class DateRangeFilter
{
    public static function make(string $column = 'created_at', string $name = 'created_between'): Filter
    {
        return Filter::make($name)
            ->schema([
                DatePicker::make('from')->label(__('From')),
                DatePicker::make('until')->label(__('Until')),
            ])
            ->columns(2)
            ->query(function (Builder $query, array $data) use ($column): Builder {
                $qualified = $query->qualifyColumn($column);

                return $query
                    ->when($data['from'] ?? null, fn (Builder $q, $from) => $q->where($qualified, '>=', Carbon::parse($from)->startOfDay()))
                    ->when($data['until'] ?? null, fn (Builder $q, $until) => $q->where($qualified, '<=', Carbon::parse($until)->endOfDay()));
            })
            ->indicateUsing(function (array $data): array {
                $out = [];
                if ($data['from'] ?? null) {
                    $out[] = __('From :date', ['date' => Carbon::parse($data['from'])->toFormattedDateString()]);
                }
                if ($data['until'] ?? null) {
                    $out[] = __('Until :date', ['date' => Carbon::parse($data['until'])->toFormattedDateString()]);
                }

                return $out;
            });
    }
}
