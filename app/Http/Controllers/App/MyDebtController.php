<?php

namespace App\Http\Controllers\App;

use App\Http\Controllers\Controller;
use App\Http\Requests\App\StoreMyDebtPaymentRequest;
use App\Http\Requests\App\StoreMyDebtRequest;
use App\Models\ActivityLog;
use App\Models\Creditor;
use App\Models\MyDebt;
use App\Services\InstallmentCalculator;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Inertia\Response;

class MyDebtController extends Controller
{
    public function index(Request $request): Response
    {
        $tenant = $request->user()->tenant;

        $myDebts = MyDebt::query()
            ->with('creditor:id,name,phone')
            ->when($request->filled('q'), function ($q) use ($request) {
                $term = $request->string('q');
                $q->whereHas('creditor', fn ($q) => $q->where('name', 'like', "%{$term}%"));
            })
            ->latest()
            ->paginate($tenant->rows_per_page)
            ->withQueryString();

        $iOwe = MyDebt::query()
            ->selectRaw('currency, SUM(amount - paid_amount) as remaining')
            ->groupBy('currency')
            ->pluck('remaining', 'currency');

        return Inertia::render('App/MyDebts/Index', [
            'myDebts' => $myDebts,
            'filters' => (object) $request->only('q'),
            'iOwe' => $iOwe,
        ]);
    }

    public function show(MyDebt $myDebt): Response
    {
        $myDebt->load(['creditor', 'installments', 'payments' => fn ($q) => $q->latest('paid_at')]);

        return Inertia::render('App/MyDebts/Show', [
            'myDebt' => $myDebt,
        ]);
    }

    public function create(): Response
    {
        return Inertia::render('App/MyDebts/Create');
    }

    public function store(StoreMyDebtRequest $request): RedirectResponse
    {
        $data = $request->validated();

        $myDebt = DB::transaction(function () use ($data) {
            $creditor = $this->resolveCreditor($data['creditor'] ?? []);

            $myDebt = MyDebt::create([
                'creditor_id' => $creditor->id,
                'amount' => $data['amount'],
                'currency' => $data['currency'],
                'description' => $data['description'] ?? null,
                'due_date' => $data['payment_type'] === 'lump_sum' ? $data['due_date'] : null,
                'payment_type' => $data['payment_type'],
            ]);

            if ($data['payment_type'] === 'installments') {
                $installments = $data['installment_method'] === 'count'
                    ? InstallmentCalculator::byCount($data['amount'], $data['currency'], (int) $data['installment_count'], (int) $data['interval_days'], $data['first_due_date'])
                    : InstallmentCalculator::byInstallmentAmount($data['amount'], $data['currency'], (int) $data['installment_amount'], (int) $data['interval_days'], $data['first_due_date']);

                foreach ($installments as $installment) {
                    $myDebt->installments()->create($installment);
                }
            }

            ActivityLog::record('my_debt_created', "تسجيل دين عليّ لصالح {$creditor->name} بمبلغ {$data['amount']} {$data['currency']}");

            return $myDebt;
        });

        return redirect()->route('app.my-debts.index')->with('success', 'تم تسجيل الدين بنجاح.');
    }

    public function pay(StoreMyDebtPaymentRequest $request, MyDebt $myDebt): RedirectResponse
    {
        $data = $request->validated();

        DB::transaction(function () use ($data, $myDebt) {
            $myDebt->payments()->create([
                'creditor_id' => $myDebt->creditor_id,
                'installment_id' => $data['installment_id'] ?? null,
                'amount' => $data['amount'],
                'paid_at' => $data['paid_at'],
                'note' => $data['note'] ?? null,
            ]);

            ActivityLog::record('my_debt_payment_recorded', "تسجيل دفعة بمبلغ {$data['amount']} على دين لصالح {$myDebt->creditor->name}");
        });

        return back()->with('success', 'تم تسجيل الدفعة بنجاح.');
    }

    protected function resolveCreditor(array $payload): Creditor
    {
        if (! empty($payload['id'])) {
            return Creditor::findOrFail($payload['id']);
        }

        $existing = Creditor::where('phone', $payload['phone'])->first();
        if ($existing) {
            return $existing;
        }

        return Creditor::create([
            'name' => $payload['name'],
            'phone' => $payload['phone'],
            'address' => $payload['address'] ?? null,
            'note' => $payload['note'] ?? null,
        ]);
    }
}
