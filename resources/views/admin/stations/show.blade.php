@extends('layouts.admin')

@section('content')
  <section class="section">
    <h2 class="section-title"><i class="bi bi-geo-alt"></i> {{ $station->name }}</h2>

    @php
      $empty = $station->emptyDocks();
      $emptyGood = $station->total_docks > 0 && $empty * 2 >= $station->total_docks;
    @endphp

    <div class="detail-card">
      <ul class="info-list">
        <li><i class="bi bi-signpost-split"></i> Địa chỉ: <strong>{{ $station->address }}</strong></li>
        <li><i class="bi bi-map"></i> Phường: <strong>{{ optional($station->ward)->name }}</strong></li>
        <li><i class="bi bi-p-square"></i> Tổng số chỗ: <strong>{{ $station->total_docks }}</strong></li>
        <li><i class="bi bi-p-circle"></i> Số chỗ còn trống:
          <span class="chip {{ $emptyGood ? 'chip-good' : 'chip-bad' }}">{{ $empty }}</span></li>
        <li><i class="bi bi-bicycle"></i> Số xe hiện có: <strong>{{ $station->bikes_present }}</strong></li>
        <li><i class="bi bi-activity"></i> Tình trạng:
          @if ($station->isActive())
            <span class="badge badge-ok"><i class="bi bi-check-circle-fill"></i> {{ $station->statusLabel() }}</span>
          @else
            <span class="badge badge-bad"><i class="bi bi-cone-striped"></i> {{ $station->statusLabel() }}</span>
          @endif
        </li>
        <li><i class="bi bi-star"></i> Điểm đánh giá trung bình:
          @if ($reviews->isNotEmpty())
            @include('partials.stars', ['value' => $station->reviews_avg_rating]) <strong>{{ number_format($station->reviews_avg_rating, 1) }}</strong> / 5 ({{ $reviews->count() }} đánh giá)
          @else
            <span class="muted">Chưa có đánh giá</span>
          @endif
        </li>
      </ul>
    </div>

    <h3 class="sub-title"><i class="bi bi-chat-left-text"></i> Tất cả đánh giá</h3>
    @include('admin._reviews', ['reviews' => $reviews])

    <div class="toolbar">
      <a class="btn btn-light" href="{{ route('admin.stations.index') }}"><i class="bi bi-arrow-left"></i> Quay lại</a>
      <a class="btn" href="{{ route('admin.stations.edit', $station) }}"><i class="bi bi-pencil-square"></i> Sửa</a>
    </div>
  </section>
@endsection
