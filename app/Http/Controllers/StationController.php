<?php

namespace App\Http\Controllers;

use App\Models\Station;
use App\Models\StationReview;
use App\Models\Ward;
use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * Public station pages.
 * index()  -> /stations page
 * the other methods are JSON endpoints used by AJAX (no page reload).
 */
class StationController extends Controller
{
    private const REVIEWS_PER_PAGE = 5;

    public function index(): View
    {
        return view('stations', [
            'wards' => Ward::orderBy('name')->get(),
        ]);
    }

    /** GET /api/stations?ward_id=1 : stations of a ward. */
    public function list(Request $request): JsonResponse
    {
        $request->validate(['ward_id' => ['required', 'integer']]);

        $stations = Station::withStats()
            ->where('ward_id', (int) $request->input('ward_id'))
            ->orderBy('name')
            ->get()
            ->map(fn (Station $s) => $this->summary($s))
            ->values();

        return response()->json(['data' => $stations]);
    }

    /** GET /api/stations/{station} : detail + the 5 newest reviews. */
    public function show(Station $station): JsonResponse
    {
        $station = Station::withStats()->with('ward')->findOrFail($station->id);
        $page = $this->reviewPage($station->id, 0);

        return response()->json(['data' => $this->summary($station) + [
            'address' => $station->address,
            'ward' => optional($station->ward)->name,
            'reviews' => $page['items'],
            'has_more' => $page['has_more'],
        ]]);
    }

    /** GET /api/stations/{station}/reviews?offset=5 : next 5 older reviews ("Tải thêm"). */
    public function reviews(Request $request, Station $station): JsonResponse
    {
        $offset = max(0, (int) $request->input('offset'));
        $page = $this->reviewPage($station->id, $offset);

        return response()->json(['data' => $page['items'], 'has_more' => $page['has_more']]);
    }

    /**
     * GET /api/wards/{ward}/stations?purpose=rent|return
     * Options for the dropdown lists of the rent / return pages:
     *  - rent  : only active stations that still have a rentable bike
     *  - return: only active stations that still have a free dock
     */
    public function options(Request $request, Ward $ward): JsonResponse
    {
        $purpose = $request->query('purpose') === 'return' ? 'return' : 'rent';

        $stations = Station::withStats()
            ->where('ward_id', $ward->id)
            ->where('status', Station::STATUS_ACTIVE)
            ->orderBy('name')
            ->get()
            ->filter(fn (Station $s) => $purpose === 'rent' ? $s->bikes_rentable > 0 : $s->emptyDocks() > 0)
            ->map(fn (Station $s) => [
                'id' => $s->id,
                'name' => $s->name,
                'label' => $purpose === 'rent'
                    ? "{$s->name} ({$s->bikes_rentable} xe)"
                    : "{$s->name} ({$s->emptyDocks()} chỗ trống)",
            ])
            ->values();

        return response()->json(['data' => $stations]);
    }

    /** Short representation of a station used by the JSON endpoints. */
    private function summary(Station $s): array
    {
        return [
            'id' => $s->id,
            'name' => $s->name,
            'status' => $s->status,
            'status_label' => $s->statusLabel(),
            'total_docks' => (int) $s->total_docks,
            'bikes_present' => (int) $s->bikes_present,
            'empty_docks' => $s->emptyDocks(),
            'avg_rating' => $s->reviews_avg_rating !== null ? round((float) $s->reviews_avg_rating, 1) : null,
            'reviews_count' => (int) $s->reviews_count,
        ];
    }

    /** @return array{items: array<int, array<string, mixed>>, has_more: bool} */
    private function reviewPage(int $stationId, int $offset): array
    {
        $rows = StationReview::with('user')
            ->where('station_id', $stationId)
            ->latest('id')
            ->skip($offset)
            ->take(self::REVIEWS_PER_PAGE + 1)
            ->get();

        return [
            'items' => $rows->take(self::REVIEWS_PER_PAGE)->map(fn (StationReview $r) => [
                'user' => optional($r->user)->name ?? 'Ẩn danh',
                'rating' => (int) $r->rating,
                'comment' => $r->comment,
                'created_at' => optional($r->created_at)->format('d/m/Y H:i'),
            ])->values()->all(),
            'has_more' => $rows->count() > self::REVIEWS_PER_PAGE,
        ];
    }
}
