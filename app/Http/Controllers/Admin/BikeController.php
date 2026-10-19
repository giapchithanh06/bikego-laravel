<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Bicycle;
use App\Models\Station;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

/** Admin: bike management (/admin/bikes) including the trash (soft delete). */
class BikeController extends Controller
{
    /** GET /admin/bikes : 10 rows per page. */
    public function index(): View
    {
        $bikes = Bicycle::with('station')
            ->withAvg('reviews', 'rating')
            ->withCount('reviews')
            ->orderBy('id')
            ->paginate(10);

        return view('admin.bikes.index', ['bikes' => $bikes]);
    }

    /** GET /admin/bikes/{id} : all information and all reviews. */
    public function show(Bicycle $bicycle): View
    {
        $bicycle->load('station')->loadAvg('reviews', 'rating');
        $reviews = $bicycle->reviews()->with('user')->latest('id')->get();

        return view('admin.bikes.show', ['bike' => $bicycle, 'reviews' => $reviews]);
    }

    public function create(): View
    {
        $bike = new Bicycle([
            'condition_status' => Bicycle::CONDITION_NORMAL,
            'price_per_minute' => config('bikego.default_price_per_minute'),
        ]);

        return view('admin.bikes.form', ['bike' => $bike, 'stations' => $this->stationOptions()]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validator = $this->validator($request, null);

        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }

        $data = $validator->validated();
        Bicycle::create([
            'bike_code' => $data['bike_code'],
            'station_id' => $data['station_id'],
            'condition_status' => $data['condition_status'],
            'price_per_minute' => $data['price_per_minute'] ?? config('bikego.default_price_per_minute'),
            'location_status' => Bicycle::LOCATION_AT_STATION,
        ]);

        return redirect()->route('admin.bikes.index')->with('success', 'Thêm xe thành công');
    }

    public function edit(Bicycle $bicycle): View
    {
        return view('admin.bikes.form', ['bike' => $bicycle, 'stations' => $this->stationOptions()]);
    }

    public function update(Request $request, Bicycle $bicycle): RedirectResponse
    {
        $validator = $this->validator($request, $bicycle);

        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput()->with('error', 'Cập nhật thất bại');
        }

        $data = $validator->validated();
        $values = [
            'bike_code' => $data['bike_code'],
            'condition_status' => $data['condition_status'],
            'price_per_minute' => $data['price_per_minute'] ?? $bicycle->price_per_minute,
        ];

        // A rented bike is not in any station, so its location can not be edited
        if (! $bicycle->isRented()) {
            $values['station_id'] = $data['station_id'];
        }

        $bicycle->update($values);

        return redirect()->route('admin.bikes.index')->with('success', 'Cập nhật thành công');
    }

    /** Soft delete: move the bike to the trash. */
    public function destroy(Bicycle $bicycle): RedirectResponse
    {
        if ($bicycle->isRented()) {
            return back()->with('error', 'Xe đang được thuê, không thể xóa.');
        }

        $bicycle->delete();

        return redirect()->route('admin.bikes.index')->with('success', 'Đã chuyển xe vào thùng rác');
    }

    /** GET /admin/bikes/bin */
    public function bin(): View
    {
        $bikes = Bicycle::onlyTrashed()->with('station')->orderByDesc('deleted_at')->paginate(10);

        return view('admin.bikes.bin', ['bikes' => $bikes]);
    }

    public function restore(int $id): RedirectResponse
    {
        Bicycle::onlyTrashed()->findOrFail($id)->restore();

        return redirect()->route('admin.bikes.index')->with('success', 'Khôi phục thành công');
    }

    public function forceDelete(int $id): RedirectResponse
    {
        Bicycle::onlyTrashed()->findOrFail($id)->forceDelete();

        return redirect()->route('admin.bikes.bin')->with('success', 'Xóa thành công');
    }

    public function restoreSelected(Request $request): RedirectResponse
    {
        $ids = $this->selectedIds($request);

        if (! $ids) {
            return redirect()->route('admin.bikes.bin')->with('error', 'Vui lòng chọn ít nhất một xe.');
        }

        Bicycle::onlyTrashed()->whereIn('id', $ids)->restore();

        return redirect()->route('admin.bikes.index')->with('success', 'Khôi phục thành công');
    }

    public function deleteSelected(Request $request): RedirectResponse
    {
        $ids = $this->selectedIds($request);

        if (! $ids) {
            return redirect()->route('admin.bikes.bin')->with('error', 'Vui lòng chọn ít nhất một xe.');
        }

        Bicycle::onlyTrashed()->whereIn('id', $ids)->get()->each->forceDelete();

        return redirect()->route('admin.bikes.bin')->with('success', 'Xóa thành công');
    }

    public function restoreAll(): RedirectResponse
    {
        Bicycle::onlyTrashed()->restore();

        return redirect()->route('admin.bikes.index')->with('success', 'Khôi phục thành công');
    }

    public function deleteAll(): RedirectResponse
    {
        Bicycle::onlyTrashed()->get()->each->forceDelete();

        return redirect()->route('admin.bikes.index')->with('success', 'Xóa thành công');
    }

    /** Stations that can hold a bike: every station that is not in the trash. */
    private function stationOptions()
    {
        return Station::orderBy('name')->get();
    }

    /** @return array<int, int> */
    private function selectedIds(Request $request): array
    {
        return array_values(array_filter(array_map('intval', (array) $request->input('ids', []))));
    }

    /** Validation shared by "create" and "update". */
    private function validator(Request $request, ?Bicycle $bike)
    {
        $validator = Validator::make($request->all(), [
            'bike_code' => [
                'required', 'string', 'max:20',
                Rule::unique('bicycles', 'bike_code')->ignore(optional($bike)->id),
            ],
            'station_id' => [
                optional($bike)->isRented() ? 'nullable' : 'required',
                'integer',
                Rule::exists('stations', 'id')->whereNull('deleted_at'),
            ],
            'condition_status' => ['required', Rule::in(array_keys(Bicycle::conditionLabels()))],
            'price_per_minute' => ['nullable', 'integer', 'min:0', 'max:1000000'],
        ], [
            'bike_code.required' => 'Vui lòng nhập mã số xe (biển số).',
            'bike_code.unique' => 'Mã số xe này đã tồn tại.',
            'bike_code.max' => 'Mã số xe tối đa 20 ký tự.',
            'station_id.required' => 'Vui lòng chọn trạm xe.',
            'station_id.exists' => 'Trạm xe không hợp lệ.',
            'condition_status.required' => 'Vui lòng chọn tình trạng hoạt động.',
            'condition_status.in' => 'Tình trạng hoạt động không hợp lệ.',
            'price_per_minute.integer' => 'Đơn giá phải là số nguyên.',
        ]);

        // The chosen station must still have a free dock for this bike
        $validator->after(function ($validator) use ($request, $bike) {
            if ($validator->errors()->has('station_id') || ! $request->filled('station_id')) {
                return;
            }
            if (optional($bike)->isRented()) {
                return;
            }

            $stationId = (int) $request->input('station_id');
            $alreadyThere = $bike && (int) $bike->station_id === $stationId;

            if (! $alreadyThere && optional(Station::withStats()->find($stationId))->emptyDocks() <= 0) {
                $validator->errors()->add('station_id', 'Trạm này đã hết chỗ trống.');
            }
        });

        return $validator;
    }
}
