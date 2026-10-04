<?php

namespace App\Modules\Admin;

use App\Modules\ModuleServiceProvider;

/**
 * Modul: Admin (alias `admin`).
 * Tanggung jawab: Administrasi kantin, tenant, role, komisi, rekening (Modul 5). Pemilik route admin.
 */
final class AdminServiceProvider extends ModuleServiceProvider
{
    public function register(): void {}

    protected function moduleAlias(): string
    {
        return 'admin';
    }
}
