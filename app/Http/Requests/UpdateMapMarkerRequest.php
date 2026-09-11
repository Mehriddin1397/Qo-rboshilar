<?php

namespace App\Http\Requests;

class UpdateMapMarkerRequest extends StoreMapMarkerRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('update', $this->route('mapMarker')) ?? false;
    }
}
