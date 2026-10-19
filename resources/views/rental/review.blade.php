@extends('layouts.app')

@section('content')
  <section class="section">
    <h2 class="section-title"><i class="bi bi-chat-heart-fill"></i><span class="scramble-text">Đánh giá chuyến đi</span></h2>

    <div class="form-card">
      <p class="alert alert-success"><i class="bi bi-check-circle-fill"></i> Trả xe thành công!
        Thời gian thuê <strong>{{ $rental->total_minutes }} phút</strong>,
        số tiền <strong>{{ number_format($rental->total_amount, 0, ',', '.') }} đ</strong>.</p>

      <form action="{{ route('rental.review.store', $rental) }}" method="post" novalidate>
        @csrf

        {{-- Bike rating --}}
        <fieldset class="rating-block">
          <legend><i class="bi bi-bicycle"></i> Xe {{ $rental->bike_code }}</legend>
          <div class="star-input">
            @for ($i = 5; $i >= 1; $i--)
              <input type="radio" id="bike_rating_{{ $i }}" name="bike_rating" value="{{ $i }}" {{ (old('bike_rating') == $i) ? 'checked' : '' }}>
              <label for="bike_rating_{{ $i }}" title="{{ $i }} sao"><i class="bi bi-star-fill"></i></label>
            @endfor
          </div>
          <p class="field-error">{{ $errors->first('bike_rating') }}</p>
          <div class="field">
            <label for="bike_comment">Bình luận (tối đa 200 ký tự)</label>
            <textarea id="bike_comment" name="bike_comment" rows="3" maxlength="200" data-counter>{{ old('bike_comment') }}</textarea>
            <p class="field-error">{{ $errors->first('bike_comment') }}</p>
          </div>
        </fieldset>

        {{-- Station rating --}}
        <fieldset class="rating-block">
          <legend><i class="bi bi-geo-alt"></i> Trạm {{ $rental->end_station_name }}</legend>
          <div class="star-input">
            @for ($i = 5; $i >= 1; $i--)
              <input type="radio" id="station_rating_{{ $i }}" name="station_rating" value="{{ $i }}" {{ (old('station_rating') == $i) ? 'checked' : '' }}>
              <label for="station_rating_{{ $i }}" title="{{ $i }} sao"><i class="bi bi-star-fill"></i></label>
            @endfor
          </div>
          <p class="field-error">{{ $errors->first('station_rating') }}</p>
          <div class="field">
            <label for="station_comment">Bình luận (tối đa 200 ký tự)</label>
            <textarea id="station_comment" name="station_comment" rows="3" maxlength="200" data-counter>{{ old('station_comment') }}</textarea>
            <p class="field-error">{{ $errors->first('station_comment') }}</p>
          </div>
        </fieldset>

        <button class="btn btn-block" type="submit">Gửi đánh giá</button>
      </form>
    </div>
  </section>
@endsection
