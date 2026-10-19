@extends('layouts.admin')

@section('content')
  <section class="section">
    <h2 class="section-title"><i class="bi bi-trash3"></i> Thùng rác - Trạm xe</h2>

    {{-- One form for the whole page: every button chooses its own target with formaction --}}
    <form id="bin-form" action="{{ route('admin.stations.bin') }}" method="post">
      @csrf

      <div class="toolbar">
        <button class="btn" type="submit" formaction="{{ route('admin.stations.restore-selected') }}"><i class="bi bi-arrow-counterclockwise"></i> Khôi phục trạm xe được chọn</button>
        <button class="btn btn-danger" type="submit" formaction="{{ route('admin.stations.delete-selected') }}" data-confirm="Bạn có chắc chắn muốn xóa vĩnh viễn các trạm xe đã chọn? Hành động này không thể hoàn tác."><i class="bi bi-x-octagon"></i> Xóa trạm xe được chọn</button>
        <button class="btn" type="submit" formaction="{{ route('admin.stations.restore-all') }}" data-confirm="Khôi phục tất cả trạm xe trong thùng rác?"><i class="bi bi-arrow-repeat"></i> Khôi phục tất cả</button>
        <button class="btn btn-danger" type="submit" formaction="{{ route('admin.stations.delete-all') }}" data-confirm="Bạn có chắc chắn muốn xóa vĩnh viễn TẤT CẢ trạm xe trong thùng rác? Hành động này không thể hoàn tác."><i class="bi bi-trash3-fill"></i> Xóa tất cả</button>
      </div>

      <div class="table-wrap">
        <table class="data-table">
          <thead>
            <tr>
              <th class="col-check"><input type="checkbox" id="check-all" aria-label="Chọn tất cả"></th>
              <th>Tên trạm xe</th>
              <th>Tình trạng</th>
              <th>Hành động</th>
            </tr>
          </thead>
          <tbody>
            @forelse ($stations as $station)
              <tr>
                <td class="col-check"><input type="checkbox" class="row-check" name="ids[]" value="{{ $station->id }}" aria-label="Chọn {{ $station->name }}"></td>
                <td data-label="Tên trạm xe"><i class="bi bi-geo-alt"></i> {{ $station->name }}</td>
                <td data-label="Tình trạng">
                  @if ($station->isActive())
                    <span class="badge badge-ok"><i class="bi bi-check-circle-fill"></i> {{ $station->statusLabel() }}</span>
                  @else
                    <span class="badge badge-bad"><i class="bi bi-cone-striped"></i> {{ $station->statusLabel() }}</span>
                  @endif
                </td>
                <td data-label="Hành động" class="actions">
                  <button class="btn btn-small" type="submit" formaction="{{ route('admin.stations.restore', $station->id) }}"><i class="bi bi-arrow-counterclockwise"></i> Khôi phục</button>
                  <button class="btn btn-small btn-danger" type="submit" formaction="{{ route('admin.stations.force-delete', $station->id) }}" data-confirm="Xóa vĩnh viễn {{ $station->name }}? Hành động này không thể hoàn tác."><i class="bi bi-x-octagon"></i> Xóa hẳn</button>
                </td>
              </tr>
            @empty
              <tr><td colspan="4" class="empty-cell">Thùng rác trống.</td></tr>
            @endforelse
          </tbody>
        </table>
      </div>
    </form>

    {{ $stations->links('partials.pagination') }}

    <div class="toolbar">
      <a class="btn btn-light" href="{{ route('admin.stations.index') }}"><i class="bi bi-arrow-left"></i> Quay lại quản lý trạm xe</a>
    </div>
  </section>
@endsection
