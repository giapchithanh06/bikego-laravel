<?php

namespace App\Console\Commands;

use App\Services\TopBikerService;
use Illuminate\Console\Command;

/**
 * Stores the Top Bikers ranking of a month in the database.
 * Scheduled for 00:00 on the first day of every month (see app/Console/Kernel.php).
 */
class SummarizeMonthlyTopBikers extends Command
{
    protected $signature = 'bikego:summarize-monthly {--month= : Month to summarise as YYYY-MM (default: previous month)}';

    protected $description = 'Summarise the top 10 bikers of a month and store the result';

    public function handle(TopBikerService $service): int
    {
        $month = $this->option('month') ?: TopBikerService::previousMonth();

        if (! preg_match('/^\d{4}-(0[1-9]|1[0-2])$/', $month)) {
            $this->error('Invalid month, expected YYYY-MM.');

            return self::FAILURE;
        }

        $count = $service->summarize($month);
        $this->info("Top bikers of {$month} saved ({$count} rows).");

        return self::SUCCESS;
    }
}
