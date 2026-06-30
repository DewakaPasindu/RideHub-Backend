<?php

namespace Database\Seeders;

use App\Models\Country;
use App\Models\Province;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class ProvinceSeeder extends Seeder
{
    public function run(): void
    {
        $country = Country::where('iso2', 'LK')->first();

        $provinces = [
            ['Western', 'WP'],
            ['Central', 'CP'],
            ['Southern', 'SP'],
            ['Northern', 'NP'],
            ['Eastern', 'EP'],
            ['North Western', 'NWP'],
            ['North Central', 'NCP'],
            ['Uva', 'UP'],
            ['Sabaragamuwa', 'SGP'],
        ];

        foreach ($provinces as [$name, $code]) {

            Province::updateOrCreate(
                [
                    'country_id' => $country->id,
                    'name' => $name,
                ],
                [
                    'uuid' => Str::uuid(),
                    'code' => $code,
                ]
            );
        }
    }
}