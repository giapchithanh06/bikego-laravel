@extends('layouts.app')

@section('content')
  <section class="section">
    <h2 class="section-title"><i class="bi bi-clock-history"></i><span class="scramble-text">Lịch sử thuê xe</span></h2>

    @if ($rentals->isEmpty())
      <p class="empty-note">Bạn chưa có lịch sử thuê xe.</p>
    @else
      <div class="table-wrap">
        <table class="data-table">
          <thead>
            <tr>
              <th>Bắt đầu - Trả xe</th>
              <th>Biển số xe</th>
              <th>Trạm lấy xe - Trạm trả xe</th>
              <th>Số phút thuê</th>
              <th>Số tiền thuê</th>
            </tr>
          </thead>
          <tbody>
            @foreach ($rentals as $rental)
              <tr>
                <td data-label="Thời gian">
                  <i class="bi bi-play-circle"></i> {{ $rental->started_at->format('H:i d/m/Y') }}<br>
                  @if ($rental->ended_at)
                    <i class="bi bi-stop-circle"></i> {{ $rental->ended_at->format('H:i d/m/Y') }}
                  @else
                    <span class="badge badge-warn"><i class="bi bi-person-walking"></i> Đang thuê</span>
                  @endif
                </td>
                <td data-label="Biển số xe"><i class="bi bi-bicycle"></i> {{ $rental->bike_code }}</td>
                <td data-label="Trạm">
                  <i class="bi bi-geo-alt"></i> {{ $rental->start_station_name }}<br>
                  <i class="bi bi-flag"></i> {{ $rental->end_station_name ?? '—' }}
                </td>
                <td data-label="Số phút">{{ $rental->ended_at ? $rental->total_minutes : $rental->elapsedMinutes() }} phút</td>
                <td data-label="Số tiền">{{ number_format($rental->currentAmount(), 0, ',', '.') }} đ</td>
              </tr>
            @endforeach
          </tbody>
        </table>
      </div>

      {{ $rentals->links('partials.pagination') }}
    @endif
  </section>
@endsection
