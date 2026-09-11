<?php

namespace App\Http\Requests;

class UpdateBlogRequest extends StoreBlogRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('update', $this->route('blog')) ?? false;
    }
}
