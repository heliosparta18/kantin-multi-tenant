<?php

namespace App\Providers;

use App\Models\Menu;
use App\Models\Order;
use App\Models\TenantOrder;
use App\Models\Withdrawal;
use App\Policies\MenuPolicy;
use App\Policies\TenantOrderPolicy;
use App\Policies\WithdrawalPolicy;
use App\Support\Tenancy\TenantContext;
use Carbon\CarbonImmutable;
use Illuminate\Support\Facades\Date;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;
use Illuminate\Validation\Rules\Password;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->scoped(
            TenantContext::class,
            fn (): TenantContext => new TenantContext,
        );
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        $this->configureDefaults();

        Gate::policy(Menu::class, MenuPolicy::class);
        Gate::policy(Order::class, TenantOrderPolicy::class);
        Gate::policy(TenantOrder::class, TenantOrderPolicy::class);
        Gate::policy(Withdrawal::class, WithdrawalPolicy::class);
    }

    /**
     * Configure default behaviors for production-ready applications.
     */
    protected function configureDefaults(): void
    {
        Date::use(CarbonImmutable::class);

        DB::prohibitDestructiveCommands(
            app()->isProduction(),
        );

        Password::defaults(fn (): ?Password => app()->isProduction()
            ? Password::min(12)
                ->mixedCase()
                ->letters()
                ->numbers()
                ->symbols()
                ->uncompromised()
            : null,
        );
    }
}
