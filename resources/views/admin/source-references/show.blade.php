@extends('layouts.admin')

@section('title', $title)

@section('content')
    <x-admin.page-header :title="$title" :breadcrumbs="$breadcrumbs">
        <x-slot:actions>
            @can('update', $sourceReference)
                <x-ui.button href="{{ route('admin.source-references.edit', $sourceReference) }}" variant="secondary">Tahrirlash</x-ui.button>
            @endcan
        </x-slot:actions>
    </x-admin.page-header>

    <div class="mt-6 grid gap-6 lg:grid-cols-3">
        <x-ui.card class="lg:col-span-1">
            <div class="flex flex-wrap justify-center gap-2">
                <x-ui.badge color="neutral">{{ $sourceReference->source_type->label() }}</x-ui.badge>
            </div>
            <dl class="mt-6 space-y-3 text-sm">
                <div><dt class="text-brown-500">Muallif</dt><dd class="text-brown-900">{{ $sourceReference->author }}</dd></div>
                <div><dt class="text-brown-500">Nashriyot</dt><dd class="text-brown-900">{{ $sourceReference->publisher ?? '—' }}</dd></div>
                <div><dt class="text-brown-500">Yil</dt><dd class="text-brown-900">{{ $sourceReference->year ?? '—' }}</dd></div>
                <div><dt class="text-brown-500">Bet</dt><dd class="text-brown-900">{{ $sourceReference->page ?? '—' }}</dd></div>
                <div><dt class="text-brown-500">Adabiyot</dt>
                    <dd class="text-brown-900">
                        @if ($sourceReference->literature)
                            <a href="{{ route('admin.adabiyotlar.show', $sourceReference->literature) }}" class="text-gold-600 hover:underline">{{ $sourceReference->literature->title }}</a>
                        @else
                            —
                        @endif
                    </dd>
                </div>
                @if ($sourceReference->url)
                    <div><dt class="text-brown-500">URL</dt><dd class="text-brown-900"><a href="{{ $sourceReference->url }}" target="_blank" rel="noopener noreferrer" class="text-gold-600 hover:underline break-all">{{ $sourceReference->url }}</a></dd></div>
                @endif
                <div><dt class="text-brown-500">Yaratilgan</dt><dd class="text-brown-900">{{ $sourceReference->created_at?->format('d.m.Y') }}</dd></div>
            </dl>
        </x-ui.card>

        <div class="space-y-6 lg:col-span-2">
            <x-ui.card title="Izoh">
                <p class="whitespace-pre-line text-sm text-brown-700">{{ $sourceReference->note ?? '—' }}</p>
            </x-ui.card>

            <x-ui.card title="Qayerda ishlatilmoqda">
                @if (array_sum($relatedCounts) > 0)
                    <ul class="grid gap-2 text-sm sm:grid-cols-2">
                        @foreach ($relatedCounts as $label => $count)
                            @if ($count > 0)
                                <li class="flex items-center justify-between rounded-md border border-sand px-4 py-2.5">
                                    <span class="text-brown-900 capitalize">{{ $label }}</span>
                                    <x-ui.badge color="gold">{{ $count }}</x-ui.badge>
                                </li>
                            @endif
                        @endforeach
                    </ul>
                @else
                    <x-ui.empty-state message="Bu manba hali hech qanday yozuvga biriktirilmagan." class="border-0 bg-transparent" />
                @endif
            </x-ui.card>
        </div>
    </div>
@endsection
