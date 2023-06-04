<?php

namespace App\Http\Requests\Admin\User;

use Illuminate\Foundation\Http\FormRequest;

class CreateRequest extends FormRequest
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
            'name' => 'required|string|min:2|max:255',
            'surname' => 'required|string|min:2|max:255',
            'email' => 'required|email|unique:users|max:255',
            'address' => 'nullable|max:255',
            'city_id' => 'nullable|integer|exists:cities,id|max:255',
            'phone_number' => 'nullable|unique:users|max:255',
            'password' => 'required|string|min:8|confirmed|max:255',
            'trust_score' => 'nullable|integer|max:255',
            'role_id' => 'nullable|integer|exists:roles,id|max:255',
            'profile_photo_path' => 'nullable|image|max:2048',
        ];
    }
}
