<?php

namespace App\Http\Requests\Attendance;

use App\Traits\ApiFailedValidation;
use Illuminate\Foundation\Http\FormRequest;

class ImportAttendanceRequest extends FormRequest
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
            'import_file' => 'required|file|mimes:xlsx,csv',
        ];
    }
}
