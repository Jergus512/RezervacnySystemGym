@if ($paginator->hasPages())
    <nav aria-label="Stránkovanie" class="d-flex justify-content-center">
        <ul class="pagination mb-0">
            {{-- Previous Page Link (text only, no arrows) --}}
            @if ($paginator->onFirstPage())
                <li class="page-item disabled" aria-disabled="true" aria-label="Predchádzajúca">
                    <span class="page-link">Predchádzajúca</span>
                </li>
            @else
                <li class="page-item">
                    <a class="page-link" href="{{ $paginator->previousPageUrl() }}" rel="prev" aria-label="Predchádzajúca">Predchádzajúca</a>
                </li>
            @endif

            {{-- Pagination Elements --}}
            @foreach ($elements as $element)
                {{-- "Three Dots" Separator --}}
                @if (is_string($element))
                    <li class="page-item disabled" aria-disabled="true"><span class="page-link">{{ $element }}</span></li>
                @endif

                {{-- Array Of Links --}}
                @if (is_array($element))
                    @foreach ($element as $page => $url)
                        @if ($page == $paginator->currentPage())
                            <li class="page-item active" aria-current="page"><span class="page-link">{{ $page }}</span></li>
                        @else
                            <li class="page-item"><a class="page-link" href="{{ $url }}">{{ $page }}</a></li>
                        @endif
                    @endforeach
                @endif
            @endforeach

            {{-- Next Page Link (text only, no arrows) --}}
            @if ($paginator->hasMorePages())
                <li class="page-item">
                    <a class="page-link" href="{{ $paginator->nextPageUrl() }}" rel="next" aria-label="Ďalšia">Ďalšia</a>
                </li>
            @else
                <li class="page-item disabled" aria-disabled="true" aria-label="Ďalšia">
                    <span class="page-link">Ďalšia</span>
                </li>
            @endif
        </ul>
    </nav>
@endif
