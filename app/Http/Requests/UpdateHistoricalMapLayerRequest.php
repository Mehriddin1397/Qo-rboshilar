<?php

namespace App\Http\Requests;

class UpdateHistoricalMapLayerRequest extends StoreHistoricalMapLayerRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('update', $this->route('historicalMapLayer')) ?? false;
    }
}
