@props(['name', 'description' => null, 'startDate' => null, 'endDate' => null, 'location' => null, 'image' => null, 'url'])

<script type="application/ld+json">
    {!! json_encode(array_filter([
        '@@context' => 'https://schema.org',
        '@type' => 'Event',
        'name' => $name,
        'description' => $description,
        'startDate' => $startDate ? (string) $startDate : null,
        'endDate' => $endDate ? (string) $endDate : null,
        'location' => $location ? ['@type' => 'Place', 'name' => $location] : null,
        'image' => $image,
        'url' => $url,
        'mainEntityOfPage' => ['@type' => 'WebPage', '@id' => $url],
    ]), JSON_UNESCAPED_UNICODE | JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP) !!}
</script>
