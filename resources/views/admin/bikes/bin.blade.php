@extends('layouts.admin')

@section('content')
  <section class="section">
    <h2 class="section-title"><i class="bi bi-trash3"></i> Thùng rác - Xe</h2>

    {{-- One form for the whole page: every button chooses its own target with formaction --}}
    <form id="bin-form" action="{{ route('admin.bikes.bin') }}" method="post">
      @csrf

      <div class="toolbar">
        <button class="btn" type="submit" formaction="{{ route('admin.bikes.restore-selected') }}"><i class="bi bi-arrow-counterclockwise"></i> Khôi phục xe được chọn</button>
        <button class="btn btn-danger" type="submit" formaction="{{ route('admin.bikes.delete-selected') }}" data-confirm="Bạn có chắc chắn muốn xóa vĩnh viễn các xe đã chọn? Hành động này không thể hoàn tác."><i class="bi bi-x-octagon"></i> Xóa xe được chọn</button>
        <button class="btn" type="submit" formaction="{{ route('admin.bikes.restore-all') }}" data-confirm="Khôi phục tất cả xe trong thùng rác?"><i class="bi bi-arrow-repeat"></i> Khôi phục tất cả</button>
        <button class="btn btn-danger" type="submit" formaction="{{ route('admin.bikes.delete-all') }}" data-confirm="Bạn có chắc chắn muốn xóa vĩnh viễn TẤT CẢ xe trong thùng rác? Hành động này không thể hoàn tác."><i class="bi bi-trash3-fill"></i> Xóa tất cả</button>
      </div>

      <div class="table-wrap">
        <table class="data-table">
          <thead>
            <tr>
              <th class="col-check"><input type="checkbox" id="check-all" aria-label="Chọn tất cả"></th>
              <th>Biển số</th>
              <th>Tình trạng</th>
              <th>Hành động</th>
            </tr>
          </thead>
          <tbody>
            @forelse ($bikes as $bike)
              <tr>
                <td class="col-check"><input type="checkbox" class="row-check" name="ids[]" value="{{ $bike->id }}" aria-label="Chọn xe {{ $bike->bike_code }}"></td>
                <td data-label="Biển số"><i class="bi bi-bicycle"></i> {{ $bike->bike_code }}</td>
                <td data-label="Tình trạng">
                  @if ($bike->condition_status === \App\Models\Bicycle::CONDITION_NORMAL)
                    <span class="badge badge-ok"><i class="bi bi-check-circle-fill"></i> {{ $bike->conditionLabel() }}</span>
                  @else
                    <span class="badge badge-bad"><i class="bi bi-tools"></i> {{ $bike->conditionLabel() }}</span>
                  @endif
                </td>
                <td data-label="Hành động" class="actions">
                  <button class="btn btn-small" type="submit" formaction="{{ route('admin.bikes.restore', $bike->id) }}"><i class="bi bi-arrow-counterclockwise"></i> Khôi phục</button>
                  <button class="btn btn-small btn-danger" type="submit" formaction="{{ route('admin.bikes.force-delete', $bike->id) }}" data-confirm="Xóa vĩnh viễn xe {{ $bike->bike_code }}? Hành động này không thể hoàn tác."><i class="bi bi-x-octagon"></i> Xóa hẳn</button>
                </td>
              </tr>
            @empty
              <tr><td colspan="4" class="empty-cell">Thùng rác trống.</td></tr>
            @endforelse
          </tbody>
        </table>
      </div>
    </form>

    {{ $bikes->links('partials.pagination') }}

    <div class="toolbar">
      <a class="btn btn-light" href="{{ route('admin.bikes.index') }}"><i class="bi bi-arrow-left"></i> Quay lại quản lý xe</a>
    </div>
  </section>
@endsection
