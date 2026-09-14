<?php

namespace App\Policies;

use App\Models\Order;
use App\Models\TenantOrder;
use App\Models\User;
use App\Support\Tenancy\TenantContext;

class TenantOrderPolicy
{
    public function viewAny(User $user): bool
    {
        return in_array($user->role, ['tenant', 'admin'], true);
    }

    public function view(User $user, Order|TenantOrder $order): bool
    {
        return $this->checkAccess($user, (int) $order->tenant_id);
    }

    public function create(User $user): bool
    {
        return in_array($user->role, ['tenant', 'admin', 'customer'], true);
    }

    public function update(User $user, Order|TenantOrder $order): bool
    {
        return $this->checkAccess($user, (int) $order->tenant_id);
    }

    public function delete(User $user, Order|TenantOrder $order): bool
    {
        return $this->checkAccess($user, (int) $order->tenant_id);
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
