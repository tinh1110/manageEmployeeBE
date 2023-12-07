<?php

namespace App\Http\Requests\Issue;

use App\Traits\ApiFailedValidation;
use Illuminate\Foundation\Http\FormRequest;

class CreateIssueRequest extends FormRequest
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
            'assignee_id' => 'nullable|integer',
            'project_id' => 'required|integer',
            'subject' => 'required|string',
            'parent_id' => 'nullable|integer',
            'description' => 'required|string',
            'start_date' => 'required|date',
            'end_date' => 'required|date',
            'priority' => 'required|integer',
            'comment' => 'nullable|string',
            'status' => 'required|integer',
        ];
    }
}
