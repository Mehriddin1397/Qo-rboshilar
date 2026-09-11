<?php

namespace App\Http\Requests;

class UpdatePeriodRequest extends StorePeriodRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('update', $this->route('period')) ?? false;
    }
}
