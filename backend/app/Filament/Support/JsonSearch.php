<?php

namespace App\Filament\Support;

use Illuminate\Database\Eloquent\Builder;

/** Case-insensitive substring search inside a JSON column (all locales), portable across drivers. */
final class JsonSearch
{
    public static function orWhere(Builder $query, string $column, string $search): Builder
    {
        $column = $query->qualifyColumn($column);
        $grammar = $query->getQuery()->getGrammar();
        $wrapped = $grammar->wrap($column);
        $term = '%'.mb_strtolower($search).'%';

        return match ($query->getConnection()->getDriverName()) {
            'pgsql' => $query->orWhereRaw("LOWER(CAST({$wrapped} AS TEXT)) LIKE ?", [$term]),
            'mysql', 'mariadb' => $query->orWhereRaw("LOWER(CAST({$wrapped} AS CHAR)) LIKE ?", [$term]),
            default => $query->orWhereRaw("LOWER({$wrapped}) LIKE ?", [$term]),
        };
    }
}
