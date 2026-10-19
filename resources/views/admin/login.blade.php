@extends('layouts.admin')

@section('content')
  <section class="section">
    <h2 class="section-title"><i class="bi bi-shield-lock-fill"></i> Đăng nhập quản trị</h2>

    <form class="form-card" action="{{ route('admin.login.submit') }}" method="post" novalidate>
      @csrf

      <div class="field">
        <label for="username">Tên đăng nhập</label>
        <input id="username" name="username" type="text" value="{{ old('username') }}" autocomplete="username" autofocus>
        <p class="field-error">{{ $errors->first('username') }}</p>
      </div>

      <div class="field">
        <label for="password">Mật khẩu</label>
        <input id="password" name="password" type="password" autocomplete="current-password">
        <p class="field-error">{{ $errors->first('password') }}</p>
      </div>

      <button class="btn btn-block" type="submit">Đăng nhập</button>
    </form>
  </section>
@endsection
