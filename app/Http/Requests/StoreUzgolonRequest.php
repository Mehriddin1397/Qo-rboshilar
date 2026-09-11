<?php

namespace App\Http\Requests;

use App\Enums\UzgolonStatus;
use App\Models\Uzgolon;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreUzgolonRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('create', Uzgolon::class) ?? false;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        $minYear = 1000;
        $maxYear = (int) date('Y');

        return [
            'name' => ['required', 'string', 'max:255'],
            'slug' => [
                'nullable',
                'string',
                'max:255',
                'regex:/^[a-z0-9]+(?:-[a-z0-9]+)*$/',
                Rule::unique('uzgolonlar', 'slug')->ignore($this->route('qozgolon')),
            ],
            'short_description' => ['required', 'string', 'max:2000'],
            'historical_context' => ['nullable', 'string'],
            'causes' => ['nullable', 'string'],
            'main_events' => ['nullable', 'string'],
            'results' => ['nullable', 'string'],
            'historical_significance' => ['nullable', 'string'],

            'start_year' => ['required', 'integer', "min:{$minYear}", "max:{$maxYear}"],
            'end_year' => ['nullable', 'integer', "min:{$minYear}", "max:{$maxYear}", 'gte:start_year'],

            'region_id' => ['nullable', 'exists:regions,id'],
            'period_id' => ['nullable', 'exists:periods,id'],
            'historical_location' => ['nullable', 'string', 'max:255'],
            'modern_location' => ['nullable', 'string', 'max:255'],

            'latitude' => ['nullable', 'numeric', 'between:-90,90', 'required_with:longitude'],
            'longitude' => ['nullable', 'numeric', 'between:-180,180', 'required_with:latitude'],

            'status' => ['required', Rule::enum(UzgolonStatus::class)],
            'featured' => ['nullable', 'boolean'],

            'meta_title' => ['nullable', 'string', 'max:255'],
            'meta_description' => ['nullable', 'string', 'max:255'],

            'cover' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:4096'],
            'gallery' => ['nullable', 'array'],
            'gallery.*' => ['image', 'mimes:jpg,jpeg,png,webp', 'max:6144'],

            'qorboshi_ids' => ['nullable', 'array'],
            'qorboshi_ids.*' => ['exists:qorboshilar,id'],
            'literature_ids' => ['nullable', 'array'],
            'literature_ids.*' => ['exists:adabiyotlar,id'],
            'source_reference_ids' => ['nullable', 'array'],
            'source_reference_ids.*' => ['exists:source_references,id'],
        ];
    }

    public function messages(): array
    {
        return [
            'slug.regex' => "Slug faqat kichik harflar, raqamlar va chiziqchadan iborat bo'lishi kerak.",
            'slug.unique' => "Bu slug allaqachon band — boshqa qo'zg'olon tomonidan ishlatilmoqda.",
            'end_year.gte' => "Yakunlanish yili boshlanish yilidan oldin bo'lishi mumkin emas.",
            'latitude.required_with' => "Kenglik (latitude) kiritilganda uzunlik (longitude) ham kerak.",
            'longitude.required_with' => "Uzunlik (longitude) kiritilganda kenglik (latitude) ham kerak.",
        ];
    }
}
