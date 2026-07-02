<?php

namespace Database\Seeders;

use App\Models\City;
use App\Models\District;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;

class CitySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $path = database_path('seeders/data/cities.csv');

        if (!File::exists($path)) {
            $this->command->error('cities.csv not found.');

            return;
        }

        // Load all districts into memory (1 query only)
        $districts = District::pluck('id', 'code');

        $handle = fopen($path, 'r');

        // Skip CSV header
        fgetcsv($handle);

        DB::beginTransaction();

        try {

            $count = 0;
            $skipped = 0;

            while (($row = fgetcsv($handle, 1000, ',')) !== false) {

                if (count($row) < 6) {
                    $skipped++;
                    continue;
                }

                [
                    $districtCode,
                    $cityCode,
                    $cityName,
                    $postalCode,
                    $latitude,
                    $longitude
                ] = $row;

                $districtCode = trim($districtCode);

                $districtId = $districts[$districtCode] ?? null;

                if (!$districtId) {

                    $this->command->warn("District '{$districtCode}' not found.");

                    $skipped++;

                    continue;
                }

                City::updateOrCreate(

                    [
                        'code' => trim($cityCode)
                    ],

                    [
                        'uuid' => Str::uuid(),

                        'district_id' => $districtId,

                        'code' => trim($cityCode),

                        'name' => trim($cityName),

                        'postal_code' => trim($postalCode) ?: null,

                        'latitude' => $latitude ?: null,

                        'longitude' => $longitude ?: null,

                        'is_active' => true,
                    ]

                );

                $count++;
            }

            fclose($handle);

            DB::commit();

            $this->command->info("");
            $this->command->info("=================================");
            $this->command->info(" City Seeder Completed");
            $this->command->info("=================================");
            $this->command->info(" Imported : {$count}");
            $this->command->info(" Skipped  : {$skipped}");
            $this->command->info("=================================");

        } catch (\Throwable $e) {

            DB::rollBack();

            fclose($handle);

            throw $e;
        }
    }
}