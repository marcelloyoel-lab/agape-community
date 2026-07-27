<?php

namespace App\Http\Requests;

use App\Enums\Gender;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Enum;

class UpdateMemberRequest extends FormRequest
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
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'name' => [
                'required',
                'string',
                'max:255',
            ],

            'gender' => [
                'required',
                new Enum(Gender::class),
            ],

            'phone_number' => [
                'nullable',
                'regex:/^[0-9]+$/',
                'max:20',
            ],

            'is_active' => [
                'required',
                'boolean',
            ],
        ];
    }

    public function messages(): array
    {
        return [
            'name.required' => 'Member name is required.',
            'name.string' => 'Member name must be valid text.',
            'name.max' => 'Member name may not be greater than 255 characters.',

            'gender.required' => 'Please select a gender.',

            'phone_number.regex' => 'Phone number must contain numbers only.',
            'phone_number.max' => 'Phone number may not be greater than 20 digits.',

            'is_active.required' => 'Please select a status.',
            'is_active.boolean' => 'The selected status is invalid.',
        ];
    }

    public function attributes(): array
    {
        return [
            'name' => 'member name',
            'gender' => 'gender',
            'phone_number' => 'phone number',
            'is_active' => 'status',
        ];
    }
}
