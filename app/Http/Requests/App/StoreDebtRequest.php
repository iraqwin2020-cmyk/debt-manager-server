<?php

namespace App\Http\Requests\App;

use App\Rules\MultipleOfCurrencyUnit;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreDebtRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $currency = $this->input('currency');

        return [
            'debtor.id' => ['nullable', 'integer', 'exists:debtors,id'],
            'debtor.name' => ['required_without:debtor.id', 'nullable', 'string', 'max:255'],
            'debtor.phone' => ['required_without:debtor.id', 'nullable', 'digits:11'],
            'debtor.address' => ['nullable', 'string', 'max:255'],
            'debtor.note' => ['nullable', 'string', 'max:2000'],

            'guarantors' => ['nullable', 'array'],
            'guarantors.*.id' => ['nullable', 'integer', 'exists:guarantors,id'],
            'guarantors.*.name' => ['required_without:guarantors.*.id', 'nullable', 'string', 'max:255'],
            'guarantors.*.phone' => ['required_without:guarantors.*.id', 'nullable', 'digits:11'],
            'guarantors.*.address' => ['nullable', 'string', 'max:255'],
            'guarantors.*.note' => ['nullable', 'string', 'max:2000'],

            'amount' => ['required', 'integer', 'min:1', new MultipleOfCurrencyUnit($currency ?? 'IQD')],
            'currency' => ['required', 'in:IQD,USD'],
            'description' => ['nullable', 'string', 'max:2000'],
            'payment_type' => ['required', 'in:lump_sum,installments'],

            'due_date' => ['required_if:payment_type,lump_sum', 'nullable', 'date'],

            'installment_method' => ['required_if:payment_type,installments', 'nullable', 'in:count,amount'],
            'installment_count' => [
                Rule::requiredIf(fn () => $this->input('payment_type') === 'installments' && $this->input('installment_method') === 'count'),
                'nullable', 'integer', 'min:2',
            ],
            'installment_amount' => [
                Rule::requiredIf(fn () => $this->input('payment_type') === 'installments' && $this->input('installment_method') === 'amount'),
                'nullable', 'integer', 'min:1',
            ],
            'interval_days' => ['required_if:payment_type,installments', 'nullable', 'integer', 'min:1'],
            'first_due_date' => ['required_if:payment_type,installments', 'nullable', 'date'],
        ];
    }

    public function attributes(): array
    {
        return [
            'debtor.name' => 'اسم العميل',
            'debtor.phone' => 'هاتف العميل',
            'guarantors.*.name' => 'اسم الكفيل',
            'guarantors.*.phone' => 'هاتف الكفيل',
            'amount' => 'المبلغ',
            'currency' => 'العملة',
            'payment_type' => 'نوع السداد',
            'due_date' => 'تاريخ الاستحقاق',
            'installment_count' => 'عدد الأقساط',
            'installment_amount' => 'مبلغ القسط',
            'interval_days' => 'الفترة بين الأقساط',
            'first_due_date' => 'تاريخ أول قسط',
        ];
    }

    public function messages(): array
    {
        return [
            'required' => 'حقل :attribute مطلوب.',
            'required_without' => 'حقل :attribute مطلوب.',
            'required_if' => 'حقل :attribute مطلوب.',
            'digits' => 'يجب أن يتكون :attribute من 11 رقماً بالضبط.',
            'integer' => 'يجب أن يكون :attribute رقماً صحيحاً.',
            'min' => 'قيمة :attribute أقل من الحد المسموح.',
            'in' => 'قيمة :attribute غير صالحة.',
            'date' => 'صيغة :attribute غير صحيحة.',
            'exists' => ':attribute المحدَّد غير موجود.',
        ];
    }
}
