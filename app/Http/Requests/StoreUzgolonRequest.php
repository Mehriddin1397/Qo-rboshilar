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
            'short_description' => ['required', 'string', 'max:5000'],
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

            'cover' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:20480'],
            'background' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:20480'],
            'remove_background' => ['nullable', 'boolean'],
            'gallery' => ['nullable', 'array'],
            'gallery.*' => ['image', 'mimes:jpg,jpeg,png,webp', 'max:20480'],

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
            'name.required' => "Qo'zg'olon nomini kiritish majburiy.",
            'name.max' => "Qo'zg'olon nomi 255 belgidan oshmasligi kerak.",
            'slug.regex' => "Slug faqat kichik harflar, raqamlar va chiziqchadan iborat bo'lishi kerak.",
            'slug.unique' => "Bu slug allaqachon band — boshqa qo'zg'olon tomonidan ishlatilmoqda.",
            'slug.max' => "Slug 255 belgidan oshmasligi kerak.",
            'short_description.required' => "Qisqa tavsifni kiritish majburiy.",
            'short_description.max' => "Qisqa tavsif :max belgidan oshmasligi kerak.",
            'start_year.required' => "Boshlanish yilini kiritish majburiy.",
            'start_year.integer' => "Boshlanish yili butun son bo'lishi kerak.",
            'start_year.min' => "Boshlanish yili :min yildan kichik bo'lishi mumkin emas.",
            'start_year.max' => "Boshlanish yili :max yildan katta bo'lishi mumkin emas.",
            'end_year.integer' => "Yakunlanish yili butun son bo'lishi kerak.",
            'end_year.min' => "Yakunlanish yili :min yildan kichik bo'lishi mumkin emas.",
            'end_year.max' => "Yakunlanish yili :max yildan katta bo'lishi mumkin emas.",
            'end_year.gte' => "Yakunlanish yili boshlanish yilidan oldin bo'lishi mumkin emas.",
            'historical_location.max' => "Tarixiy joy nomi 255 belgidan oshmasligi kerak.",
            'modern_location.max' => "Zamonaviy joy nomi 255 belgidan oshmasligi kerak.",
            'latitude.required_with' => "Kenglik (latitude) kiritilganda uzunlik (longitude) ham kerak.",
            'longitude.required_with' => "Uzunlik (longitude) kiritilganda kenglik (latitude) ham kerak.",
            'cover.image' => "Muqova fayli rasm bo'lishi kerak.",
            'cover.mimes' => "Muqova faqat jpg, jpeg, png yoki webp formatida bo'lishi kerak.",
            'cover.max' => "Muqova fayli hajmi 20 MB dan oshmasligi kerak.",
            'background.image' => "Orqa fon fayli rasm bo'lishi kerak.",
            'background.mimes' => "Orqa fon faqat jpg, jpeg, png yoki webp formatida bo'lishi kerak.",
            'background.max' => "Orqa fon fayli hajmi 20 MB dan oshmasligi kerak.",
            'gallery.*.image' => "Galereyaga yuklangan har bir fayl rasm bo'lishi kerak.",
            'gallery.*.mimes' => "Galereya rasmlari faqat jpg, jpeg, png yoki webp formatida bo'lishi kerak.",
            'gallery.*.max' => "Galereya rasmining hajmi 20 MB dan oshmasligi kerak.",
            'meta_title.max' => "Meta sarlavha 255 belgidan oshmasligi kerak.",
            'meta_description.max' => "Meta tavsif 255 belgidan oshmasligi kerak.",
        ];
    }

    public function attributes(): array
    {
        return [
            'name' => "Qo'zg'olon nomi",
            'slug' => 'Slug',
            'short_description' => 'Qisqa tavsif',
            'historical_context' => 'Tarixiy kontekst',
            'causes' => 'Sabablari',
            'main_events' => 'Asosiy voqealar',
            'results' => 'Natijalari',
            'historical_significance' => 'Tarixiy ahamiyati',
            'start_year' => 'Boshlanish yili',
            'end_year' => 'Yakunlanish yili',
            'region_id' => 'Hudud',
            'period_id' => 'Davr',
            'historical_location' => 'Tarixiy joy nomi',
            'modern_location' => 'Zamonaviy joy nomi',
            'latitude' => 'Kenglik (latitude)',
            'longitude' => 'Uzunlik (longitude)',
            'status' => 'Holat',
            'cover' => 'Muqova rasmi',
            'background' => 'Orqa fon rasmi',
            'gallery' => 'Galereya',
            'gallery.*' => 'Galereya rasmi',
            'qorboshi_ids' => "Bog'liq qo'rboshilar",
            'literature_ids' => "Bog'liq adabiyotlar",
            'source_reference_ids' => 'Manbalar',
        ];
    }
}
