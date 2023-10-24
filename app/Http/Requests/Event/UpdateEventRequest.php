<?php

namespace App\Http\Requests\Event;

use App\Traits\ApiFailedValidation;
use Illuminate\Foundation\Http\FormRequest;

class UpdateEventRequest extends FormRequest
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
            'name' => 'required',
            'type' => 'required',
            'link' => 'nullable',
            'start_time' => 'required|date|date_format:Y-m-d H:i:s',
            'end_time' => 'required|date|date_format:Y-m-d H:i:s',
            'description' => 'nullable',
            'location' => 'nullable',
            'image.*' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
        ];
    }
}
