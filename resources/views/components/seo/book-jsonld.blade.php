@props(['name', 'author' => null, 'description' => null, 'datePublished' => null, 'publisher' => null, 'isbn' => null, 'image' => null, 'url'])

<script type="application/ld+json">
    {!! json_encode(array_filter([
        '@@context' => 'https://schema.org',
        '@type' => 'Book',
        'name' => $name,
        'author' => $author ? ['@type' => 'Person', 'name' => $author] : null,
        'description' => $description,
        'datePublished' => $datePublished ? (string) $datePublished : null,
        'publisher' => $publisher ? ['@type' => 'Organization', 'name' => $publisher] : null,
        'isbn' => $isbn,
        'image' => $image,
        'url' => $url,
        'mainEntityOfPage' => ['@type' => 'WebPage', '@id' => $url],
    ]), JSON_UNESCAPED_UNICODE | JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP) !!}
</script>
