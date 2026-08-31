<?php

namespace App\Http\Controllers\App;

use App\Http\Controllers\Controller;
use App\Http\Requests\App\StoreDebtRequest;
use App\Models\ActivityLog;
use App\Models\Debt;
use App\Models\Debtor;
use App\Models\Guarantor;
use App\Models\PlatformSetting;
use App\Models\Tenant;
use App\Services\InstallmentCalculator;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Inertia\Response;

class DebtController extends Controller
{
    public function index(Request $request): Response
    {
        $tenant = $request->user()->tenant;

        $debts = Debt::query()
            ->with(['debtor:id,name,phone', 'guarantors:id,name'])
            ->when($request->filled('q'), function ($q) use ($request) {
                $term = $request->string('q');
                $q->where(function ($q) use ($term) {
                    $q->where('receipt_number', 'like', "%{$term}%")
                        ->orWhereHas('debtor', fn ($q) => $q->where('name', 'like', "%{$term}%"));
                });
            })
            ->when($request->filled('currency'), fn ($q) => $q->where('currency', $request->input('currency')))
            ->when($request->filled('payment_type'), fn ($q) => $q->where('payment_type', $request->input('payment_type')))
            ->when($request->input('status') === 'open', fn ($q) => $q->whereColumn('paid_amount', '<', 'amount'))
            ->when($request->input('status') === 'paid', fn ($q) => $q->whereColumn('paid_amount', '>=', 'amount'))
            ->when($request->input('status') === 'overdue', function ($q) use ($tenant) {
                $q->whereColumn('paid_amount', '<', 'amount')
                    ->whereNotNull('due_date')
                    ->whereRaw('DATE_ADD(due_date, INTERVAL ? DAY) < CURDATE()', [$tenant->overdue_grace_days]);
            })
            ->latest()
            ->paginate($tenant->rows_per_page)
            ->withQueryString();

        return Inertia::render('App/Debts/Index', [
            'debts' => $debts,
            'filters' => (object) $request->only('q', 'currency', 'payment_type', 'status'),
        ]);
    }

    public function show(Debt $debt): Response
    {
        $debt->load(['debtor', 'guarantors', 'installments', 'payments' => fn ($q) => $q->latest('paid_at')]);

        return Inertia::render('App/Debts/Show', [
            'debt' => $debt,
        ]);
    }

    public function create(): Response
    {
        return Inertia::render('App/Debts/Create');
    }

    public function store(StoreDebtRequest $request): RedirectResponse
    {
        $data = $request->validated();
        $tenant = $request->user()->tenant;

        if (isset($data['debtor'])) {
            $data['debtor']['new_images'] = $request->file('debtor.new_images', []);
        }
        foreach ($data['guarantors'] ?? [] as $i => $g) {
            $data['guarantors'][$i]['new_images'] = $request->file("guarantors.{$i}.new_images", []);
        }

        $debt = DB::transaction(function () use ($data, $tenant) {
            $lockedTenant = Tenant::whereKey($tenant->id)->lockForUpdate()->first();

            $debtor = $this->resolvePerson(Debtor::class, $data['debtor'] ?? []);

            $guarantors = collect($data['guarantors'] ?? [])
                ->map(fn ($g) => $this->resolvePerson(Guarantor::class, $g))
                ->filter();

            $receiptNumber = $lockedTenant->next_debt_receipt_number;
            $lockedTenant->increment('next_debt_receipt_number');

            $debt = Debt::create([
                'debtor_id' => $debtor->id,
                'amount' => $data['amount'],
                'currency' => $data['currency'],
                'description' => $data['description'] ?? null,
                'due_date' => $data['payment_type'] === 'lump_sum' ? $data['due_date'] : null,
                'payment_type' => $data['payment_type'],
                'receipt_number' => $receiptNumber,
            ]);

            if (! empty($guarantors)) {
                $debt->guarantors()->sync($guarantors->pluck('id'));
            }

            if ($data['payment_type'] === 'installments') {
                $installments = $data['installment_method'] === 'count'
                    ? InstallmentCalculator::byCount($data['amount'], $data['currency'], (int) $data['installment_count'], (int) $data['interval_days'], $data['first_due_date'])
                    : InstallmentCalculator::byInstallmentAmount($data['amount'], $data['currency'], (int) $data['installment_amount'], (int) $data['interval_days'], $data['first_due_date']);

                foreach ($installments as $installment) {
                    $debt->installments()->create($installment);
                }
            }

            ActivityLog::record('debt_created', "تسجيل دين جديد على {$debtor->name} بمبلغ {$data['amount']} {$data['currency']}");

            return $debt;
        });

        $debt->load('debtor');
        $intlPhone = PlatformSetting::get('country_code', '964').preg_replace('/^0/', '', $debt->debtor->phone);
        $waText = rawurlencode("مرحباً {$debt->debtor->name}، تم تسجيل دين جديد بمبلغ ".number_format($debt->amount)." {$debt->currency}، رقم الوصل #{$debt->receipt_number}.");

        return redirect()->route('app.debts.index')
            ->with('success', "تم تسجيل الدين — رقم الوصل {$debt->receipt_number}.")
            ->with('receiptUrl', route('app.debts.receipt', $debt->id))
            ->with('shareUrl', "https://wa.me/{$intlPhone}?text={$waText}");
    }

    /**
     * يربط بشخص موجود عبر id، أو ينشئ سجلاً جديداً — الهاتف هو مفتاح التطابق الحقيقي.
     */
    protected function resolvePerson(string $modelClass, array $payload): ?object
    {
        if (empty($payload) || (empty($payload['id']) && empty($payload['phone']))) {
            return null;
        }

        if (! empty($payload['id'])) {
            return $modelClass::findOrFail($payload['id']);
        }

        // لو الهاتف موجود مسبقاً، اربط به بدل إنشاء سجل مكرر (نفس قاعدة منع التكرار)
        $existing = $modelClass::where('phone', $payload['phone'])->first();
        if ($existing) {
            return $existing;
        }

        $imagePaths = array_map(
            fn ($file) => $file->store('id-documents', 'local'),
            $payload['new_images'] ?? []
        );

        return $modelClass::create([
            'name' => $payload['name'],
            'phone' => $payload['phone'],
            'address' => $payload['address'] ?? null,
            'note' => $payload['note'] ?? null,
            'id_document_images' => $imagePaths,
        ]);
    }
}
