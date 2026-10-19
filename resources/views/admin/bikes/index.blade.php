@extends('layouts.admin')

@section('content')
  <section class="section">
    <h2 class="section-title"><i class="bi bi-bicycle"></i> Quản lý xe</h2>

    <div class="toolbar">
      <a class="btn" href="{{ route('admin.bikes.create') }}"><i class="bi bi-plus-circle"></i> Thêm xe</a>
      <a class="btn btn-light" href="{{ route('admin.bikes.bin') }}"><i class="bi bi-trash3"></i> Thùng rác</a>
    </div>

    <div class="table-wrap">
      <table class="data-table">
        <thead>
          <tr>
            <th>Biển số</th>
            <th>Vị trí hiện tại</th>
            <th>Tình trạng</th>
            <th>Điểm đánh giá</th>
            <th>Hành động</th>
          </tr>
        </thead>
        <tbody>
          @forelse ($bikes as $bike)
            <tr>
              <td data-label="Biển số"><a href="{{ route('admin.bikes.show', $bike) }}"><i class="bi bi-bicycle"></i> {{ $bike->bike_code }}</a></td>
              <td data-label="Vị trí hiện tại">
                @if ($bike->isRented())
                  <span class="badge badge-warn"><i class="bi bi-person-walking"></i> Đang thuê</span>
                @else
                  <i class="bi bi-geo-alt"></i> {{ optional($bike->station)->name }}
                @endif
              </td>
              <td data-label="Tình trạng">
                @if ($bike->condition_status === \App\Models\Bicycle::CONDITION_NORMAL)
                  <span class="badge badge-ok"><i class="bi bi-check-circle-fill"></i> {{ $bike->conditionLabel() }}</span>
                @else
                  <span class="badge badge-bad"><i class="bi bi-tools"></i> {{ $bike->conditionLabel() }}</span>
                @endif
              </td>
              <td data-label="Điểm đánh giá">
                @if ($bike->reviews_count)
                  @include('partials.stars', ['value' => $bike->reviews_avg_rating])
                  {{ number_format($bike->reviews_avg_rating, 1) }}
                @else
                  <span class="muted">Chưa có</span>
                @endif
              </td>
              <td data-label="Hành động" class="actions">
                <a class="btn btn-small" href="{{ route('admin.bikes.edit', $bike) }}"><i class="bi bi-pencil-square"></i> Sửa</a>
                <form action="{{ route('admin.bikes.destroy', $bike) }}" method="post" data-confirm="Bạn có muốn xóa xe hay không?">
                  @csrf
                  @method('DELETE')
                  <button class="btn btn-small btn-danger" type="submit"><i class="bi bi-trash3"></i> Xóa</button>
                </form>
              </td>
            </tr>
          @empty
            <tr><td colspan="5" class="empty-cell">Chưa có xe nào.</td></tr>
          @endforelse
        </tbody>
      </table>
    </div>

    {{ $bikes->links('partials.pagination') }}
  </section>
@endsection
