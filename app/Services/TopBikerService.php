<?php

namespace App\Services;

use App\Models\MonthlyTopBiker;
use App\Models\Rental;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

/**
 * Builds the "Top Bikers" ranking of one month and stores it in monthly_top_bikers.
 *
 * Ranking rules (from the exam):
 *  1. more total rented minutes first
 *  2. then more rentals
 *  3. then the account created earlier
 *  4. then random
 */
class TopBikerService
{
    public const LIMIT = 10;

    /** Month before the current one, format YYYY-MM. */
    public static function previousMonth(?Carbon $now = null): string
    {
        return ($now ?? now())->copy()->startOfMonth()->subMonth()->format('Y-m');
    }

    /**
     * Compute and store the ranking of $month (YYYY-MM). Returns the number of stored rows.
     */
    public function summarize(string $month): int
    {
        $start = Carbon::createFromFormat('Y-m-d', $month.'-01')->startOfMonth();
        $end = $start->copy()->endOfMonth();

        $rows = Rental::query()
            ->whereNotNull('ended_at')
            ->whereBetween('ended_at', [$start, $end])
            ->selectRaw('user_id, SUM(total_minutes) AS total_minutes, COUNT(*) AS total_rentals')
            ->groupBy('user_id')
            ->get();

        $users = User::whereIn('id', $rows->pluck('user_id'))->get()->keyBy('id');

        $ranking = $rows->map(fn ($row) => [
            'user_id' => $row->user_id,
            'total_minutes' => (int) $row->total_minutes,
            'total_rentals' => (int) $row->total_rentals,
            'created' => optional(optional($users[$row->user_id] ?? null)->created_at)->getTimestamp() ?? PHP_INT_MAX,
            'random' => mt_rand(),
        ])->all();

        usort($ranking, fn ($a, $b) => [$b['total_minutes'], $b['total_rentals'], $a['created'], $a['random']]
            <=> [$a['total_minutes'], $a['total_rentals'], $b['created'], $b['random']]);

        $ranking = array_slice($ranking, 0, self::LIMIT);

        DB::transaction(function () use ($month, $ranking) {
            MonthlyTopBiker::where('month', $month)->delete();

            foreach ($ranking as $index => $item) {
                MonthlyTopBiker::create([
                    'month' => $month,
                    'rank' => $index + 1,
                    'user_id' => $item['user_id'],
                    'total_minutes' => $item['total_minutes'],
                    'total_rentals' => $item['total_rentals'],
                ]);
            }
        });

        return count($ranking);
    }
}
