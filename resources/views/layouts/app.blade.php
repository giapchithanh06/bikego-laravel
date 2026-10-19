<!DOCTYPE html>
<html lang="vi">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <meta name="csrf-token" content="{{ csrf_token() }}">
  {{-- Same title on every page: "Full name – Candidate number" --}}
  <title>{{ config('bikego.exam_title') }}</title>

  {{-- Icon font (local copy) + shared styles + dynamic components --}}
  <link rel="stylesheet" href="{{ asset('vendor/bootstrap-icons/bootstrap-icons.min.css') }}">
  <link rel="stylesheet" href="{{ asset('assets/css/style.css') }}">
  <link rel="stylesheet" href="{{ asset('assets/css/app.css') }}">
</head>
<body data-base-url="{{ url('/') }}">

  {{-- ===== Header: logo ===== --}}
  <header class="site-header">
    <div class="container">
      <a class="logo" href="{{ route('home') }}"><img src="{{ asset('assets/img/logo.svg') }}" alt="BikeGo"></a>
    </div>
  </header>

  {{-- ===== Navigation + banner (pure CSS animation) ===== --}}
  <section class="hero" aria-label="Banner">
    <div class="container">
      <nav class="main-nav" aria-label="Main navigation">
        <ul>
          <li><a href="{{ route('home') }}" {!! (request()->routeIs('home')) ? 'class="active"' : '' !!}>Home</a></li>
          <li><a href="{{ route('stations') }}" {!! (request()->routeIs('stations')) ? 'class="active"' : '' !!}>Trạm xe</a></li>
          @auth
            {{-- Logged in: the user's name opens the rental history --}}
            <li><a href="{{ route('rental.history') }}" {!! (request()->routeIs('rental.history')) ? 'class="active"' : '' !!}>{{ auth()->user()->name }}</a></li>
            <li><a href="{{ route('logout') }}">Đăng xuất</a></li>
          @else
            <li><a href="{{ route('login') }}" {!! (request()->routeIs('login')) ? 'class="active"' : '' !!}>Đăng nhập</a></li>
            <li><a href="{{ route('register') }}" {!! (request()->routeIs('register')) ? 'class="active"' : '' !!}>Đăng ký</a></li>
          @endauth
        </ul>
      </nav>
      <div class="hero-actions">
        <a class="hero-btn" href="{{ route('rental.rent') }}">Thuê xe</a>
        <a class="hero-btn" href="{{ route('rental.return') }}">Trả xe</a>
      </div>
    </div>
  </section>

  {{-- ===== Page content ===== --}}
  <main class="page-main">
    <div class="container">
      @include('partials.flash')
      @yield('content')
    </div>
  </main>

  {{-- ===== Footer ===== --}}
  <footer class="site-footer">
    <div class="container">
      <img src="{{ asset('assets/img/logo-thanhdoan.svg') }}" alt="Thành Đoàn TP.HCM">
      <div>
        <p>&copy; 2026 Thành Đoàn TP.HCM. Bản quyền thuộc về Thành Đoàn TP.HCM.</p>
        <p>Địa chỉ: Số 1 Phạm Ngọc Thạch, Quận 1, TP HCM</p>
        <p>Email: <a href="mailto:thanhdoan@tphcm.gov.vn">thanhdoan@tphcm.gov.vn</a></p>
      </div>
    </div>
  </footer>

  @include('partials.confirm')

  <script src="{{ asset('assets/js/effects.js') }}"></script>
  <script src="{{ asset('assets/js/app.js') }}"></script>
  @stack('scripts')
</body>
</html>
