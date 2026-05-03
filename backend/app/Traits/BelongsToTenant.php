<?php

namespace App\Traits;

use App\Models\Tenant;
use Illuminate\Database\Eloquent\Builder;

trait BelongsToTenant
{
    /**
     * Boot the trait for a model.
     */
    protected static function bootBelongsToTenant()
    {
        \Log::info('BelongsToTenant: Booting for model ' . static::class);

        static::addGlobalScope('tenant', function (Builder $builder) {
            // Se estiver no console (migrações, etc), não aplicamos o filtro global para evitar problemas
            if (app()->runningInConsole()) {
                return;
            }

            $tenantId = session('tenant_id') ?? request()->attributes->get('tenant_id') ?? config('app.current_tenant_id') ?? 1;
            
            \Log::info('BelongsToTenant: Applying global scope for model ' . static::class . ' with tenant_id: ' . $tenantId);
            $builder->where('tenant_id', $tenantId);
        });

        static::creating(function ($model) {
            \Log::info('BelongsToTenant: Creating model ' . get_class($model));
            $tenantId = session('tenant_id') ?? request()->attributes->get('tenant_id') ?? config('app.current_tenant_id') ?? 1;
            $model->tenant_id = $tenantId;
        });
    }

    public function tenant()
    {
        return $this->belongsTo(Tenant::class);
    }
}
