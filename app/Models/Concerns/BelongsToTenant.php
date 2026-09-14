<?php

namespace App\Models\Concerns;

use App\Models\Tenant;
use App\Support\Tenancy\TenantContext;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use RuntimeException;

trait BelongsToTenant
{
    /**
     * Boot the BelongsToTenant trait for a model.
     */
    public static function bootBelongsToTenant(): void
    {
        static::addGlobalScope('tenant', function (Builder $builder): void {
            $context = app(TenantContext::class);
            if ($context->has()) {
                $builder->where(
                    $builder->qualifyColumn('tenant_id'),
                    $context->id()
                );
            }
        });

        static::creating(function (Model $model): void {
            $context = app(TenantContext::class);
            if ($context->has()) {
                $model->setAttribute('tenant_id', $context->id());
            } elseif (empty($model->getAttribute('tenant_id'))) {
                throw new RuntimeException('Cannot create tenant-owned model without an active tenant context.');
            }
        });

        static::saving(function (Model $model): void {
            $context = app(TenantContext::class);
            if ($context->has()) {
                $model->setAttribute('tenant_id', $context->id());
            }
        });
    }

    /**
     * Define the relationship to the tenant.
     *
     * @return BelongsTo<Tenant, $this>
     */
    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }
}
