<?php

namespace Database\Seeders;

use App\Models\District;
use App\Models\Province;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class DistrictSeeder extends Seeder
{
    public function run(): void
    {
        $districts = [

            // Western Province
            ['province_code' => 'WP', 'code' => 'COL', 'name' => 'Colombo'],
            ['province_code' => 'WP', 'code' => 'GAM', 'name' => 'Gampaha'],
            ['province_code' => 'WP', 'code' => 'KLU', 'name' => 'Kalutara'],

            // Central Province
            ['province_code' => 'CP', 'code' => 'KAN', 'name' => 'Kandy'],
            ['province_code' => 'CP', 'code' => 'MAT', 'name' => 'Matale'],
            ['province_code' => 'CP', 'code' => 'NEL', 'name' => 'Nuwara Eliya'],

            // Southern Province
            ['province_code' => 'SP', 'code' => 'GAL', 'name' => 'Galle'],
            ['province_code' => 'SP', 'code' => 'MATA', 'name' => 'Matara'],
            ['province_code' => 'SP', 'code' => 'HAM', 'name' => 'Hambantota'],

            // Northern Province
            ['province_code' => 'NP', 'code' => 'JAF', 'name' => 'Jaffna'],
            ['province_code' => 'NP', 'code' => 'KIL', 'name' => 'Kilinochchi'],
            ['province_code' => 'NP', 'code' => 'MAN', 'name' => 'Mannar'],
            ['province_code' => 'NP', 'code' => 'MUL', 'name' => 'Mullaitivu'],
            ['province_code' => 'NP', 'code' => 'VAV', 'name' => 'Vavuniya'],

            // Eastern Province
            ['province_code' => 'EP', 'code' => 'TRI', 'name' => 'Trincomalee'],
            ['province_code' => 'EP', 'code' => 'BAT', 'name' => 'Batticaloa'],
            ['province_code' => 'EP', 'code' => 'AMP', 'name' => 'Ampara'],

            // North Western Province
            ['province_code' => 'NW', 'code' => 'KUR', 'name' => 'Kurunegala'],
            ['province_code' => 'NW', 'code' => 'PUT', 'name' => 'Puttalam'],

            // North Central Province
            ['province_code' => 'NC', 'code' => 'ANU', 'name' => 'Anuradhapura'],
            ['province_code' => 'NC', 'code' => 'POL', 'name' => 'Polonnaruwa'],

            // Uva Province
            ['province_code' => 'UV', 'code' => 'BAD', 'name' => 'Badulla'],
            ['province_code' => 'UV', 'code' => 'MON', 'name' => 'Monaragala'],

            // Sabaragamuwa Province
            ['province_code' => 'SG', 'code' => 'RAT', 'name' => 'Ratnapura'],
            ['province_code' => 'SG', 'code' => 'KEG', 'name' => 'Kegalle'],
        ];

        foreach ($districts as $district) {

            $province = Province::where('code', $district['province_code'])->first();

            if (!$province) {
                continue;
            }

            District::updateOrCreate(
                [
                    'code' => $district['code'],
                ],
                [
                    'uuid' => Str::uuid(),
                    'province_id' => $province->id,
                    'name' => $district['name'],
                ]
            );
        }
    }
}