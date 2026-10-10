@props(['name', 'description' => null, 'image' => null, 'birthDate' => null, 'deathDate' => null, 'birthPlace' => null, 'deathPlace' => null, 'url'])

<script type="application/ld+json">
    {!! json_encode(array_filter([
        '@@context' => 'https://schema.org',
        '@type' => 'Person',
        'name' => $name,
        'description' => $description,
        'image' => $image,
        'birthDate' => $birthDate ? (string) $birthDate : null,
        'deathDate' => $deathDate ? (string) $deathDate : null,
        'birthPlace' => $birthPlace ? ['@type' => 'Place', 'name' => $birthPlace] : null,
        'deathPlace' => $deathPlace ? ['@type' => 'Place', 'name' => $deathPlace] : null,
        'url' => $url,
        'mainEntityOfPage' => ['@type' => 'WebPage', '@id' => $url],
    ]), JSON_UNESCAPED_UNICODE | JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP) !!}
</script>
