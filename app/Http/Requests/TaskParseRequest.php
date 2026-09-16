<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class TaskParseRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        return [
            'text' => 'required|string|max:1000',
        ];
    }

    /**
     * Get custom error messages.
     */
    public function messages(): array
    {
        return [
            'text.required' => 'Task text is required for parsing.',
            'text.max' => 'Task text must not exceed 1000 characters.',
        ];
    }
}
