<?php

namespace App\Http\Controllers\App;

use App\Http\Controllers\Controller;
use App\Http\Requests\App\StorePaymentRequest;
use App\Models\ActivityLog;
use App\Models\Debt;
use App\Models\PlatformSetting;
use App\Models\Tenant;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Inertia\Response;

class PaymentController extends Controller
{
    public function create(): Response
    {
        return Inertia::render('App/Payments/Create');
    }

    /** ديون عميل معيّن (المفتوحة فقط) لعرضها بخطوة اختيار الدين عند التسديد */
    public function debtorDebts(int $debtorId): JsonResponse
    {
        $debts = Debt::with('installments')
            ->where('debtor_id', $debtorId)
            ->whereColumn('paid_amount', '<', 'amount')
            ->get();

        return response()->json($debts);
    }

    public function store(StorePaymentRequest $request): RedirectResponse
    {
        $data = $request->validated();
        $tenant = $request->user()->tenant;

        $debt = Debt::findOrFail($data['debt_id']);

        $payment = DB::transaction(function () use ($data, $tenant, $debt) {
            $lockedTenant = Tenant::whereKey($tenant->id)->lockForUpdate()->first();

            $receiptNumber = $lockedTenant->next_payment_receipt_number;
            $lockedTenant->increment('next_payment_receipt_number');

            $payment = $debt->payments()->create([
                'debtor_id' => $debt->debtor_id,
                'installment_id' => $data['installment_id'] ?? null,
                'amount' => $data['amount'],
                'paid_at' => $data['paid_at'],
                'note' => $data['note'] ?? null,
                'receipt_number' => $receiptNumber,
            ]);

            ActivityLog::record('payment_recorded', "تسجيل دفعة بمبلغ {$data['amount']} على دين رقم {$debt->receipt_number}");

            return $payment;
        });

        $payment->load('debtor');
        $intlPhone = PlatformSetting::get('country_code', '964').preg_replace('/^0/', '', $payment->debtor->phone);
        $waText = rawurlencode("مرحباً {$payment->debtor->name}، تم استلام دفعة بمبلغ ".number_format($payment->amount)." {$debt->currency}، رقم الوصل #{$payment->receipt_number}.");

        return redirect()->route('app.debts.index')
            ->with('success', "تم تسجيل الدفعة — رقم الوصل {$payment->receipt_number}.")
            ->with('receiptUrl', route('app.payments.receipt', $payment->id))
            ->with('shareUrl', "https://wa.me/{$intlPhone}?text={$waText}");
    }
}
