<?php

namespace App\Http\Controllers;

use App\Models\Bicycle;
use App\Models\MonthlyTopBiker;
use App\Models\Station;
use App\Services\TopBikerService;
use Illuminate\Contracts\View\View;

/** Home page: Top Bikers, infrastructure statistics and Top Stations. */
class HomeController extends Controller
{
    public function index(): View
    {
        $month = TopBikerService::previousMonth();

        // Ranking is read from the table filled by the monthly job (no live aggregation)
        $topBikers = MonthlyTopBiker::with('user')
            ->where('month', $month)
            ->orderBy('rank')
            ->get();

        // Top 10 stations by average rating, ties are broken randomly
        $topStations = Station::query()
            ->withAvg('reviews', 'rating')
            ->whereHas('reviews')
            ->orderByDesc('reviews_avg_rating')
            ->inRandomOrder()
            ->limit(10)
            ->get()
            ->each(function (Station $station) {
                // 3 newest comments of the station
                $station->latest_reviews = $station->reviews()
                    ->with('user')
                    ->whereNotNull('comment')
                    ->where('comment', '!=', '')
                    ->latest('id')
                    ->limit(3)
                    ->get();
            });

        return view('home', [
            'month' => $month,
            'topBikers' => $topBikers,
            'totalBikes' => Bicycle::count(),
            'totalStations' => Station::count(),
            'topStations' => $topStations,
        ]);
    }
}
