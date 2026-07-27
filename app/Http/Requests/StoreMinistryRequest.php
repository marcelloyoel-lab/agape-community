<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class StoreMinistryRequest extends FormRequest
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
                'max:100',
                'unique:ministries,name',
            ],
            'allow_multiple_members' => [
                'required',
                'boolean',
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
            'name.required' => 'Ministry name is required.',
            'name.string' => 'Ministry name must be valid text.',
            'name.max' => 'Ministry name may not exceed 100 characters.',
            'name.unique' => 'A ministry with this name already exists.',

            'allow_multiple_members.required' => 'Please select the member assignment type.',
            'allow_multiple_members.boolean' => 'The member assignment type is invalid.',

            'is_active.required' => 'Please select the ministry status.',
            'is_active.boolean' => 'The ministry status is invalid.',
        ];
    }
}
