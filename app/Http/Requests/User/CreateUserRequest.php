<?php

namespace App\Http\Requests\User;

use App\Traits\ApiFailedValidation;
use Illuminate\Foundation\Http\FormRequest;

class CreateUserRequest extends FormRequest
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
            'name' => 'required|string',
            'email' => 'required|string|email:rfc,dns|unique:users,email',
            'avatar' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
            'address' => 'nullable',
            'phone_number' => 'nullable|regex:/(0)[0-9]{9}/',
            'dob' => 'nullable|before:today',
            'details' => 'nullable',
            'gender' => 'required',
            'role_id' => 'required',
            'status' => 'required',
            'password' => 'required|confirmed|min:6',
        ];
    }
}
