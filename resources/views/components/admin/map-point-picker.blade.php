@props([
    'latName' => 'latitude',
    'lngName' => 'longitude',
    'latValue' => null,
    'lngValue' => null,
    'readonly' => false,
])

@php
    $id = 'map-point-picker-'.\Illuminate\Support\Str::random(8);
    $initialLat = old($latName, $latValue);
    $initialLng = old($lngName, $lngValue);
@endphp

<div
    x-data="{
        lat: @js($initialLat !== null && $initialLat !== '' ? (float) $initialLat : null),
        lng: @js($initialLng !== null && $initialLng !== '' ? (float) $initialLng : null),
        picker: null,
        onMapChange(lat, lng) {
            this.lat = lat;
            this.lng = lng;
        },
        onInputChange() {
            this.picker?.setPosition(this.lat, this.lng);
        },
    }"
    x-init="picker = window.initMapMarkerPicker('{{ $id }}', {
        latitude: lat, longitude: lng, readonly: {{ $readonly ? 'true' : 'false' }}, onChange: (lat, lng) => onMapChange(lat, lng),
    })"
>
    <div id="{{ $id }}" class="h-80 w-full rounded-lg border border-sand"></div>

    @unless ($readonly)
        <div class="mt-3 grid gap-4 sm:grid-cols-2">
            <div>
                <label for="{{ $latName }}" class="block text-sm font-medium text-brown-900">Latitude</label>
                <input id="{{ $latName }}" type="number" step="0.0000001" min="-90" max="90" name="{{ $latName }}"
                       x-model.number="lat" @input="onInputChange()"
                       class="mt-1 w-full rounded-md border border-sand bg-white px-3 py-2 text-sm focus:border-gold-600 focus:outline-none focus:ring-1 focus:ring-gold-600">
                @error($latName)
                    <p class="mt-1 text-sm text-danger">{{ $message }}</p>
                @enderror
            </div>
            <div>
                <label for="{{ $lngName }}" class="block text-sm font-medium text-brown-900">Longitude</label>
                <input id="{{ $lngName }}" type="number" step="0.0000001" min="-180" max="180" name="{{ $lngName }}"
                       x-model.number="lng" @input="onInputChange()"
                       class="mt-1 w-full rounded-md border border-sand bg-white px-3 py-2 text-sm focus:border-gold-600 focus:outline-none focus:ring-1 focus:ring-gold-600">
                @error($lngName)
                    <p class="mt-1 text-sm text-danger">{{ $message }}</p>
                @enderror
            </div>
        </div>
        <p class="mt-2 text-xs text-brown-500">Xaritani bosib yoki markerni sudrab koordinatani tanlashingiz mumkin.</p>
    @endunless
</div>
