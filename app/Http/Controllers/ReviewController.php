<?php

namespace App\Http\Controllers;

use App\Models\BicycleReview;
use App\Models\Rental;
use App\Models\StationReview;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

/** Rating (1-5) + comment (max 200 chars) of the bike and the station after a return. */
class ReviewController extends Controller
{
    /** GET /rentals/{rental}/review */
    public function create(Request $request, Rental $rental)
    {
        $this->authorizeRental($request, $rental);

        if (BicycleReview::where('rental_id', $rental->id)->exists()
            || StationReview::where('rental_id', $rental->id)->exists()) {
            return redirect()->route('home')->with('success', 'Bạn đã đánh giá chuyến đi này rồi.');
        }

        return view('rental.review', ['rental' => $rental]);
    }

    /** POST /rentals/{rental}/review */
    public function store(Request $request, Rental $rental): RedirectResponse
    {
        $this->authorizeRental($request, $rental);

        $data = $request->validate([
            'bike_rating' => ['required', 'integer', 'between:1,5'],
            'bike_comment' => ['nullable', 'string', 'max:200'],
            'station_rating' => ['required', 'integer', 'between:1,5'],
            'station_comment' => ['nullable', 'string', 'max:200'],
        ], [
            'bike_rating.required' => 'Vui lòng chọn điểm đánh giá xe (1-5).',
            'bike_rating.between' => 'Điểm đánh giá phải từ 1 đến 5.',
            'station_rating.required' => 'Vui lòng chọn điểm đánh giá trạm (1-5).',
            'station_rating.between' => 'Điểm đánh giá phải từ 1 đến 5.',
            'bike_comment.max' => 'Bình luận tối đa 200 ký tự.',
            'station_comment.max' => 'Bình luận tối đa 200 ký tự.',
        ]);

        if ($rental->bicycle_id) {
            BicycleReview::updateOrCreate(
                ['rental_id' => $rental->id],
                [
                    'user_id' => $request->user()->id,
                    'bicycle_id' => $rental->bicycle_id,
                    'rating' => $data['bike_rating'],
                    'comment' => $data['bike_comment'] ?? null,
                ]
            );
        }

        if ($rental->end_station_id) {
            StationReview::updateOrCreate(
                ['rental_id' => $rental->id],
                [
                    'user_id' => $request->user()->id,
                    'station_id' => $rental->end_station_id,
                    'rating' => $data['station_rating'],
                    'comment' => $data['station_comment'] ?? null,
                ]
            );
        }

        return redirect()->route('home')->with('success', 'Cảm ơn bạn đã đánh giá!');
    }

    /** Only the owner of a finished rental may review it. */
    private function authorizeRental(Request $request, Rental $rental): void
    {
        abort_if((int) $rental->user_id !== (int) $request->user()->id, 403);
        abort_if($rental->isActive(), 404);
    }
}
