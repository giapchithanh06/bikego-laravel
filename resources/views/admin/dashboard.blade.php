@extends('layouts.admin')

@section('content')
  <section class="section">
    <h2 class="section-title"><i class="bi bi-speedometer2"></i> Trang quản trị</h2>

    <div class="stat-grid">
      <a class="stat-card admin-card" href="{{ route('admin.bikes.index') }}">
        <p class="label"><i class="bi bi-bicycle"></i> Quản lý xe</p>
        <p class="value">{{ $bikeCount }} xe</p>
      </a>
      <a class="stat-card admin-card" href="{{ route('admin.stations.index') }}">
        <p class="label"><i class="bi bi-geo-alt"></i> Quản lý trạm xe</p>
        <p class="value">{{ $stationCount }} trạm</p>
      </a>
    </div>
  </section>
@endsection
