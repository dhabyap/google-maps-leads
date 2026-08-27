@if ($paginator->hasPages())
<nav class="pagination" aria-label="Pagination">
  @if ($paginator->onFirstPage())
    <span class="disabled">&laquo; Prev</span>
  @else
    <a href="{{ $paginator->previousPageUrl() }}">&laquo; Prev</a>
  @endif

  @for ($i = 1; $i <= $paginator->lastPage(); $i++)
    @if ($i == $paginator->currentPage())
      <span class="active">{{ $i }}</span>
    @else
      <a href="{{ $paginator->url($i) }}">{{ $i }}</a>
    @endif
  @endfor

  @if ($paginator->hasMorePages())
    <a href="{{ $paginator->nextPageUrl() }}">Next &raquo;</a>
  @else
    <span class="disabled">Next &raquo;</span>
  @endif
</nav>
@endif
