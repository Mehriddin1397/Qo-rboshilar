@props(['title', 'subtitle' => null, 'image' => null])

<section {{ $attributes->class(['relative overflow-hidden bg-brown-900']) }}>
    @if ($image)
        <img src="{{ $image }}" alt="" class="absolute inset-0 h-full w-full object-cover opacity-30">
    @else
        <div class="absolute inset-0 opacity-[0.07]" style="background-image: radial-gradient(circle at 1px 1px, #E4D6B8 1px, transparent 0); background-size: 24px 24px;"></div>
    @endif

    <div class="absolute inset-0 bg-gradient-to-t from-brown-900 via-brown-900/80 to-brown-900/40"></div>

    <div class="relative mx-auto max-w-4xl px-4 py-24 text-center sm:px-6 sm:py-32 lg:px-8">
        <h1 class="font-serif text-4xl font-bold tracking-wide text-paper sm:text-5xl lg:text-6xl">
            {{ $title }}
        </h1>

        @if ($subtitle)
            <p class="mx-auto mt-5 max-w-2xl font-quote text-lg italic text-paper-dark sm:text-xl">
                {{ $subtitle }}
            </p>
        @endif

        @isset($actions)
            <div class="mt-8 flex flex-col items-center justify-center gap-3 sm:flex-row">
                {{ $actions }}
            </div>
        @endisset
    </div>
</section>
