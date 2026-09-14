<?php

namespace App\Jobs;

use App\Models\Tenant;
use App\Support\Tenancy\TenantContext;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use RuntimeException;

class ProcessTenantOrderJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Payload stores only minimum primitive IDs, avoiding full model serialization.
     */
    public function __construct(
        public int $tenantId,
        public int $orderId,
    ) {}

    /**
     * Execute the job.
     */
    public function handle(TenantContext $tenantContext): void
    {
        $tenant = Tenant::find($this->tenantId);

        if (! $tenant || $tenant->status !== 'active') {
            throw new RuntimeException('Tenant not found or inactive for queued job.');
        }

        $tenantContext->set($tenant);

        try {
            // Domain processing logic executed under active tenant context
        } finally {
            $tenantContext->clear();
        }
    }
}
