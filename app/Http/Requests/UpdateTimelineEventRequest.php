<?php

namespace App\Http\Requests;

class UpdateTimelineEventRequest extends StoreTimelineEventRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('update', $this->route('timelineEvent')) ?? false;
    }
}
