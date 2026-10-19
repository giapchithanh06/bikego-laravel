<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Bicycle;
use App\Models\Station;
use App\Models\Ward;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

/** Admin: station management (/admin/stations) including the trash (soft delete). */
class StationController extends Controller
{
    /** GET /admin/stations : 10 rows per page. */
    public function index(): View
    {
        $stations = Station::withStats()->orderBy('id')->paginate(10);

        return view('admin.stations.index', ['stations' => $stations]);
    }

    /** GET /admin/stations/{id} : all information and all reviews. */
    public function show(Station $station): View
    {
        $station = Station::withStats()->with('ward')->findOrFail($station->id);
        $reviews = $station->reviews()->with('user')->latest('id')->get();

        return view('admin.stations.show', ['station' => $station, 'reviews' => $reviews]);
    }

    public function create(): View
    {
        $station = new Station(['status' => Station::STATUS_ACTIVE]);

        return view('admin.stations.form', ['station' => $station, 'wards' => Ward::orderBy('name')->get()]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validator = $this->validator($request, null);

        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }

        Station::create($validator->validated());

        return redirect()->route('admin.stations.index')->with('success', 'Thêm trạm xe thành công');
    }

    public function edit(Station $station): View
    {
        return view('admin.stations.form', ['station' => $station, 'wards' => Ward::orderBy('name')->get()]);
    }

    public function update(Request $request, Station $station): RedirectResponse
    {
        $validator = $this->validator($request, $station);

        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput()->with('error', 'Cập nhật thất bại');
        }

        $station->update($validator->validated());

        return redirect()->route('admin.stations.index')->with('success', 'Cập nhật thành công');
    }

    /** Soft delete: move the station to the trash. */
    public function destroy(Station $station): RedirectResponse
    {
        $station->delete();

        return redirect()->route('admin.stations.index')->with('success', 'Đã chuyển trạm xe vào thùng rác');
    }

    /** GET /admin/stations/bin */
    public function bin(): View
    {
        $stations = Station::onlyTrashed()->orderByDesc('deleted_at')->paginate(10);

        return view('admin.stations.bin', ['stations' => $stations]);
    }

    public function restore(int $id): RedirectResponse
    {
        Station::onlyTrashed()->findOrFail($id)->restore();

        return redirect()->route('admin.stations.index')->with('success', 'Khôi phục thành công');
    }

    public function forceDelete(int $id): RedirectResponse
    {
        $station = Station::onlyTrashed()->findOrFail($id);

        if ($this->hasBikes($station)) {
            return redirect()->route('admin.stations.bin')
                ->with('error', 'Trạm còn xe (kể cả xe trong thùng rác) nên không thể xóa vĩnh viễn.');
        }

        $station->forceDelete();

        return redirect()->route('admin.stations.bin')->with('success', 'Xóa thành công');
    }

    public function restoreSelected(Request $request): RedirectResponse
    {
        $ids = $this->selectedIds($request);

        if (! $ids) {
            return redirect()->route('admin.stations.bin')->with('error', 'Vui lòng chọn ít nhất một trạm xe.');
        }

        Station::onlyTrashed()->whereIn('id', $ids)->restore();

        return redirect()->route('admin.stations.index')->with('success', 'Khôi phục thành công');
    }

    public function deleteSelected(Request $request): RedirectResponse
    {
        $ids = $this->selectedIds($request);

        if (! $ids) {
            return redirect()->route('admin.stations.bin')->with('error', 'Vui lòng chọn ít nhất một trạm xe.');
        }

        $skipped = $this->forceDeleteMany(Station::onlyTrashed()->whereIn('id', $ids)->get());

        return redirect()->route('admin.stations.bin')->with(...$this->deleteFlash($skipped));
    }

    public function restoreAll(): RedirectResponse
    {
        Station::onlyTrashed()->restore();

        return redirect()->route('admin.stations.index')->with('success', 'Khôi phục thành công');
    }

    public function deleteAll(): RedirectResponse
    {
        $skipped = $this->forceDeleteMany(Station::onlyTrashed()->get());

        return redirect()->route('admin.stations.index')->with(...$this->deleteFlash($skipped));
    }

    /** A station that still owns bikes can not be permanently deleted. */
    private function hasBikes(Station $station): bool
    {
        return Bicycle::withTrashed()->where('station_id', $station->id)->exists();
    }

    /** Permanently delete stations that have no bikes. Returns how many were skipped. */
    private function forceDeleteMany($stations): int
    {
        $skipped = 0;

        foreach ($stations as $station) {
            if ($this->hasBikes($station)) {
                $skipped++;
            } else {
                $station->forceDelete();
            }
        }

        return $skipped;
    }

    /** @return array{0: string, 1: string} flash key + message */
    private function deleteFlash(int $skipped): array
    {
        return $skipped > 0
            ? ['error', "Có {$skipped} trạm còn xe nên chưa thể xóa vĩnh viễn."]
            : ['success', 'Xóa thành công'];
    }

    /** @return array<int, int> */
    private function selectedIds(Request $request): array
    {
        return array_values(array_filter(array_map('intval', (array) $request->input('ids', []))));
    }

    /** Validation shared by "create" and "update". */
    private function validator(Request $request, ?Station $station)
    {
        $validator = Validator::make($request->all(), [
            'name' => ['required', 'string', 'max:150'],
            'address' => ['required', 'string', 'max:255'],
            'ward_id' => ['required', 'integer', 'exists:wards,id'],
            'total_docks' => ['required', 'integer', 'min:1', 'max:500'],
            'status' => ['required', Rule::in(array_keys(Station::statusLabels()))],
        ], [
            'name.required' => 'Vui lòng nhập tên trạm xe.',
            'address.required' => 'Vui lòng nhập địa chỉ.',
            'ward_id.required' => 'Vui lòng chọn phường.',
            'ward_id.exists' => 'Phường không hợp lệ.',
            'total_docks.required' => 'Vui lòng nhập tổng số chỗ.',
            'total_docks.integer' => 'Tổng số chỗ phải là số nguyên.',
            'total_docks.min' => 'Tổng số chỗ phải lớn hơn 0.',
            'total_docks.max' => 'Tổng số chỗ tối đa là 500.',
            'status.required' => 'Vui lòng chọn tình trạng.',
            'status.in' => 'Tình trạng không hợp lệ.',
        ]);

        // The docks can not be fewer than the bikes currently parked in the station
        $validator->after(function ($validator) use ($request, $station) {
            if (! $station || $validator->errors()->has('total_docks')) {
                return;
            }

            $present = $station->bicycles()->where('location_status', Bicycle::LOCATION_AT_STATION)->count();

            if ((int) $request->input('total_docks') < $present) {
                $validator->errors()->add('total_docks', "Trạm đang có {$present} xe nên tổng số chỗ không được nhỏ hơn {$present}.");
            }
        });

        return $validator;
    }
}
