<?php

namespace App\Observers;

use App\Models\MyDebtPayment;

class MyDebtPaymentObserver
{
    public function saved(MyDebtPayment $payment): void
    {
        $this->recalculate($payment);
    }

    public function deleted(MyDebtPayment $payment): void
    {
        $this->recalculate($payment);
    }

    protected function recalculate(MyDebtPayment $payment): void
    {
        if ($payment->installment_id) {
            $installment = $payment->installment()->withoutGlobalScopes()->first();
            if ($installment) {
                $installment->paid_amount = $installment->payments()->withoutGlobalScopes()->sum('amount');
                $installment->saveQuietly();
            }
        }

        $myDebt = $payment->myDebt()->withoutGlobalScopes()->first();
        if ($myDebt) {
            $myDebt->paid_amount = $myDebt->payments()->withoutGlobalScopes()->sum('amount');
            $myDebt->saveQuietly();
        }
    }
}
