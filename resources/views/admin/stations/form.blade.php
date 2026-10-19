@extends('layouts.admin')

@php $editing = $station->exists; @endphp

@section('content')
  <section class="section">
    <h2 class="section-title">
      <i class="bi bi-geo-alt"></i> {{ $editing ? 'Sửa trạm xe '.$station->name : 'Thêm trạm xe' }}
    </h2>

    <form class="form-card" action="{{ $editing ? route('admin.stations.update', $station) : route('admin.stations.store') }}" method="post" novalidate>
      @csrf
      @if ($editing)
        @method('PUT')
      @endif

      <div class="field">
        <label for="name">Tên trạm xe</label>
        <input id="name" name="name" type="text" maxlength="150" value="{{ old('name', $station->name) }}">
        <p class="field-error">{{ $errors->first('name') }}</p>
      </div>

      <div class="field">
        <label for="address">Địa chỉ</label>
        <input id="address" name="address" type="text" maxlength="255" value="{{ old('address', $station->address) }}">
        <p class="field-error">{{ $errors->first('address') }}</p>
      </div>

      <div class="field">
        <label for="ward_id">Phường</label>
        <select id="ward_id" name="ward_id">
          <option value="">Chọn Phường</option>
          @foreach ($wards as $ward)
            <option value="{{ $ward->id }}" {{ (old('ward_id', $station->ward_id) == $ward->id) ? 'selected' : '' }}>{{ $ward->name }}</option>
          @endforeach
        </select>
        <p class="field-error">{{ $errors->first('ward_id') }}</p>
      </div>

      <div class="field">
        <label for="total_docks">Tổng số chỗ</label>
        <input id="total_docks" name="total_docks" type="number" min="1" max="500" value="{{ old('total_docks', $station->total_docks) }}">
        <p class="field-error">{{ $errors->first('total_docks') }}</p>
      </div>

      <div class="field">
        <label for="status">Tình trạng</label>
        <select id="status" name="status">
          @foreach (\App\Models\Station::statusLabels() as $value => $label)
            <option value="{{ $value }}" {{ (old('status', $station->status) === $value) ? 'selected' : '' }}>{{ $label }}</option>
          @endforeach
        </select>
        <p class="field-error">{{ $errors->first('status') }}</p>
      </div>

      <div class="form-actions">
        <a class="btn btn-light" href="{{ route('admin.stations.index') }}">Quay lại</a>
        <button class="btn" type="submit">{{ $editing ? 'Cập nhật' : 'Thêm trạm xe' }}</button>
      </div>
    </form>
  </section>
@endsection
