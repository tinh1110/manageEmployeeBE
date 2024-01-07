<?php

namespace App\Http\Requests\Team;

use App\Traits\ApiFailedValidation;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class CreateTeamRequest extends FormRequest
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
            'leader_id' => [
                'required',
                'integer',
                Rule::exists('users', 'id')
            ],
            'details' => 'nullable',
            'status' => 'nullable|integer',
            'start_time' => 'required|date',
            'end_time' => 'required|date|after_or_equal:start_time',
            'customer' => 'required',

        ];
    }
}
