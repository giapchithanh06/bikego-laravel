{{-- Success / error messages stored in the session by the controllers --}}
@if (session('success'))
  <div class="alert alert-success" role="status"><i class="bi bi-check-circle-fill"></i> {{ session('success') }}</div>
@endif
@if (session('error'))
  <div class="alert alert-error" role="alert"><i class="bi bi-exclamation-triangle-fill"></i> {{ session('error') }}</div>
@endif
