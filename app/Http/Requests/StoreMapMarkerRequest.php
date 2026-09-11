<?php

namespace App\Http\Requests;

use App\Enums\MapMarkerStatus;
use App\Enums\MapMarkerType;
use App\Models\MapMarker;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreMapMarkerRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('create', MapMarker::class) ?? false;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'title' => ['required', 'string', 'max:255'],
            'uzgolon_id' => ['nullable', 'exists:uzgolonlar,id'],
            'latitude' => ['required', 'numeric', 'between:-90,90'],
            'longitude' => ['required', 'numeric', 'between:-180,180'],
            'description' => ['nullable', 'string', 'max:2000'],
            'type' => ['required', Rule::enum(MapMarkerType::class)],
            'is_primary' => ['nullable', 'boolean'],
            'status' => ['required', Rule::enum(MapMarkerStatus::class)],
            'sort_order' => ['nullable', 'integer', 'min:0', 'max:9999'],
        ];
    }

    public function messages(): array
    {
        return [
            'latitude.required' => "Kenglik (latitude) majburiy — marker xaritada ko'rinishi uchun kerak.",
            'latitude.between' => 'Kenglik -90 dan 90 gacha bo\'lishi kerak.',
            'longitude.required' => "Uzunlik (longitude) majburiy — marker xaritada ko'rinishi uchun kerak.",
            'longitude.between' => 'Uzunlik -180 dan 180 gacha bo\'lishi kerak.',
        ];
    }
}
