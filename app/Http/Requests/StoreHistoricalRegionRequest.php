<?php

namespace App\Http\Requests;

use App\Enums\HistoricalAccuracyStatus;
use App\Enums\HistoricalRegionStatus;
use App\Enums\HistoricalRegionType;
use App\Models\HistoricalRegion;
use App\Rules\ValidGeoJson;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreHistoricalRegionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('create', HistoricalRegion::class) ?? false;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'slug' => [
                'nullable', 'string', 'max:255', 'regex:/^[a-z0-9]+(?:-[a-z0-9]+)*$/',
                Rule::unique('historical_regions', 'slug')->ignore($this->route('historicalRegion')),
            ],
            'historical_name' => ['nullable', 'string', 'max:255'],
            'modern_name' => ['nullable', 'string', 'max:255'],
            'region_type' => ['required', Rule::enum(HistoricalRegionType::class)],
            'description' => ['nullable', 'string', 'max:5000'],

            'period_id' => ['nullable', 'exists:periods,id'],
            'region_id' => ['nullable', 'exists:regions,id'],

            'geojson' => ['nullable', 'string', new ValidGeoJson],

            'status' => ['required', Rule::enum(HistoricalRegionStatus::class)],
            'accuracy_status' => ['required', Rule::enum(HistoricalAccuracyStatus::class)],
            'featured' => ['nullable', 'boolean'],
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
     * §9, §40: haqiqiy geometriyaga ega yozuvni manbasiz publish qilib bo'lmaydi.
     * Geojson bo'sh bo'lsa (masalan yozuv hali tayyorlanmoqda), bu tekshiruv
     * ishlamaydi — talab faqat "publish + real geometriya" kombinatsiyasiga tegishli.
     */
    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator) {
            $status = $this->input('status');

            // Update so'rovi geojson maydonini qayta yubormasligi mumkin (mavjud
            // qiymat DBda saqlanib qoladi) — shu holatda ham tekshiruv ishlashi
            // uchun mavjud model qiymatiga ham qaraladi.
            $existing = $this->route('historicalRegion');
            $hasGeojson = ValidGeoJson::hasRealGeometry($this->input('geojson'))
                || ($this->missing('geojson') && $existing && $existing->geojson && ValidGeoJson::hasRealGeometry(json_encode($existing->geojson)));
            $hasSource = filled(array_filter($this->input('source_reference_ids', [])))
                || ($this->missing('source_reference_ids') && $existing && $existing->exists && $existing->sourceReferences()->exists());

            if ($status === HistoricalRegionStatus::Published->value && $hasGeojson && ! $hasSource) {
                $validator->errors()->add(
                    'source_reference_ids',
                    "Geometriyaga ega tarixiy hududni nashr etish uchun kamida bitta manba biriktirilishi shart."
                );
            }
        });
    }
}
