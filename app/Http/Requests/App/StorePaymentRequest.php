<?php

namespace App\Http\Requests\App;

use Illuminate\Foundation\Http\FormRequest;

class StorePaymentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'debt_id' => ['required', 'integer', 'exists:debts,id'],
            'installment_id' => ['nullable', 'integer', 'exists:debt_installments,id'],
            'amount' => ['required', 'integer', 'min:1'],
            'paid_at' => ['required', 'date'],
            'note' => ['nullable', 'string', 'max:2000'],
        ];
    }

    public function attributes(): array
    {
        return [
            'debt_id' => 'الدين',
            'amount' => 'المبلغ',
            'paid_at' => 'تاريخ الدفعة',
        ];
    }

    public function messages(): array
    {
        return [
            'required' => 'حقل :attribute مطلوب.',
            'integer' => 'يجب أن يكون :attribute رقماً صحيحاً.',
            'min' => 'قيمة :attribute أقل من الحد المسموح.',
            'exists' => ':attribute المحدَّد غير موجود.',
            'date' => 'صيغة :attribute غير صحيحة.',
        ];
    }
}
