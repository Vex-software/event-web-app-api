<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class SearchRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'q' => 'nullable|string|max:255|min:3',
            'orderBy' => 'nullable|string|max:255|in:id,name,created_at,updated_at|default:id',
            'order' => 'nullable|string|max:255|in:asc,desc',
        ];
    }
}
