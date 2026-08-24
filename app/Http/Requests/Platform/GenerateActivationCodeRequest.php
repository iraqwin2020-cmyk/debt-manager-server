<?php

namespace App\Http\Requests\Platform;

use Illuminate\Foundation\Http\FormRequest;

class GenerateActivationCodeRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'assigned_tenant_id' => ['required', 'integer', 'exists:tenants,id'],
            'plan_id' => ['required', 'integer', 'exists:plans,id'],
            'expires_at' => ['required', 'date', 'after:now'],
        ];
    }

    public function attributes(): array
    {
        return [
            'assigned_tenant_id' => 'المشترك',
            'plan_id' => 'الباقة',
            'expires_at' => 'تاريخ انتهاء الصلاحية',
        ];
    }

    public function messages(): array
    {
        return [
            'required' => 'حقل :attribute مطلوب.',
            'exists' => ':attribute المحدَّد غير موجود.',
            'date' => 'صيغة :attribute غير صحيحة.',
            'after' => 'يجب أن يكون :attribute بتاريخ مستقبلي.',
        ];
    }
}
