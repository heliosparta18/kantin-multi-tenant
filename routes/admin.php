<?php

use App\Support\Routing\PortalRoutes;
use Illuminate\Support\Facades\Route;

/**
 * Administrasi tenant/role/komisi/rekening (Modul 5): app/Modules/Admin/routes/admin.php.
 * Policy per-aksi (TenantPolicy) diperiksa di controller modul.
 */
PortalRoutes::admin(function (): void {
    Route::get('/dashboard', fn () => view('admin.dashboard'))->name('dashboard');
});
