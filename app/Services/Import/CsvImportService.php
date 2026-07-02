<?php

namespace App\Services\Import;

use Illuminate\Support\Facades\File;

class CsvImportService
{
    /**
     * Read a CSV file and return all rows.
     *
     * @param string $fileName
     * @return array
     */
    public static function read(string $fileName): array
    {
        $path = database_path("seeders/data/{$fileName}");

        if (! File::exists($path)) {
            throw new \Exception("CSV file not found: {$fileName}");
        }

        $rows = [];

        if (($handle = fopen($path, 'r')) !== false) {

            // Skip header
            fgetcsv($handle);

            while (($data = fgetcsv($handle, 1000, ',')) !== false) {
                $rows[] = $data;
            }

            fclose($handle);
        }

        return $rows;
    }
}