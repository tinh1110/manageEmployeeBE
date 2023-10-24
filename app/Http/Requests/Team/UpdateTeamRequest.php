<?php

namespace App\Http\Requests\Team;

use App\Traits\ApiFailedValidation;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateTeamRequest extends FormRequest
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
            'parent_team_id' => [
                'nullable',
                'integer',
                Rule::exists('teams', 'id')
            ],
            'name' => "required|string|min:4|max:255|unique:teams,name,$id",
            'leader_id' => [
                'nullable',
                'integer',
                Rule::exists('users', 'id')
            ],
            'details' => 'nullable'
        ];
    }
}
