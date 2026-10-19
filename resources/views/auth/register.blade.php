@extends('layouts.app')

@section('content')
  <section class="section">
    <h2 class="section-title"><i class="bi bi-person-plus-fill"></i><span class="scramble-text">Đăng ký</span></h2>

    {{-- novalidate: validation is done by assets/js/register.js (client) and RegisterRequest (server) --}}
    <form class="form-card" id="register-form" action="{{ route('register.submit') }}" method="post" novalidate>
      @csrf

      <div class="field">
        <label for="name">Họ tên</label>
        <input id="name" name="name" type="text" value="{{ old('name') }}" autocomplete="name" data-field="name">
        <p class="field-error" data-error-for="name">{{ $errors->first('name') }}</p>
      </div>

      <div class="field">
        <label for="phone">Số điện thoại</label>
        <input id="phone" name="phone" type="text" inputmode="tel" value="{{ old('phone') }}" autocomplete="tel" data-field="phone" placeholder="0912345678">
        <p class="field-error" data-error-for="phone">{{ $errors->first('phone') }}</p>
      </div>

      <div class="field">
        <label for="email">Email</label>
        <input id="email" name="email" type="text" inputmode="email" value="{{ old('email') }}" autocomplete="email" data-field="email" placeholder="ten@gmail.com">
        <p class="field-error" data-error-for="email">{{ $errors->first('email') }}</p>
      </div>

      <div class="field">
        <label for="password">Mật khẩu</label>
        <input id="password" name="password" type="password" autocomplete="new-password" data-field="password">
        <p class="field-error" data-error-for="password">{{ $errors->first('password') }}</p>
      </div>

      <div class="field">
        <label for="password_confirmation">Nhắc lại mật khẩu</label>
        <input id="password_confirmation" name="password_confirmation" type="password" autocomplete="new-password" data-field="password_confirmation">
        <p class="field-error" data-error-for="password_confirmation">{{ $errors->first('password_confirmation') }}</p>
      </div>

      <button class="btn btn-block" type="submit">Đăng ký</button>
      <p class="form-note">Đã có tài khoản? <a href="{{ route('login') }}">Đăng nhập</a></p>
    </form>
  </section>
@endsection

@push('scripts')
  <script src="{{ asset('assets/js/register.js') }}"></script>
@endpush
