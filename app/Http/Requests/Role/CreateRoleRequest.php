<?php

namespace App\Http\Requests\Role;

use App\Traits\ApiFailedValidation;
use Illuminate\Foundation\Http\FormRequest;

class CreateRoleRequest extends FormRequest
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
            'role_name' => 'required|string|max:45|min:4|unique:roles',
            'description' => 'required|string'
        ];
    }
}
