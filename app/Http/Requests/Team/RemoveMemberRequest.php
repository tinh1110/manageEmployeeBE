<?php

namespace App\Http\Requests\Team;

use App\Traits\ApiFailedValidation;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class RemoveMemberRequest extends FormRequest
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
     * @return array<string, ValidationRule|array|string>
     */
        public function rules(): array
    {
        return [
            'user_id' =>[
                'required',
                'integer',
                Rule::exists('users', 'id')
            ],
            'position_id' => 'required|integer'
        ];
    }
}
