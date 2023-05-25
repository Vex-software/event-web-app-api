<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Str;

class AdminCreateClubRequest extends FormRequest
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
            'name' => ['required', 'string', 'max:255', 'unique:clubs,name,'],
            'title' => 'nullable|string',
            'description' => ['nullable', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:100', 'unique:clubs,email,'],
            'phone_number' => ['nullable', 'string', 'max:17', 'min:17', 'unique:clubs,phone_number,'],
            'address' => ['nullable', 'string', 'max:255', 'unique:clubs,address,'],
            'website' => 'nullable',
            'founded_year' => 'nullable|date',
            'manager_id' => 'required|integer|exists:users,id',
            'city_id' => ['required', 'integer', 'exists:cities,id'],
            'logo' => ['nullable', 'image', 'mimes:jpeg,png,jpg,gif,svg', 'max:2048'],
        ];

        if ($this->filled('phone_number')) {
            $phoneNumber = preg_replace('/[^0-9]/', '', $this->input('phone_number'));

            if (Str::startsWith($phoneNumber, "90")) {
                $phoneNumber = "+90" . substr($phoneNumber, -10);
            }

            $phoneNumber = preg_replace('/(\d{2})(\d{3})(\d{3})(\d{2})(\d{2})/', '$1-$2-$3-$4-$5', $phoneNumber);

            $this->merge(['phone_number' => $phoneNumber]);
        }

        return $rules;
    }
}
