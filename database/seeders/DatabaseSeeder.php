<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // Admin
        User::firstOrCreate(
            ['email' => 'admin@kantin.com'],
            [
                'name' => 'Super Admin',
                'password' => Hash::make('password'),
                'role' => 'admin',
                'status' => 'active',
            ]
        );

        // User Tenant (Stan Bakso)
        User::firstOrCreate(
            ['email' => 'tenant@kantin.com'],
            [
                'name' => 'Owner Stan Bakso',
                'password' => Hash::make('password'),
                'role' => 'tenant',
                'status' => 'active',
            ]
        );

        // User Customer
        User::firstOrCreate(
            ['email' => 'customer@kantin.com'],
            [
                'name' => 'Customer Pembeli',
                'password' => Hash::make('password'),
                'role' => 'customer',
                'status' => 'active',
            ]
        );

        // Panggil seeder kantin
        $this->call(DemoCanteenSeeder::class);
    }
}
