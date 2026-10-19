@extends('layouts.admin')

@php $editing = $bike->exists; @endphp

@section('content')
  <section class="section">
    <h2 class="section-title">
      <i class="bi bi-bicycle"></i> {{ $editing ? 'Sửa xe '.$bike->bike_code : 'Thêm xe' }}
    </h2>

    <form class="form-card" action="{{ $editing ? route('admin.bikes.update', $bike) : route('admin.bikes.store') }}" method="post" novalidate>
      @csrf
      @if ($editing)
        @method('PUT')
      @endif

      <div class="field">
        <label for="bike_code">Mã số xe (biển số)</label>
        <input id="bike_code" name="bike_code" type="text" maxlength="20" value="{{ old('bike_code', $bike->bike_code) }}">
        <p class="field-error">{{ $errors->first('bike_code') }}</p>
      </div>

      <div class="field">
        <label for="station_id">Vị trí (trạm xe)</label>
        @if ($bike->isRented())
          <input type="text" value="Đang thuê" disabled>
        @else
          <select id="station_id" name="station_id">
            <option value="">Chọn trạm xe</option>
            @foreach ($stations as $station)
              <option value="{{ $station->id }}" {{ (old('station_id', $bike->station_id) == $station->id) ? 'selected' : '' }}>{{ $station->name }}</option>
            @endforeach
          </select>
        @endif
        <p class="field-error">{{ $errors->first('station_id') }}</p>
      </div>

      <div class="field">
        <label for="condition_status">Tình trạng hoạt động</label>
        <select id="condition_status" name="condition_status">
          @foreach (\App\Models\Bicycle::conditionLabels() as $value => $label)
            <option value="{{ $value }}" {{ (old('condition_status', $bike->condition_status) === $value) ? 'selected' : '' }}>{{ $label }}</option>
          @endforeach
        </select>
        <p class="field-error">{{ $errors->first('condition_status') }}</p>
      </div>

      <div class="field">
        <label for="price_per_minute">Đơn giá thuê (đồng/phút)</label>
        <input id="price_per_minute" name="price_per_minute" type="number" min="0" value="{{ old('price_per_minute', $bike->price_per_minute) }}">
        <p class="field-error">{{ $errors->first('price_per_minute') }}</p>
      </div>

      <div class="form-actions">
        <a class="btn btn-light" href="{{ route('admin.bikes.index') }}">Quay lại</a>
        <button class="btn" type="submit">{{ $editing ? 'Cập nhật' : 'Thêm xe' }}</button>
      </div>
    </form>
  </section>
@endsection
