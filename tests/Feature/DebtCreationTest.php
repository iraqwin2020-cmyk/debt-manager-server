<?php

use App\Models\Debt;
use App\Models\DebtInstallment;

it('creates a lump-sum debt and assigns the tenant\'s starting receipt number', function () {
    ['user' => $user] = tenantWithOwner();

    $response = $this->actingAs($user)->post(route('app.debts.store'), [
        'debtor' => ['name' => 'عميل أول', 'phone' => '07701111111'],
        'amount' => 50_000,
        'currency' => 'IQD',
        'payment_type' => 'lump_sum',
        'due_date' => '2026-06-01',
    ]);

    $response->assertRedirect(route('app.debts.index'));
    expect(Debt::first())
        ->receipt_number->toBe(1)
        ->amount->toBe(50_000)
        ->paid_amount->toBe(0);
});

it('increments the receipt number sequentially across multiple debts for the same tenant', function () {
    ['user' => $user] = tenantWithOwner();

    foreach (range(1, 3) as $i) {
        $this->actingAs($user)->post(route('app.debts.store'), [
            'debtor' => ['name' => "عميل {$i}", 'phone' => "0770111111{$i}"],
            'amount' => 10_000,
            'currency' => 'IQD',
            'payment_type' => 'lump_sum',
            'due_date' => '2026-06-01',
        ]);
    }

    expect(Debt::orderBy('id')->pluck('receipt_number')->all())->toBe([1, 2, 3]);
});

it('keeps receipt numbering independent between two different tenants', function () {
    ['user' => $userA] = tenantWithOwner();
    ['user' => $userB] = tenantWithOwner();

    $this->actingAs($userA)->post(route('app.debts.store'), [
        'debtor' => ['name' => 'عميل أ', 'phone' => '07701112222'],
        'amount' => 10_000, 'currency' => 'IQD', 'payment_type' => 'lump_sum', 'due_date' => '2026-06-01',
    ]);
    $this->actingAs($userB)->post(route('app.debts.store'), [
        'debtor' => ['name' => 'عميل ب', 'phone' => '07701113333'],
        'amount' => 10_000, 'currency' => 'IQD', 'payment_type' => 'lump_sum', 'due_date' => '2026-06-01',
    ]);

    // كل مستأجر يبدأ ترقيمه من 1 بشكل مستقل، رغم وجود سجل دين آخر بالقاعدة المشتركة
    expect(Debt::withoutGlobalScopes()->pluck('receipt_number')->all())->toBe([1, 1]);
});

it('generates the correct installment schedule when creating a debt by installment count', function () {
    ['user' => $user] = tenantWithOwner();

    $this->actingAs($user)->post(route('app.debts.store'), [
        'debtor' => ['name' => 'عميل أقساط', 'phone' => '07701114444'],
        'amount' => 1_000_000,
        'currency' => 'IQD',
        'payment_type' => 'installments',
        'installment_method' => 'count',
        'installment_count' => 3,
        'interval_days' => 30,
        'first_due_date' => '2026-01-01',
    ]);

    $amounts = DebtInstallment::orderBy('seq_number')->pluck('amount')->all();

    expect($amounts)->toBe([333_000, 333_000, 334_000]);
    expect(array_sum($amounts))->toBe(1_000_000);
});

it('rejects an installment count too high for the debt amount instead of creating a negative installment', function () {
    ['user' => $user] = tenantWithOwner();

    $response = $this->actingAs($user)->post(route('app.debts.store'), [
        'debtor' => ['name' => 'عميل مرفوض', 'phone' => '07701115555'],
        'amount' => 2_000,
        'currency' => 'IQD',
        'payment_type' => 'installments',
        'installment_method' => 'count',
        'installment_count' => 10,
        'interval_days' => 30,
        'first_due_date' => '2026-01-01',
    ]);

    $response->assertSessionHasErrors('installment_count');
    expect(Debt::count())->toBe(0);
});
