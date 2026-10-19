<!DOCTYPE html>
<html lang="vi">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <meta name="csrf-token" content="{{ csrf_token() }}">
  <title>{{ config('bikego.exam_title') }}</title>
  <link rel="stylesheet" href="{{ asset('vendor/bootstrap-icons/bootstrap-icons.min.css') }}">
  <link rel="stylesheet" href="{{ asset('assets/css/style.css') }}">
  <link rel="stylesheet" href="{{ asset('assets/css/app.css') }}">
</head>
<body class="admin-body" data-base-url="{{ url('/') }}">

  {{-- ===== Admin header + navigation ===== --}}
  <header class="admin-header">
    <div class="container">
      <a class="logo" href="{{ route('admin.dashboard') }}"><img src="{{ asset('assets/img/logo.svg') }}" alt="BikeGo"> <span class="admin-tag">Admin</span></a>
      @if (auth()->check() && auth()->user()->isAdmin())
        <nav class="admin-nav" aria-label="Admin navigation">
          <a href="{{ route('admin.bikes.index') }}" {!! (request()->routeIs('admin.bikes.*')) ? 'class="active"' : '' !!}><i class="bi bi-bicycle"></i> Quản lý xe</a>
          <a href="{{ route('admin.stations.index') }}" {!! (request()->routeIs('admin.stations.*')) ? 'class="active"' : '' !!}><i class="bi bi-geo-alt"></i> Quản lý trạm xe</a>
          <a href="{{ route('home') }}"><i class="bi bi-house"></i> Trang chủ</a>
          <a href="{{ route('logout') }}"><i class="bi bi-box-arrow-right"></i> Đăng xuất</a>
        </nav>
      @endif
    </div>
  </header>

  <main class="page-main">
    <div class="container">
      @include('partials.flash')
      @yield('content')
    </div>
  </main>

  @include('partials.confirm')

  <script src="{{ asset('assets/js/app.js') }}"></script>
  @stack('scripts')
</body>
</html>
