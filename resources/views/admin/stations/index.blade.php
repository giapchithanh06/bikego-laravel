@extends('layouts.admin')

@section('content')
  <section class="section">
    <h2 class="section-title"><i class="bi bi-geo-alt"></i> Quản lý trạm xe</h2>

    <div class="toolbar">
      <a class="btn" href="{{ route('admin.stations.create') }}"><i class="bi bi-plus-circle"></i> Thêm trạm xe</a>
      <a class="btn btn-light" href="{{ route('admin.stations.bin') }}"><i class="bi bi-trash3"></i> Thùng rác</a>
    </div>

    <div class="table-wrap">
      <table class="data-table">
        <thead>
          <tr>
            <th>Tên trạm xe</th>
            <th>Địa chỉ</th>
            <th>Số chỗ trống / Tổng số chỗ</th>
            <th>Số xe hiện có</th>
            <th>Tình trạng</th>
            <th>Điểm đánh giá</th>
            <th>Hành động</th>
          </tr>
        </thead>
        <tbody>
          @forelse ($stations as $station)
            @php
              $empty = $station->emptyDocks();
              $emptyGood = $station->total_docks > 0 && $empty * 2 >= $station->total_docks;
            @endphp
            <tr>
              <td data-label="Tên trạm xe"><a href="{{ route('admin.stations.show', $station) }}"><i class="bi bi-geo-alt"></i> {{ $station->name }}</a></td>
              <td data-label="Địa chỉ">{{ $station->address }}</td>
              <td data-label="Số chỗ trống">
                <span class="chip {{ $emptyGood ? 'chip-good' : 'chip-bad' }}"><i class="bi bi-p-square"></i> {{ $empty }} / {{ $station->total_docks }}</span>
              </td>
              <td data-label="Số xe hiện có"><i class="bi bi-bicycle"></i> {{ $station->bikes_present }}</td>
              <td data-label="Tình trạng">
                @if ($station->isActive())
                  <span class="badge badge-ok"><i class="bi bi-check-circle-fill"></i> {{ $station->statusLabel() }}</span>
                @else
                  <span class="badge badge-bad"><i class="bi bi-cone-striped"></i> {{ $station->statusLabel() }}</span>
                @endif
              </td>
              <td data-label="Điểm đánh giá">
                @if ($station->reviews_count)
                  @include('partials.stars', ['value' => $station->reviews_avg_rating])
                  {{ number_format($station->reviews_avg_rating, 1) }}
                @else
                  <span class="muted">Chưa có</span>
                @endif
              </td>
              <td data-label="Hành động" class="actions">
                <a class="btn btn-small" href="{{ route('admin.stations.edit', $station) }}"><i class="bi bi-pencil-square"></i> Sửa</a>
                <form action="{{ route('admin.stations.destroy', $station) }}" method="post" data-confirm="Bạn có muốn xóa trạm xe hay không?">
                  @csrf
                  @method('DELETE')
                  <button class="btn btn-small btn-danger" type="submit"><i class="bi bi-trash3"></i> Xóa</button>
                </form>
              </td>
            </tr>
          @empty
            <tr><td colspan="7" class="empty-cell">Chưa có trạm xe nào.</td></tr>
          @endforelse
        </tbody>
      </table>
    </div>

    {{ $stations->links('partials.pagination') }}
  </section>
@endsection
