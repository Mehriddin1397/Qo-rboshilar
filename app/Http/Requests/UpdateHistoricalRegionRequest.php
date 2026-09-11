<?php

namespace App\Http\Requests;

class UpdateHistoricalRegionRequest extends StoreHistoricalRegionRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('update', $this->route('historicalRegion')) ?? false;
    }
}
