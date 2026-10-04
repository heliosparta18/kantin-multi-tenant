<?php

namespace Database\Seeders;

use App\Models\Canteen;
use App\Models\Category;
use App\Models\Commission;
use App\Models\CommissionScheme;
use App\Models\Menu;
use App\Models\Modifier;
use App\Models\Table;
use App\Models\Tenant;
use App\Models\TenantBalance;
use App\Models\User;
use App\Models\UserCanteenRole;
use App\Models\UserTenantRole;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DemoCanteenSeeder extends Seeder
{
    public function run(): void
    {
        // 1 Kantin
        $canteen = Canteen::updateOrCreate(
            ['code' => 'KANTIN-POLIWANGI'],
            ['name' => 'Kantin Terpadu Poliwangi', 'status' => 'active']
        );

        // Admin demo -> pengelola (manager) Kantin Pusat.
        foreach (['admin@kantin.test', 'admin@kantin.com'] as $email) {
            $admin = User::firstOrCreate(
                ['email' => $email],
                [
                    'name' => 'Admin Kantin',
                    'password' => Hash::make('password'),
                    'role' => 'admin',
                    'status' => 'active',
                    'email_verified_at' => now(),
                ]
            );
            UserCanteenRole::firstOrCreate(
                ['user_id' => $admin->id, 'canteen_id' => $canteen->id, 'role' => 'manager'],
            );
        }

        // User Tenant 1 & 2
        $user1 = User::updateOrCreate(
            ['email' => 'stan.bakso@kantin.com'],
            ['name' => 'Pak Joko Bakso', 'password' => Hash::make('password'), 'role' => 'tenant', 'status' => 'active']
        );

        $user2 = User::updateOrCreate(
            ['email' => 'stan.jus@kantin.com'],
            ['name' => 'Bu Siti Jus Segar', 'password' => Hash::make('password'), 'role' => 'tenant', 'status' => 'active']
        );

        // 2 Tenant
        $tenant1 = Tenant::updateOrCreate(
            ['canteen_id' => $canteen->id, 'code' => 'ST01'],
            [
                'user_id' => $user1->id,
                'name' => 'Stan Bakso Solo',
                'display_name' => 'Stan Bakso Solo',
                'slug' => 'stan-bakso-solo',
                'bank_name' => 'BCA',
                'bank_account_number' => '1234567890',
                'bank_account_last4' => '7890',
                'bank_account_holder' => 'Joko',
                'status' => 'active',
            ]
        );

        $tenant2 = Tenant::updateOrCreate(
            ['canteen_id' => $canteen->id, 'code' => 'ST02'],
            [
                'user_id' => $user2->id,
                'name' => 'Stan Aneka Jus',
                'display_name' => 'Stan Aneka Jus',
                'slug' => 'stan-aneka-jus',
                'bank_name' => 'BRI',
                'bank_account_number' => '9876543210',
                'bank_account_last4' => '3210',
                'bank_account_holder' => 'Siti',
                'status' => 'active',
            ]
        );

        UserTenantRole::firstOrCreate(
            ['user_id' => $user1->id, 'tenant_id' => $tenant1->id, 'role' => 'owner'],
        );

        UserTenantRole::firstOrCreate(
            ['user_id' => $user2->id, 'tenant_id' => $tenant2->id, 'role' => 'owner'],
        );

        foreach ([$tenant1, $tenant2] as $t) {
            TenantBalance::firstOrCreate(
                ['tenant_id' => $t->id],
                ['available_amount' => 0, 'held_amount' => 0]
            );
            CommissionScheme::firstOrCreate(
                ['tenant_id' => $t->id, 'valid_to' => null],
                [
                    'commission_rate' => 0.1000,
                    'valid_from' => now()->startOfYear(),
                ]
            );
        }

        // Meja Kantin
        Table::updateOrCreate(
            ['canteen_id' => $canteen->id, 'table_number' => 'M01'],
            ['qr_code_token' => 'QR-POLI-M01', 'status' => 'available']
        );

        // Komisi
        Commission::updateOrCreate(
            ['tenant_id' => $tenant1->id, 'starts_at' => now()->startOfYear()->toDateString()],
            ['percentage' => 10.00, 'status' => 'active']
        );

        Commission::updateOrCreate(
            ['tenant_id' => $tenant2->id, 'starts_at' => now()->startOfYear()->toDateString()],
            ['percentage' => 10.00, 'status' => 'active']
        );

        // Kategori & Menu Tenant 1
        $catBakso = Category::updateOrCreate(
            ['tenant_id' => $tenant1->id, 'name' => 'Makanan Utama']
        );

        $menuBakso = Menu::updateOrCreate(
            ['tenant_id' => $tenant1->id, 'name' => 'Bakso Urat Istimewa'],
            ['category_id' => $catBakso->id, 'price' => 15000, 'is_available' => true]
        );

        Modifier::updateOrCreate(
            ['tenant_id' => $tenant1->id, 'menu_id' => $menuBakso->id, 'name' => 'Ekstra Pangsit'],
            ['additional_price' => 2000]
        );
    }
}
