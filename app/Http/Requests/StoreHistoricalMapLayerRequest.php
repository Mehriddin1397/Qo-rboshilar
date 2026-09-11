<?php

namespace App\Http\Requests;

use App\Enums\HistoricalAccuracyStatus;
use App\Enums\HistoricalMapLayerStatus;
use App\Models\HistoricalMapLayer;
use App\Rules\ValidGeoJson;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreHistoricalMapLayerRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('create', HistoricalMapLayer::class) ?? false;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'title' => ['required', 'string', 'max:255'],
            'slug' => [
                'nullable', 'string', 'max:255', 'regex:/^[a-z0-9]+(?:-[a-z0-9]+)*$/',
                Rule::unique('historical_map_layers', 'slug')->ignore($this->route('historicalMapLayer')),
            ],
            'description' => ['nullable', 'string', 'max:5000'],

            'period_id' => ['nullable', 'exists:periods,id'],
            'historical_region_id' => ['nullable', 'exists:historical_regions,id'],

            'image' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:10240'],

            // §22-23: image overlay uchun georeference chegaralari — HistoricalMapLayer
            // rasm-asosli (raster) qatlam bo'lsa MapLibre ImageSource'ga to'g'ridan-to'g'ri
            // mos keladi (§13'dagi mavjud arxitektura, Faza 3). Vektor-only (faqat geojson)
            // qatlamlar uchun bularning barchasi bo'sh qoldirilishi mumkin.
            'bounds' => ['nullable', 'array'],
            'bounds.north' => ['nullable', 'numeric', 'between:-90,90'],
            'bounds.south' => ['nullable', 'numeric', 'between:-90,90'],
            'bounds.east' => ['nullable', 'numeric', 'between:-180,180'],
            'bounds.west' => ['nullable', 'numeric', 'between:-180,180'],

            'geojson' => ['nullable', 'string', new ValidGeoJson],

            'opacity' => ['nullable', 'numeric', 'min:0', 'max:1'],
            'is_active' => ['nullable', 'boolean'],
            'status' => ['required', Rule::enum(HistoricalMapLayerStatus::class)],
            'accuracy_status' => ['required', Rule::enum(HistoricalAccuracyStatus::class)],
            'sort_order' => ['nullable', 'integer', 'min:0', 'max:9999'],

            'source_reference_ids' => ['nullable', 'array'],
            'source_reference_ids.*' => ['exists:source_references,id'],
        ];
    }

    public function messages(): array
    {
        return [
            'slug.regex' => "Slug faqat kichik harflar, raqamlar va chiziqchadan iborat bo'lishi kerak.",
            'slug.unique' => 'Bu slug allaqachon band.',
        ];
    }

    /**
     * §9, §40: real geometriyaga (vektor yoki raster) ega layer manbasiz publish
     * qilinmaydi.
     */
    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator) {
            $status = $this->input('status');
            $existing = $this->route('historicalMapLayer');

            $hasGeometry = ValidGeoJson::hasRealGeometry($this->input('geojson'))
                || $this->hasFile('image')
                || filled(array_filter($this->input('bounds', [])))
                || ($existing && (
                    ($this->missing('geojson') && $existing->geojson && ValidGeoJson::hasRealGeometry(json_encode($existing->geojson)))
                    || (! $this->hasFile('image') && $existing->image_path)
                ));

            $hasSource = filled(array_filter($this->input('source_reference_ids', [])))
                || ($this->missing('source_reference_ids') && $existing && $existing->exists && $existing->sourceReferences()->exists());

            if ($status === HistoricalMapLayerStatus::Published->value && $hasGeometry && ! $hasSource) {
                $validator->errors()->add(
                    'source_reference_ids',
                    "Geometriyaga ega xarita qatlamini nashr etish uchun kamida bitta manba biriktirilishi shart."
                );
            }
        });
    }
}
