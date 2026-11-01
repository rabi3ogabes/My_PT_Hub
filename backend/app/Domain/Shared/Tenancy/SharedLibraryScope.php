<?php

namespace App\Domain\Shared\Tenancy;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Scope;

/** Rows visible to a tenant: the global library (tenant_id null) plus its own rows. */
class SharedLibraryScope implements Scope
{
    public function apply(Builder $builder, Model $model): void
    {
        $context = app(TenantContext::class);
        if ($context->has()) {
            $column = $model->qualifyColumn('tenant_id');
            $builder->where(fn (Builder $q) => $q->whereNull($column)->orWhere($column, $context->id()));
        }
    }
}
