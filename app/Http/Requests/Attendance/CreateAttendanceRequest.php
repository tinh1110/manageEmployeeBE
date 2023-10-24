<?php

namespace App\Http\Requests\Attendance;

use App\Traits\ApiFailedValidation;
use Illuminate\Foundation\Http\FormRequest;
use App\Rules\ManagerId;

class CreateAttendanceRequest extends FormRequest
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
            'type_id' => 'required|exists:attendance_types,id',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
            'start_time' => 'required|date_format:H:i:s',
            'end_time' => 'required|date_format:H:i:s|after:start_time',
            'reason' => 'nullable|string',
            'img' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
            'result' => 'nullable|string',
            'ids' => ['required', 'array', new ManagerId],
            'ids.*' => 'int|exists:users,id'
        ];
    }
}
