<?php

namespace App\Http\Middleware;

use App\Models\Tenant;
use App\Support\Tenancy\TenantContext;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class SetTenantContext
{
    public function __construct(
        protected TenantContext $tenantContext
    ) {}

    /**
     * Handle an incoming request.
     *
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $tenantParam = $request->route('tenant');

        if ($tenantParam instanceof Tenant) {
            $tenant = $tenantParam;
        } elseif (is_string($tenantParam)) {
            $tenant = Tenant::query()
                ->where('slug', $tenantParam)
                ->orWhere('id', $tenantParam)
                ->orWhere('code', $tenantParam)
                ->first();
        } else {
            $tenant = null;
        }

        if (! $tenant) {
            abort(404, 'Tenant not found.');
        }

        if ($tenant->status !== 'active') {
            abort(403, 'Tenant is inactive or suspended.');
        }

        $user = $request->user();
        if (! $user) {
            abort(401, 'Unauthenticated.');
        }

        $hasAccess = ($user->role === 'admin') || ($user->role === 'tenant' && (int) $tenant->user_id === (int) $user->id);

        if (! $hasAccess) {
            abort(403, 'User does not have access to this tenant.');
        }

        $this->tenantContext->set($tenant);

        try {
            return $next($request);
        } finally {
            $this->tenantContext->clear();
        }
    }
}
