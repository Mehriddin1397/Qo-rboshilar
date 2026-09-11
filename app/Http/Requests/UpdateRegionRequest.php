<?php

namespace App\Http\Requests;

class UpdateRegionRequest extends StoreRegionRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('update', $this->route('region')) ?? false;
    }
}
