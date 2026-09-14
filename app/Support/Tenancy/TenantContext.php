<?php

namespace App\Support\Tenancy;

use App\Models\Tenant;

class TenantContext
{
    protected ?Tenant $tenant = null;

    /**
     * Set the active tenant for the current request or job lifecycle.
     */
    public function set(Tenant $tenant): void
    {
        $this->tenant = $tenant;
    }

    /**
     * Clear the active tenant context.
     */
    public function clear(): void
    {
        $this->tenant = null;
    }

    /**
     * Check if an active tenant context exists.
     */
    public function has(): bool
    {
        return $this->tenant !== null;
    }

    /**
     * Get the currently active tenant model instance.
     */
    public function tenant(): ?Tenant
    {
        return $this->tenant;
    }

    /**
     * Get the currently active tenant ID.
     */
    public function id(): ?int
    {
        return $this->tenant?->id;
    }
}
