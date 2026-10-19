<?php

namespace Database\Seeders;

use App\Models\Ward;
use App\Services\WardImporter;
use Illuminate\Database\Seeder;

/**
 * Loads the ward list. Order of attempts:
 *  1. database/data/wards.json (if the file exists)
 *  2. download the JSON from the exam URL (and cache it in database/data/wards.json)
 *  3. built-in sample list (offline machine)
 */
class WardSeeder extends Seeder
{
    public function run(WardImporter $importer): void
    {
        if (Ward::count() > 0) {
            return;
        }

        $cache = database_path('data/wards.json');
        $count = 0;

        try {
            $count = is_file($cache)
                ? $importer->importFromFile($cache)
                : $importer->importFromUrl(config('bikego.wards_url'), $cache);
        } catch (\Throwable $e) {
            optional($this->command)->warn('Could not load the ward JSON ('.$e->getMessage().'). Using the built-in sample list.');
        }

        if ($count === 0) {
            $importer->importFallback();
        }
    }
}
