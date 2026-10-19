<?php

namespace App\Http\Controllers;

use App\Models\Bicycle;
use App\Models\Rental;
use App\Models\Station;
use App\Models\Ward;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

/** Rent a bike, return a bike and the rental history of the logged-in user. */
class RentalController extends Controller
{
    /** GET /bike-rental */
    public function rentForm(Request $request): View
    {
        return view('rental.rent', [
            'rental' => $request->user()->activeRental(),
            'wards' => Ward::orderBy('name')->get(),
        ]);
    }

    /** POST /bike-rental */
    public function rent(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'ward_id' => ['required', 'integer'],
            'station_id' => ['required', 'integer'],
            'bike_code' => ['required', 'string', 'max:20'],
        ], [
            'ward_id.required' => 'Vui lòng chọn phường.',
            'station_id.required' => 'Vui lòng chọn trạm.',
            'bike_code.required' => 'Vui lòng nhập biển số xe.',
        ]);

        $user = $request->user();

        if ($user->activeRental()) {
            return redirect()->route('rental.rent')
                ->with('error', 'Bạn đang thuê một xe. Hãy trả xe trước khi thuê xe mới.');
        }

        $errors = DB::transaction(function () use ($data, $user) {
            $station = Station::where('id', $data['station_id'])
                ->where('status', Station::STATUS_ACTIVE)
                ->lockForUpdate()
                ->first();

            if (! $station) {
                return ['station_id' => 'Trạm không tồn tại hoặc đang bảo trì.'];
            }

            $bike = Bicycle::whereRaw('LOWER(bike_code) = ?', [mb_strtolower(trim($data['bike_code']))])
                ->lockForUpdate()
                ->first();

            if (! $bike
                || (int) $bike->station_id !== (int) $station->id
                || $bike->location_status !== Bicycle::LOCATION_AT_STATION) {
                return ['bike_code' => 'Không tìm thấy xe có biển số này tại trạm đã chọn.'];
            }

            if ($bike->condition_status !== Bicycle::CONDITION_NORMAL) {
                return ['bike_code' => 'Xe này đang sửa chữa, vui lòng chọn xe khác.'];
            }

            Rental::create([
                'user_id' => $user->id,
                'bicycle_id' => $bike->id,
                'bike_code' => $bike->bike_code,
                'start_station_id' => $station->id,
                'start_station_name' => $station->name,
                'started_at' => now(),
                'price_per_minute' => $bike->price_per_minute,
            ]);

            // While rented, the bike is not located in any station
            $bike->update([
                'location_status' => Bicycle::LOCATION_RENTED,
                'station_id' => null,
            ]);

            return null;
        });

        if ($errors) {
            return back()->withErrors($errors)->withInput();
        }

        return redirect()->route('rental.rent')->with('success', 'Thuê xe thành công. Chúc bạn đi vui vẻ!');
    }

    /** GET /bike-return */
    public function returnForm(Request $request): View
    {
        return view('rental.return', [
            'rental' => $request->user()->activeRental(),
            'wards' => Ward::orderBy('name')->get(),
        ]);
    }

    /** POST /bike-return */
    public function returnBike(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'ward_id' => ['required', 'integer'],
            'station_id' => ['required', 'integer'],
        ], [
            'ward_id.required' => 'Vui lòng chọn phường.',
            'station_id.required' => 'Vui lòng chọn trạm trả xe.',
        ]);

        $user = $request->user();
        $rental = $user->activeRental();

        if (! $rental) {
            return redirect()->route('rental.return')->with('error', 'Bạn chưa thuê xe nào.');
        }

        $error = DB::transaction(function () use ($data, $rental) {
            $station = Station::withStats()
                ->where('id', $data['station_id'])
                ->where('status', Station::STATUS_ACTIVE)
                ->first();

            if (! $station) {
                return ['station_id' => 'Trạm không tồn tại hoặc đang bảo trì.'];
            }

            if ($station->emptyDocks() <= 0) {
                return ['station_id' => 'Trạm này đã hết chỗ trống, vui lòng chọn trạm khác.'];
            }

            $end = now();
            $minutes = Rental::minutesBetween($rental->started_at, $end);

            $rental->update([
                'end_station_id' => $station->id,
                'end_station_name' => $station->name,
                'ended_at' => $end,
                'total_minutes' => $minutes,
                'total_amount' => $minutes * (int) $rental->price_per_minute,
            ]);

            // The bike is automatically "locked" and parked in the return station
            Bicycle::withTrashed()->where('id', $rental->bicycle_id)->update([
                'location_status' => Bicycle::LOCATION_AT_STATION,
                'station_id' => $station->id,
            ]);

            return null;
        });

        if ($error) {
            return back()->withErrors($error)->withInput();
        }

        // After a successful return, the user rates the bike and the station
        return redirect()->route('rental.review', $rental);
    }

    /** GET /rental-history : newest rentals first. */
    public function history(Request $request): View
    {
        $rentals = $request->user()->rentals()
            ->orderByDesc('started_at')
            ->orderByDesc('id')
            ->paginate(10);

        return view('rental.history', ['rentals' => $rentals]);
    }
}
