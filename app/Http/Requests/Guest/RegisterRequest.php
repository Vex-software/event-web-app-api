<?php

namespace App\Http\Requests\Guest;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Str;

class RegisterRequest extends FormRequest
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
        $rules = [
            'name' => 'required|max:55',
            'surname' => 'required|max:65',
            // 'phone_number' => ['regex:/^\+?\d{12}$/'],
            'phone_number' => ['nullable', 'string', 'max:17', 'min:17', 'unique:clubs,phone_number,'],
            'email' => 'email|required|max:255|min:5',
            'address' => 'nullable|max:255',
            'city_id' => 'nullable|exists:cities,id|integer|min:1|max:81',
            'password' => 'required|confirmed|min:8|max:255|regex:/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d).+$/|string',
            'photo' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
        ];

        if ($this->filled('phone_number')) {
            $phoneNumber = preg_replace('/[^0-9]/', '', $this->input('phone_number'));

            if (Str::startsWith($phoneNumber, '90')) {
                $phoneNumber = '+90'.substr($phoneNumber, -10);
            }

            $phoneNumber = preg_replace('/(\d{2})(\d{3})(\d{3})(\d{2})(\d{2})/', '$1-$2-$3-$4-$5', $phoneNumber);

            $this->merge(['phone_number' => $phoneNumber]);
            
        }

        return $rules;
    }
}
