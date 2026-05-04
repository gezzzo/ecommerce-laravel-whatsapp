{{-- resources/views/partials/pagination.blade.php --}}
@if ($paginator->hasPages())
<nav class="flex items-center gap-2">
    {{-- Previous --}}
    @if ($paginator->onFirstPage())
        <span class="w-9 h-9 flex items-center justify-center rounded-xl border border-gray-100 text-gray-300 text-sm">←</span>
    @else
        <a href="{{ $paginator->previousPageUrl() }}" class="w-9 h-9 flex items-center justify-center rounded-xl border border-gray-200 text-gray-600 hover:border-primary-400 hover:text-primary-600 text-sm">←</a>
    @endif

    {{-- Pages --}}
    @foreach ($elements as $element)
        @if (is_string($element))
            <span class="w-9 h-9 flex items-center justify-center text-gray-400 text-sm">...</span>
        @endif
        @if (is_array($element))
            @foreach ($element as $page => $url)
                @if ($page == $paginator->currentPage())
                    <span class="w-9 h-9 flex items-center justify-center rounded-xl bg-primary-600 text-white text-sm font-medium">{{ $page }}</span>
                @else
                    <a href="{{ $url }}" class="w-9 h-9 flex items-center justify-center rounded-xl border border-gray-200 text-gray-600 hover:border-primary-400 hover:text-primary-600 text-sm">{{ $page }}</a>
                @endif
            @endforeach
        @endif
    @endforeach

    {{-- Next --}}
    @if ($paginator->hasMorePages())
        <a href="{{ $paginator->nextPageUrl() }}" class="w-9 h-9 flex items-center justify-center rounded-xl border border-gray-200 text-gray-600 hover:border-primary-400 hover:text-primary-600 text-sm">→</a>
    @else
        <span class="w-9 h-9 flex items-center justify-center rounded-xl border border-gray-100 text-gray-300 text-sm">→</span>
    @endif
</nav>
@endif
