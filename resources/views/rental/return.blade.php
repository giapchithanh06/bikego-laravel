@extends('layouts.app')

@section('content')
  <section class="section">
    <h2 class="section-title"><i class="bi bi-lock-fill"></i><span class="scramble-text">Trả xe</span></h2>

    @if (! $rental)
      <div class="form-card">
        <p class="alert alert-info"><i class="bi bi-info-circle-fill"></i> Bạn chưa thuê xe nào.</p>
        <a class="btn btn-block" href="{{ route('rental.rent') }}">Đi đến trang thuê xe</a>
      </div>
    @else
      <form class="form-card" action="{{ route('rental.return.store') }}" method="post" novalidate>
        @csrf
        <p><strong>Thông tin xe đang thuê:</strong></p>
        @include('rental._active', ['rental' => $rental])

        <p><strong>Chọn trạm trả xe:</strong></p>

        <div class="field">
          <label for="ward">Phường</label>
          <select id="ward" name="ward_id" data-purpose="return" data-target="#station" data-old-station="{{ old('station_id') }}">
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

        <button class="btn btn-block" type="submit">Trả xe và thanh toán</button>
      </form>
    @endif
  </section>
@endsection

@push('scripts')
  <script src="{{ asset('assets/js/rental.js') }}"></script>
@endpush
