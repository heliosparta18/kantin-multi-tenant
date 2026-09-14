<?php

namespace Tests\Feature;

use Illuminate\Database\QueryException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class DatabaseIntegrityTest extends TestCase
{
    use RefreshDatabase;

    public function test_duplicate_tenant_code_in_same_canteen_is_rejected(): void
    {
        $canteenId = DB::table('canteens')->insertGetId([
            'name' => 'Kantin Utama',
            'code' => 'KU-01',
            'status' => 'active',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        DB::table('tenants')->insert([
            'canteen_id' => $canteenId,
            'name' => 'Stan Ayam',
            'code' => 'ST01',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $this->expectException(QueryException::class);

        DB::table('tenants')->insert([
            'canteen_id' => $canteenId,
            'name' => 'Stan Bebek',
            'code' => 'ST01',
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    public function test_modifier_referencing_different_tenant_is_rejected(): void
    {
        $canteenId = DB::table('canteens')->insertGetId([
            'name' => 'Kantin Pusat',
            'code' => 'KP-01',
            'status' => 'active',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $tenantA = DB::table('tenants')->insertGetId([
            'canteen_id' => $canteenId,
            'name' => 'Tenant A',
            'code' => 'TA01',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $tenantB = DB::table('tenants')->insertGetId([
            'canteen_id' => $canteenId,
            'name' => 'Tenant B',
            'code' => 'TB01',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $menuA = DB::table('menus')->insertGetId([
            'tenant_id' => $tenantA,
            'name' => 'Mie Goreng',
            'price' => 15000,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $this->expectException(QueryException::class);

        DB::table('modifiers')->insert([
            'tenant_id' => $tenantB,
            'menu_id' => $menuA,
            'name' => 'Ekstra Telur',
            'additional_price' => 3000,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    public function test_duplicate_order_idempotency_key_is_rejected(): void
    {
        $canteenId = DB::table('canteens')->insertGetId([
            'name' => 'Kantin Digital',
            'code' => 'KD-01',
            'status' => 'active',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $tenantId = DB::table('tenants')->insertGetId([
            'canteen_id' => $canteenId,
            'name' => 'Tenant Kopi',
            'code' => 'TK01',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $this->expectException(QueryException::class);

        for ($i = 0; $i < 2; $i++) {
            DB::table('orders')->insert([
                'canteen_id' => $canteenId,
                'tenant_id' => $tenantId,
                'order_number' => 'ORD-TEST-'.$i,
                'idempotency_key' => 'SAME-IDEMPOTENCY-KEY',
                'total_amount' => 15000,
                'status' => 'pending',
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }
}
