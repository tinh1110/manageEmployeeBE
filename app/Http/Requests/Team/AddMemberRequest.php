<?php

namespace App\Http\Requests\Team;

use App\Traits\ApiFailedValidation;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class AddMemberRequest extends FormRequest
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
            'user_id' =>[
                'required',
                'integer',
                Rule::exists('users', 'id')
            ],
            'position_id' => 'required|integer',
        ];
    }
}
