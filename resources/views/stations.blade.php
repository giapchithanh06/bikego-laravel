@extends('layouts.app')

@section('content')
  <section class="section">
    <h2 class="section-title"><i class="bi bi-geo-alt-fill"></i><span class="scramble-text">Trạm xe</span></h2>

    {{-- Ward dropdown (loaded from the database) --}}
    <div class="form-card">
      <div class="field">
        <label for="ward">Phường</label>
        <select id="ward">
          <option value="">Chọn Phường</option>
          @foreach ($wards as $ward)
            <option value="{{ $ward->id }}">{{ $ward->name }}</option>
          @endforeach
        </select>
      </div>
    </div>

    {{-- Stations of the chosen ward are rendered here by AJAX (assets/js/stations.js) --}}
    <div id="station-list" class="accordion" aria-live="polite">
      <p class="empty-note">Hãy chọn phường để xem danh sách trạm xe.</p>
    </div>
  </section>
@endsection

@push('scripts')
  <script src="{{ asset('assets/js/station-detail.js') }}"></script>
  <script src="{{ asset('assets/js/stations.js') }}"></script>
@endpush
