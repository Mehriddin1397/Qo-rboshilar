@props(['title', 'breadcrumbs' => []])

<div class="flex flex-col gap-2 border-b border-sand pb-4 sm:flex-row sm:items-end sm:justify-between">
    <div>
        <x-ui.breadcrumb :items="$breadcrumbs" class="mb-1" />

        <h1 class="font-serif text-2xl font-semibold text-brown-900">{{ $title }}</h1>
    </div>

    @isset($actions)
        <div class="flex items-center gap-2">
            {{ $actions }}
        </div>
    @endisset
</div>
