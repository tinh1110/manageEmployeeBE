<?php

namespace App\Http\Requests;

use App\Traits\ApiFailedValidation;
use Illuminate\Foundation\Http\FormRequest;

class EditProfileRequest extends FormRequest
{
    use ApiFailedValidation;

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
            'name' => 'required',
            'email' => 'required|email|unique:users,email,'.auth()->user()->id,
            'address' => 'nullable',
            'dob' => 'nullable|before:today|date|date_format:Y-m-d',
            'avatar' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:4096',
            'phone_number' => 'nullable|regex:/(0)[0-9]{9,11}$/',
            'gender' => 'required',
            'details' => 'nullable',
            'password' => 'nullable|confirmed|min:6',
        ];
    }
}
