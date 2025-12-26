@if ($paginator->hasPages())
<div class="flex flex-row gap-3 border-t pt-4">

    {{-- Page Info --}}
    <div class="text-sm text-gray-600">
        Page {{ $paginator->currentPage() }} of {{ $paginator->lastPage() }}
    </div>

    {{-- Pagination Numbers --}}
    <div class="flex items-center gap-2 flex-wrap pagination">

        {{-- Previous --}}
        @if ($paginator->onFirstPage())
            <span class="px-3 py-1 text-gray-400">PREV</span>
        @else
            <button onclick="changePage({{ $paginator->currentPage() - 1 }})"
                class="px-3 py-1 text-blue-600 hover:underline">
                PREV
            </button>
        @endif

        {{-- Page Numbers --}}
        @for ($i = 1; $i <= $paginator->lastPage(); $i++)
            @if ($i == $paginator->currentPage())
                <span class="w-8 h-8 flex items-center justify-center rounded-full bg-blue-600 text-white">
                    {{ $i }}
                </span>
            @elseif ($i <= 10 || abs($i - $paginator->currentPage()) <= 2)
                <button onclick="changePage({{ $i }})"
                    class="w-8 h-8 flex items-center justify-center rounded-full border hover:bg-gray-100">
                    {{ $i }}
                </button>
            @endif
        @endfor

        {{-- Next --}}
        @if ($paginator->hasMorePages())
            <button onclick="changePage({{ $paginator->currentPage() + 1 }})"
                class="px-3 py-1 text-blue-600 hover:underline">
                NEXT
            </button>
        @else
            <span class="px-3 py-1 text-gray-400">NEXT</span>
        @endif

    </div>
</div>
{{-- </form> --}}
<script>
    document.addEventListener('DOMContentLoaded', () => {
        window.changePage = function(page) {
            const url = new URL(window.location.href);
            url.searchParams.set('page', page);
            window.location.href = url.toString();
        }
    });
    </script>    
@endif
