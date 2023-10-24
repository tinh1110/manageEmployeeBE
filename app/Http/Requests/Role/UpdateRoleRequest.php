<?php

namespace App\Http\Requests\Role;

use App\Traits\ApiFailedValidation;
use Illuminate\Foundation\Http\FormRequest;

class UpdateRoleRequest extends FormRequest
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
        $id = $this->route('id');
        return [
            'role_name' => "required|string|max:50|min:4|unique:roles,role_name,". $id,
            'description' => 'required|string',
            'role.*'=>'string'
        ];
    }
}
