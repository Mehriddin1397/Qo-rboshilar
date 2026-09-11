<?php

namespace App\Http\Requests;

use App\Enums\HistoricalAccuracyStatus;
use App\Enums\TimelineEventStatus;
use App\Models\TimelineEvent;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreTimelineEventRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('create', TimelineEvent::class) ?? false;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        $minYear = 1000;
        $maxYear = (int) date('Y');

        return [
            'title' => ['required', 'string', 'max:255'],
            'slug' => [
                'nullable', 'string', 'max:255', 'regex:/^[a-z0-9]+(?:-[a-z0-9]+)*$/',
                Rule::unique('timeline_events', 'slug')->ignore($this->route('timelineEvent')),
            ],
            'description' => ['required', 'string'],

            'start_year' => ['required', 'integer', "min:{$minYear}", "max:{$maxYear}"],
            'end_year' => ['nullable', 'integer', "min:{$minYear}", "max:{$maxYear}", 'gte:start_year'],
            // §4: faqat aniq sana haqiqatan ma'lum bo'lganda to'ldiriladi — soxta
            // kun/oy aniqligi majburlanmaydi (bu maydon ixtiyoriy, bo'sh qoldirilishi
            // odatiy holat).
            'event_date' => ['nullable', 'date'],

            'period_id' => ['nullable', 'exists:periods,id'],
            'qorboshi_id' => ['nullable', 'exists:qorboshilar,id'],
            'uzgolon_id' => ['nullable', 'exists:uzgolonlar,id'],
            'region_id' => ['nullable', 'exists:regions,id'],
            'historical_region_id' => ['nullable', 'exists:historical_regions,id'],

            'latitude' => ['nullable', 'numeric', 'between:-90,90', 'required_with:longitude'],
            'longitude' => ['nullable', 'numeric', 'between:-180,180', 'required_with:latitude'],

            'status' => ['required', Rule::enum(TimelineEventStatus::class)],
            'accuracy_status' => ['required', Rule::enum(HistoricalAccuracyStatus::class)],
            'featured' => ['nullable', 'boolean'],
            'sort_order' => ['nullable', 'integer', 'min:0', 'max:9999'],

            'image' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:6144'],

            'source_reference_ids' => ['nullable', 'array'],
            'source_reference_ids.*' => ['exists:source_references,id'],
        ];
    }

    public function messages(): array
    {
        return [
            'slug.regex' => "Slug faqat kichik harflar, raqamlar va chiziqchadan iborat bo'lishi kerak.",
            'slug.unique' => 'Bu slug allaqachon band.',
            'end_year.gte' => "Tugash yili boshlanish yilidan oldin bo'lishi mumkin emas.",
            'latitude.required_with' => 'Longitude kiritilgan bo\'lsa, latitude ham kerak.',
            'longitude.required_with' => 'Latitude kiritilgan bo\'lsa, longitude ham kerak.',
        ];
    }
}
