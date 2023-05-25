<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class AdminCreateEventRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array|string>
     */
    public function rules(): array
    {
        return [
            'name' => 'required|string|unique:events,name',
            'title' => 'required|string',
            'description' => 'required',
            'start_time' => 'required|date',
            'end_time' => 'required|date',
            'club_id' => 'required|integer',
            'category_id' => 'required|integer',
            'location' => 'required|string',
            'image' => 'nullable',
            'quota' => 'nullable|integer',
        ];
    }
}
