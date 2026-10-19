{{-- Simple pagination: use with $paginator->links('partials.pagination') --}}
@if ($paginator->hasPages())
  <nav class="pager" role="navigation" aria-label="Phân trang">
    @if ($paginator->onFirstPage())
      <span class="page disabled"><i class="bi bi-chevron-left"></i></span>
    @else
      <a class="page" href="{{ $paginator->previousPageUrl() }}" rel="prev"><i class="bi bi-chevron-left"></i></a>
    @endif

    @foreach ($elements as $element)
      @if (is_string($element))
        <span class="page disabled">{{ $element }}</span>
      @endif

      @if (is_array($element))
        @foreach ($element as $page => $url)
          @if ($page == $paginator->currentPage())
            <span class="page active" aria-current="page">{{ $page }}</span>
          @else
            <a class="page" href="{{ $url }}">{{ $page }}</a>
          @endif
        @endforeach
      @endif
    @endforeach

    @if ($paginator->hasMorePages())
      <a class="page" href="{{ $paginator->nextPageUrl() }}" rel="next"><i class="bi bi-chevron-right"></i></a>
    @else
      <span class="page disabled"><i class="bi bi-chevron-right"></i></span>
    @endif
  </nav>
@endif
