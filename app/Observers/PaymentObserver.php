<?php

namespace App\Observers;

use App\Models\Payment;

/**
 * يحدّث paid_amount على القسط (إن وُجد) ثم على الدين ككل تلقائياً عند أي تغيير بالدفعات —
 * لا يُعدَّل paid_amount يدوياً من أي مكان آخر (معيار الهيكلية).
 */
class PaymentObserver
{
    public function saved(Payment $payment): void
    {
        $this->recalculate($payment);
    }

    public function deleted(Payment $payment): void
    {
        $this->recalculate($payment);
    }

    protected function recalculate(Payment $payment): void
    {
        if ($payment->installment_id) {
            $installment = $payment->installment()->withoutGlobalScopes()->first();
            if ($installment) {
                $installment->paid_amount = $installment->payments()->withoutGlobalScopes()->sum('amount');
                $installment->saveQuietly();
            }
        }

        $debt = $payment->debt()->withoutGlobalScopes()->first();
        if ($debt) {
            $debt->paid_amount = $debt->payments()->withoutGlobalScopes()->sum('amount');
            $debt->saveQuietly();
        }
    }
}
