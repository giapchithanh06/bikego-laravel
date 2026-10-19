@extends('layouts.app')

@section('content')

  {{-- ===== Top Bikers (read from the monthly summary table) ===== --}}
  <section class="section" id="top-bikers">
    <h2 class="section-title"><i class="bi bi-trophy-fill"></i><span class="scramble-text">Top Bikers</span></h2>
    <p class="section-sub">Người dùng nổi bật tháng {{ substr($month, 5, 2) }}/{{ substr($month, 0, 4) }}</p>

    @if ($topBikers->isEmpty())
      <p class="empty-note">Chưa có dữ liệu của tháng này.</p>
    @else
      {{-- Podium order on screen: 2nd - 1st - 3rd --}}
      <div class="podium">
        @foreach ([2, 1, 3] as $rank)
          @php $biker = $topBikers->firstWhere('rank', $rank); @endphp
          @if ($biker)
            <article class="podium-item rank-{{ $rank }}">
              <span class="medal">{{ $rank }}</span>
              <h3>{{ $biker->user->name }}</h3>
              <p class="meta"><i class="bi bi-telephone"></i> {{ $biker->user->maskedPhone() }}</p>
              <p class="figures">
                <span><i class="bi bi-clock-history"></i> {{ $biker->durationText() }}</span>
                <span><i class="bi bi-bicycle"></i> {{ $biker->total_rentals }} lượt</span>
              </p>
            </article>
          @endif
        @endforeach
      </div>

      {{-- Rank 4 - 10 --}}
      <ul class="rank-list">
        @foreach ($topBikers->where('rank', '>', 3) as $biker)
          <li class="rank-row">
            <span class="rank-no">Top {{ $biker->rank }}</span>
            <span class="col-name">{{ $biker->user->name }}</span>
            <span class="col-phone">{{ $biker->user->maskedPhone() }}</span>
            <span class="col-time"><i class="bi bi-clock-history"></i> {{ $biker->durationText() }}</span>
            <span class="col-count"><i class="bi bi-bicycle"></i> {{ $biker->total_rentals }} lượt</span>
          </li>
        @endforeach
      </ul>
    @endif
  </section>

  {{-- ===== Let's BikeGo: infrastructure ===== --}}
  <section class="section" id="lets-bikego">
    <h2 class="section-title"><i class="bi bi-geo-alt-fill"></i><span class="scramble-text">Let's BikeGo</span></h2>

    <div class="stat-grid">
      <div class="stat-card">
        <p class="label">Số xe hiện có:</p>
        <p class="value">{{ number_format($totalBikes, 0, ',', '.') }} xe</p>
      </div>
      <div class="stat-card">
        <p class="label">Số trạm hiện có:</p>
        <p class="value">{{ number_format($totalStations, 0, ',', '.') }} trạm</p>
      </div>
    </div>

    <h3 class="section-title"><i class="bi bi-stars"></i><span class="scramble-text">Top Stations</span></h3>

    @if ($topStations->isEmpty())
      <p class="empty-note">Chưa có đánh giá nào về các trạm xe.</p>
    @else
      {{-- Auto-running carousel, 4 stations per view (see assets/js/home.js) --}}
      <div class="carousel" id="station-carousel">
        <button class="carousel-arrow" type="button" data-dir="-1" aria-label="Previous"><i class="bi bi-chevron-left"></i></button>
        <div class="carousel-viewport">
          <div class="carousel-track">
            @foreach ($topStations as $station)
              <div class="carousel-item">
                <article class="station-card">
                  <button type="button" class="station-link" data-station-id="{{ $station->id }}">{{ $station->name }}</button>
                  <p class="rating-line">
                    @include('partials.stars', ['value' => $station->reviews_avg_rating])
                    <strong>{{ number_format($station->reviews_avg_rating, 1) }}</strong> / 5
                  </p>
                  @foreach ($station->latest_reviews as $review)
                    <p class="quote">“{{ $review->comment }}” <small>— {{ optional($review->user)->name }}</small></p>
                  @endforeach
                </article>
              </div>
            @endforeach
          </div>
        </div>
        <button class="carousel-arrow" type="button" data-dir="1" aria-label="Next"><i class="bi bi-chevron-right"></i></button>
      </div>
    @endif
  </section>

  {{-- Station detail (same content as the accordion on /stations) --}}
  <dialog id="station-dialog" class="modal">
    <button type="button" class="modal-close" id="station-dialog-close" aria-label="Close"><i class="bi bi-x-lg"></i></button>
    <div id="station-dialog-body"></div>
  </dialog>
@endsection

@push('scripts')
  <script src="{{ asset('assets/js/station-detail.js') }}"></script>
  <script src="{{ asset('assets/js/home.js') }}"></script>
@endpush
