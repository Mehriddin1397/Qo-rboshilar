@props(['title', 'description' => null, 'authorName' => null, 'publishedAt' => null, 'modifiedAt' => null, 'image' => null, 'url'])

<script type="application/ld+json">
    {{-- '@context' Blade'ning @context direktivasi (Laravel Context feature) bilan
    to'qnashadi — @@context orqali escape qilingan, aks holda compile vaqtida
    butunlay boshqa PHP kodga almashtirilib ketardi (bag topilib tuzatildi). --}}
    {!! json_encode(array_filter([
        '@@context' => 'https://schema.org',
        '@type' => 'Article',
        'headline' => $title,
        'description' => $description,
        'author' => $authorName ? ['@type' => 'Person', 'name' => $authorName] : null,
        'datePublished' => $publishedAt?->toAtomString(),
        'dateModified' => ($modifiedAt ?? $publishedAt)?->toAtomString(),
        'image' => $image,
        'mainEntityOfPage' => ['@type' => 'WebPage', '@id' => $url],
    ]), JSON_UNESCAPED_UNICODE | JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP) !!}
</script>
