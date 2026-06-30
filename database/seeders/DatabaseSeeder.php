<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([

            RoleSeeder::class,

            PermissionSeeder::class,

            SuperAdminSeeder::class,

            CountrySeeder::class,
            
            ProvinceSeeder::class,
            
            DistrictSeeder::class,
            
            CitySeeder::class,
            
            AreaSeeder::class,

        ]);
    }
}