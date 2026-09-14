<?php

namespace App\Modules\Catalog\Services;

use App\Models\Menu;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Log;

class PublicCatalogQuery
{
    /**
     * Get active menus for a specific canteen, bypassing tenant scope with mandatory substitute filters.
     *
     * @return Collection<int, Menu>
     */
    public function getMenusForCanteen(int $canteenId): Collection
    {
        /** @var Collection<int, Menu> $menus */
        $menus = Menu::withoutGlobalScope('tenant')
            ->whereHas('tenant', function (Builder $query) use ($canteenId): void {
                $query->where('canteen_id', $canteenId)
                    ->where('status', 'active');
            })
            ->where('is_available', true)
            ->get();

        return $menus;
    }

    /**
     * Get active tenants for a specific canteen.
     *
     * @return Collection<int, Tenant>
     */
    public function getTenantsForCanteen(int $canteenId): Collection
    {
        return Tenant::query()
            ->where('canteen_id', $canteenId)
            ->where('status', 'active')
            ->get();
    }

    /**
     * Log cross-tenant access performed by platform admins.
     *
     * @param  array<string, mixed>  $context
     */
    public function logPlatformAdminAccess(User $admin, string $action, array $context = []): void
    {
        Log::info('Platform admin cross-tenant access performed', [
            'admin_id' => $admin->id,
            'admin_email' => $admin->email,
            'action' => $action,
            'context' => $context,
            'timestamp' => now()->toIso8601String(),
        ]);
    }
}
