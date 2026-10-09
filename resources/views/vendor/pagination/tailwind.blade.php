@if ($paginator->hasPages())
    <nav role="navigation" class="paginasi" aria-label="Navigasi halaman">
        <span class="paginasi-info">
            Menampilkan {{ $paginator->firstItem() ?? 0 }}&ndash;{{ $paginator->lastItem() ?? 0 }}
            dari {{ $paginator->total() }} data
        </span>

        <span class="paginasi-tautan">
            @if ($paginator->onFirstPage())
                <span class="paginasi-aksi nonaktif">&laquo; Sebelumnya</span>
            @else
                <a class="paginasi-aksi" href="{{ $paginator->previousPageUrl() }}" rel="prev">&laquo; Sebelumnya</a>
            @endif

            @foreach ($elements as $element)
                @if (is_string($element))
                    <span class="paginasi-aksi nonaktif">{{ $element }}</span>
                @endif

                @if (is_array($element))
                    @foreach ($element as $page => $url)
                        @if ($page == $paginator->currentPage())
                            <span class="paginasi-aksi aktif" aria-current="page">{{ $page }}</span>
                        @else
                            <a class="paginasi-aksi" href="{{ $url }}">{{ $page }}</a>
                        @endif
                    @endforeach
                @endif
            @endforeach

            @if ($paginator->hasMorePages())
                <a class="paginasi-aksi" href="{{ $paginator->nextPageUrl() }}" rel="next">Berikutnya &raquo;</a>
            @else
                <span class="paginasi-aksi nonaktif">Berikutnya &raquo;</span>
            @endif
        </span>
    </nav>
@endif
