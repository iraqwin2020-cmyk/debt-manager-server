<?php

namespace App\Http\Requests\App;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class GuarantorRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $guarantorId = $this->route('guarantor')?->id;

        return [
            'name' => ['required', 'string', 'max:255'],
            'phone' => [
                'required',
                'digits:11',
                Rule::unique('guarantors', 'phone')
                    ->where('tenant_id', $this->user()->tenant_id)
                    ->ignore($guarantorId),
            ],
            'address' => ['nullable', 'string', 'max:255'],
            'note' => ['nullable', 'string', 'max:2000'],
            'id_document_image' => ['nullable', 'image', 'max:4096'],
        ];
    }

    public function attributes(): array
    {
        return [
            'name' => 'الاسم',
            'phone' => 'رقم الهاتف',
            'address' => 'العنوان',
            'note' => 'الملاحظات',
            'id_document_image' => 'صورة المستمسك',
        ];
    }

    public function messages(): array
    {
        return [
            'required' => 'حقل :attribute مطلوب.',
            'digits' => 'يجب أن يتكون :attribute من 11 رقماً بالضبط.',
            'unique' => 'هذا الرقم مسجّل مسبقاً بكفيل آخر عندك.',
            'image' => 'يجب أن تكون :attribute صورة.',
            'max' => 'تجاوز :attribute الحد المسموح.',
        ];
    }
}
