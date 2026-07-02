<?php

namespace Database\Seeders;

use App\Models\Area;
use App\Models\City;
use App\Services\Import\CsvImportService;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class AreaSeeder extends Seeder
{
    public function run(): void
    {
        $rows = CsvImportService::read('areas.csv');

        $cities = City::pluck('id', 'code');

        DB::beginTransaction();

        try {

            $imported = 0;
            $skipped = 0;

            foreach ($rows as $row) {

                if (count($row) < 6) {
                    $skipped++;
                    continue;
                }

                [
                    $cityCode,
                    $areaCode,
                    $areaName,
                    $postalCode,
                    $latitude,
                    $longitude
                ] = $row;

                $cityId = $cities[trim($cityCode)] ?? null;

                if (!$cityId) {
                    $skipped++;
                    continue;
                }

                Area::updateOrCreate(

                    [
                        'code' => trim($areaCode)
                    ],

                    [
                        'uuid' => Str::uuid(),

                        'city_id' => $cityId,

                        'name' => trim($areaName),

                        'postal_code' => trim($postalCode),

                        'latitude' => $latitude ?: null,

                        'longitude' => $longitude ?: null,

                        'is_active' => true,
                    ]
                );

                $imported++;
            }

            DB::commit();

            $this->command->info("=================================");
            $this->command->info(" Area Seeder Completed");
            $this->command->info("=================================");
            $this->command->info(" Imported : {$imported}");
            $this->command->info(" Skipped  : {$skipped}");
            $this->command->info("=================================");

        } catch (\Throwable $e) {

            DB::rollBack();

            throw $e;
        }
    }
}