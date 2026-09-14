<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // User Admin
        User::create([
            'name' => 'Super Admin',
            'email' => 'admin@kantin.com',
            'password' => Hash::make('password'),
            'role' => 'admin',
            'status' => 'active',
        ]);

        // User Tenant (Pengelola Stan)
        User::create([
            'name' => 'Owner Stan Bakso',
            'email' => 'tenant@kantin.com',
            'password' => Hash::make('password'),
            'role' => 'tenant',
            'status' => 'active',
        ]);

        // User Customer (Pembeli)
        User::create([
            'name' => 'Customer Pembeli',
            'email' => 'customer@kantin.com',
            'password' => Hash::make('password'),
            'role' => 'customer',
            'status' => 'active',
        ]);
    }
}
