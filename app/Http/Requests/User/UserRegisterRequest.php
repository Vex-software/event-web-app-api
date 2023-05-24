<?php

namespace App\Http\Requests\User;

use Illuminate\Foundation\Http\FormRequest;

class UserRegisterRequest extends FormRequest
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
            'name' => 'required|max:55',
            'surname' => 'required|max:65',
            'phone_number' => ['regex:/^\+?\d{12}$/'],
            'email' => 'email|required|unique:users',
            'address' => 'nullable',
            'city' => 'nullable',
            'password' => 'required|confirmed'
        ];
    }
}
