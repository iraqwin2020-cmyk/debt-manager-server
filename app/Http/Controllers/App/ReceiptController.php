<?php

namespace App\Http\Controllers\App;

use App\Http\Controllers\Controller;
use App\Models\Debt;
use App\Models\Debtor;
use App\Models\Payment;
use Illuminate\Contracts\View\View;

class ReceiptController extends Controller
{
    public function statement(Debtor $debtor): View
    {
        $debtor->load(['tenant', 'debts' => fn ($q) => $q->with('payments')->latest()]);

        return view('receipts.statement', ['debtor' => $debtor, 'tenant' => $debtor->tenant]);
    }

    public function debt(Debt $debt): View
    {
        $debt->load(['debtor', 'guarantors', 'installments', 'tenant']);

        return view('receipts.debt', ['debt' => $debt, 'tenant' => $debt->tenant]);
    }

    public function payment(Payment $payment): View
    {
        $payment->load(['debt', 'debtor', 'installment', 'tenant']);

        return view('receipts.payment', ['payment' => $payment, 'tenant' => $payment->tenant]);
    }
}
