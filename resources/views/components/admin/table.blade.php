@props(['headers' => []])

<div class="overflow-x-auto">
    <table class="w-full min-w-full divide-y divide-sand text-left text-sm">
        @if (count($headers))
            <thead>
                <tr>
                    @foreach ($headers as $header)
                        <th class="whitespace-nowrap px-4 py-2 font-medium text-brown-700">{{ $header }}</th>
                    @endforeach
                </tr>
            </thead>
        @endif

        <tbody class="divide-y divide-sand">
            {{ $slot }}
        </tbody>
    </table>
</div>
