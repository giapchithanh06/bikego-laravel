<?php

namespace App\Console\Commands;

use App\Services\WardImporter;
use Illuminate\Console\Command;

/** Imports the ward list into the database from the exam JSON (file or URL). */
class ImportWards extends Command
{
    protected $signature = 'bikego:import-wards {--file= : Local JSON file to import instead of downloading}';

    protected $description = 'Import the Ho Chi Minh City ward list from JSON';

    public function handle(WardImporter $importer): int
    {
        try {
            if ($file = $this->option('file')) {
                $count = $importer->importFromFile($file);
            } else {
                $count = $importer->importFromUrl(config('bikego.wards_url'), database_path('data/wards.json'));
            }
        } catch (\Throwable $e) {
            $this->error('Import failed: '.$e->getMessage());
            $this->line('Tip: save the JSON as database/data/wards.json and run: php artisan bikego:import-wards --file=database/data/wards.json');

            return self::FAILURE;
        }

        $this->info("Imported {$count} wards.");

        return self::SUCCESS;
    }
}
