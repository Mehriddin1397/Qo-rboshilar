@props(['paginator'])

@if ($paginator->hasPages())
    <div {{ $attributes->class(['mt-4 flex items-center justify-between border-t border-sand pt-4 text-sm text-brown-700']) }}>
        <p>
            Jami <span class="font-medium text-brown-900">{{ $paginator->total() }}</span> ta —
            {{ $paginator->firstItem() }}–{{ $paginator->lastItem() }} ko'rsatilmoqda
        </p>

        <div class="flex items-center gap-2">
            @if ($paginator->onFirstPage())
                <span class="rounded-md border border-sand px-3 py-1.5 text-brown-500/50">Oldingi</span>
            @else
                <a href="{{ $paginator->previousPageUrl() }}" class="rounded-md border border-sand px-3 py-1.5 hover:bg-paper-dark">Oldingi</a>
            @endif

            <span class="text-brown-900">{{ $paginator->currentPage() }} / {{ $paginator->lastPage() }}</span>

            @if ($paginator->hasMorePages())
                <a href="{{ $paginator->nextPageUrl() }}" class="rounded-md border border-sand px-3 py-1.5 hover:bg-paper-dark">Keyingi</a>
            @else
                <span class="rounded-md border border-sand px-3 py-1.5 text-brown-500/50">Keyingi</span>
            @endif
        </div>
    </div>
@endif
