<?php

namespace App\Http\Requests\App;

use Illuminate\Foundation\Http\FormRequest;

class UpdateAccountRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'office_name' => ['required', 'string', 'max:255'],
            'name' => ['required', 'string', 'max:255'],
            'logo' => ['nullable', 'image', 'max:5120'],
        ];
    }

    public function attributes(): array
    {
        return [
            'office_name' => 'اسم المكتب',
            'name' => 'اسم المستخدم',
            'logo' => 'الشعار',
        ];
    }

    public function messages(): array
    {
        return [
            'required' => 'حقل :attribute مطلوب.',
            'image' => 'يجب أن يكون :attribute صورة.',
            'max' => 'تجاوز :attribute الحد المسموح.',
        ];
    }
}
