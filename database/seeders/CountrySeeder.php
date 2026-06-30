<?php

namespace Database\Seeders;

use App\Models\Country;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class CountrySeeder extends Seeder
{
    public function run(): void
    {
        Country::updateOrCreate(
            ['iso2' => 'LK'],
            [
                'uuid' => Str::uuid(),
                'name' => 'Sri Lanka',
                'iso3' => 'LKA',
                'phone_code' => '94',
                'is_active' => true,
            ]
        );
    }
}