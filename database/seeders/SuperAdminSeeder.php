<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class SuperAdminSeeder extends Seeder
{
    public function run(): void
    {
        $admin = User::firstOrCreate(
            [
                'email' => 'admin@ridehub.com'
            ],
            [
                'uuid' => \Illuminate\Support\Str::uuid(),

                'first_name' => 'RideHub',

                'last_name' => 'Administrator',

                'password' => Hash::make('Admin@123'),

                'status' => 'active',
            ]
        );

        $admin->assignRole('Super Admin');
    }
}