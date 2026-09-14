<?php

namespace App\Policies;

use App\Models\Menu;
use App\Models\User;
use App\Support\Tenancy\TenantContext;

class MenuPolicy
{
    public function viewAny(User $user): bool
    {
        return in_array($user->role, ['tenant', 'admin'], true);
    }

    public function view(User $user, Menu $menu): bool
    {
        return $this->checkAccess($user, (int) $menu->tenant_id);
    }

    public function create(User $user): bool
    {
        return in_array($user->role, ['tenant', 'admin'], true);
    }

    public function update(User $user, Menu $menu): bool
    {
        return $this->checkAccess($user, (int) $menu->tenant_id);
    }

    public function delete(User $user, Menu $menu): bool
    {
        return $this->checkAccess($user, (int) $menu->tenant_id);
    }

    protected function checkAccess(User $user, int $tenantId): bool
    {
        if ($user->role === 'admin') {
            return true;
        }

        if ($user->role !== 'tenant') {
            return false;
        }

        $userTenantId = $user->tenant ? $user->tenant->id : app(TenantContext::class)->id();

        return (int) $userTenantId === $tenantId;
    }
}
