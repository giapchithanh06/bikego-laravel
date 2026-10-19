@extends('layouts.app')

@section('content')
  <section class="section">
    <h2 class="section-title"><i class="bi bi-box-arrow-in-right"></i><span class="scramble-text">Đăng nhập</span></h2>

    <form class="form-card" action="{{ route('login.submit') }}" method="post" novalidate>
      @csrf

      <div class="field">
        <label for="identifier">Số điện thoại hoặc email</label>
        <input id="identifier" name="identifier" type="text" value="{{ old('identifier') }}" autocomplete="username" autofocus>
        <p class="field-error">{{ $errors->first('identifier') }}</p>
      </div>

      <div class="field">
        <label for="password">Mật khẩu</label>
        <input id="password" name="password" type="password" autocomplete="current-password">
        <p class="field-error">{{ $errors->first('password') }}</p>
      </div>

      <button class="btn btn-block" type="submit">Đăng nhập</button>
      <p class="form-note">Chưa có tài khoản? <a href="{{ route('register') }}">Đăng ký</a></p>
    </form>
  </section>
@endsection
