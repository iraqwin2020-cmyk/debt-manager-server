<?php

namespace App\Http\Requests\Auth;

use Illuminate\Foundation\Http\FormRequest;

class RegisterTenantRequest extends FormRequest
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
            'phone' => ['required', 'digits:11', 'unique:tenants,phone', 'unique:users,phone'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
            'agree_privacy' => ['accepted'],
        ];
    }

    public function attributes(): array
    {
        return [
            'office_name' => 'اسم المكتب',
            'name' => 'الاسم',
            'phone' => 'رقم الهاتف',
            'password' => 'كلمة المرور',
            'agree_privacy' => 'الموافقة على سياسة الخصوصية',
        ];
    }

    public function messages(): array
    {
        return [
            'required' => 'حقل :attribute مطلوب.',
            'digits' => 'يجب أن يتكون :attribute من 11 رقماً بالضبط.',
            'unique' => ':attribute مستخدم مسبقاً — إذا كان حسابك، سجّل الدخول مباشرة.',
            'min' => 'يجب ألا يقل :attribute عن :min أحرف.',
            'confirmed' => 'تأكيد :attribute غير مطابق.',
            'accepted' => 'يجب الموافقة على سياسة الخصوصية للمتابعة.',
        ];
    }
}
