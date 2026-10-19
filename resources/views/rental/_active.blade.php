{{-- Information of the rental in progress (rent + return pages). Minutes / amount are refreshed by rental.js --}}
<ul class="info-list" id="active-rental"
    data-start="{{ $rental->started_at->getTimestamp() * 1000 }}"
    data-now="{{ now()->getTimestamp() * 1000 }}"
    data-price="{{ $rental->price_per_minute }}">
  <li><i class="bi bi-clock"></i> Thời gian bắt đầu thuê: <strong>{{ $rental->started_at->format('H:i d/m/Y') }}</strong></li>
  <li><i class="bi bi-bicycle"></i> Biển số xe: <strong>{{ $rental->bike_code }}</strong></li>
  <li><i class="bi bi-geo-alt"></i> Tên trạm lấy xe: <strong>{{ $rental->start_station_name }}</strong></li>
  <li><i class="bi bi-hourglass-split"></i> Số phút thuê tính tới hiện tại: <strong data-role="minutes">{{ $rental->elapsedMinutes() }}</strong> phút</li>
  <li><i class="bi bi-cash-coin"></i> Số tiền thuê: <strong data-role="amount">{{ number_format($rental->currentAmount(), 0, ',', '.') }}</strong> đ
    <small>(đơn giá {{ number_format($rental->price_per_minute, 0, ',', '.') }} đ/phút)</small></li>
</ul>
