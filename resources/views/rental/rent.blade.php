@extends('layouts.app')

@section('content')
  <section class="section">
    <h2 class="section-title"><i class="bi bi-unlock-fill"></i><span class="scramble-text">Thuê xe</span></h2>

    @if ($rental)
      {{-- One bike at a time: show the rental in progress instead of the form --}}
      <div class="form-card">
        <p class="alert alert-info"><i class="bi bi-info-circle-fill"></i> Bạn đang thuê một xe và chưa trả.</p>
        <p><strong>Thông tin xe đang thuê:</strong></p>
        @include('rental._active', ['rental' => $rental])
        <a class="btn btn-block" href="{{ route('rental.return') }}">Đi đến trang trả xe</a>
      </div>
    @else
      <form class="form-card" action="{{ route('rental.rent.store') }}" method="post" novalidate>
        @csrf

        <div class="field">
          <label for="ward">Phường</label>
          <select id="ward" name="ward_id" data-purpose="rent" data-target="#station" data-old-station="{{ old('station_id') }}">
            <option value="">Chọn Phường</option>
            @foreach ($wards as $ward)
              <option value="{{ $ward->id }}" {{ (old('ward_id') == $ward->id) ? 'selected' : '' }}>{{ $ward->name }}</option>
            @endforeach
          </select>
          <p class="field-error">{{ $errors->first('ward_id') }}</p>
        </div>

        <div class="field">
          <label for="station">Trạm</label>
          <select id="station" name="station_id">
            <option value="">Chọn Trạm</option>
          </select>
          <p class="field-error">{{ $errors->first('station_id') }}</p>
        </div>

        <div class="field">
          <label for="bike_code">Biển số xe</label>
          <input id="bike_code" name="bike_code" type="text" value="{{ old('bike_code') }}" placeholder="Nhập biển số..." autocomplete="off">
          <p class="field-error">{{ $errors->first('bike_code') }}</p>
        </div>

        <button class="btn btn-block" type="submit">Thuê</button>
      </form>
    @endif
  </section>
@endsection

@push('scripts')
  <script src="{{ asset('assets/js/rental.js') }}"></script>
@endpush
