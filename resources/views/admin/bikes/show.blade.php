@extends('layouts.admin')

@section('content')
  <section class="section">
    <h2 class="section-title"><i class="bi bi-bicycle"></i> Xe {{ $bike->bike_code }}</h2>

    <div class="detail-card">
      <ul class="info-list">
        <li><i class="bi bi-hash"></i> Mã số xe (biển số): <strong>{{ $bike->bike_code }}</strong></li>
        <li><i class="bi bi-geo-alt"></i> Vị trí hiện tại:
          @if ($bike->isRented())
            <span class="badge badge-warn"><i class="bi bi-person-walking"></i> Đang thuê</span>
          @else
            <strong>{{ optional($bike->station)->name }}</strong>
          @endif
        </li>
        <li><i class="bi bi-wrench-adjustable"></i> Tình trạng hoạt động:
          @if ($bike->condition_status === \App\Models\Bicycle::CONDITION_NORMAL)
            <span class="badge badge-ok"><i class="bi bi-check-circle-fill"></i> {{ $bike->conditionLabel() }}</span>
          @else
            <span class="badge badge-bad"><i class="bi bi-tools"></i> {{ $bike->conditionLabel() }}</span>
          @endif
        </li>
        <li><i class="bi bi-cash-coin"></i> Đơn giá thuê: <strong>{{ number_format($bike->price_per_minute, 0, ',', '.') }} đ/phút</strong></li>
        <li><i class="bi bi-star"></i> Điểm đánh giá trung bình:
          @if ($reviews->isNotEmpty())
            @include('partials.stars', ['value' => $bike->reviews_avg_rating]) <strong>{{ number_format($bike->reviews_avg_rating, 1) }}</strong> / 5 ({{ $reviews->count() }} đánh giá)
          @else
            <span class="muted">Chưa có đánh giá</span>
          @endif
        </li>
        <li><i class="bi bi-calendar-plus"></i> Ngày tạo: {{ optional($bike->created_at)->format('H:i d/m/Y') }}</li>
      </ul>
    </div>

    <h3 class="sub-title"><i class="bi bi-chat-left-text"></i> Tất cả đánh giá</h3>
    @include('admin._reviews', ['reviews' => $reviews])

    <div class="toolbar">
      <a class="btn btn-light" href="{{ route('admin.bikes.index') }}"><i class="bi bi-arrow-left"></i> Quay lại</a>
      <a class="btn" href="{{ route('admin.bikes.edit', $bike) }}"><i class="bi bi-pencil-square"></i> Sửa</a>
    </div>
  </section>
@endsection
