{{-- Renders a rating (0-5) with Bootstrap Icons. Usage: @include('partials.stars', ['value' => 4.5]) --}}
@php $stars = (float) ($value ?? 0); @endphp
<span class="stars" title="{{ number_format($stars, 1) }}/5">
  @for ($i = 1; $i <= 5; $i++)
    @if ($stars >= $i)
      <i class="bi bi-star-fill"></i>
    @elseif ($stars >= $i - 0.5)
      <i class="bi bi-star-half"></i>
    @else
      <i class="bi bi-star"></i>
    @endif
  @endfor
</span>
