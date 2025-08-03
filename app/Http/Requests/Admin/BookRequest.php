<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class BookRequest extends FormRequest
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
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'grade_id' => 'required|exists:grades,id',
            'img.*'=>'nullable|image|mimes:jpg,jpeg,png,gif,svg',
            'nameOfBook' => 'required|string',
            'price' => 'required|numeric|regex:/^\d{1,5}(\.\d{1,2})?$/',
            'description' => 'nullable|string',
            'creationDate' => 'nullable|date_format:Y-m-d',
        ];
    }
}
