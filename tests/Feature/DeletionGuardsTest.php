<?php

use App\Models\Debt;
use App\Models\Debtor;
use App\Models\Guarantor;

it('refuses to delete a debtor who still owes an unpaid debt', function () {
    ['user' => $user] = tenantWithOwner();
    $debtor = Debtor::create(['tenant_id' => $user->tenant_id, 'name' => 'مدين', 'phone' => '07701230001']);
    Debt::create([
        'tenant_id' => $user->tenant_id, 'debtor_id' => $debtor->id,
        'amount' => 10_000, 'currency' => 'IQD', 'payment_type' => 'lump_sum', 'receipt_number' => 1,
    ]);

    $this->actingAs($user)->delete(route('app.debtors.destroy', $debtor->id))
        ->assertSessionHas('error');

    expect($debtor->fresh()->trashed())->toBeFalse();
});

it('allows deleting a debtor once their debt is fully paid', function () {
    ['user' => $user] = tenantWithOwner();
    $debtor = Debtor::create(['tenant_id' => $user->tenant_id, 'name' => 'مدين مسدَّد', 'phone' => '07701230002']);
    Debt::create([
        'tenant_id' => $user->tenant_id, 'debtor_id' => $debtor->id,
        'amount' => 10_000, 'currency' => 'IQD', 'payment_type' => 'lump_sum',
        'receipt_number' => 1, 'paid_amount' => 10_000,
    ]);

    $this->actingAs($user)->delete(route('app.debtors.destroy', $debtor->id))
        ->assertSessionHas('success');

    expect($debtor->fresh()->trashed())->toBeTrue();
});

it('refuses to delete a guarantor who still guarantees an unpaid debt', function () {
    ['user' => $user] = tenantWithOwner();
    $debtor = Debtor::create(['tenant_id' => $user->tenant_id, 'name' => 'مدين', 'phone' => '07701230003']);
    $guarantor = Guarantor::create(['tenant_id' => $user->tenant_id, 'name' => 'كفيل', 'phone' => '07701230004']);
    $debt = Debt::create([
        'tenant_id' => $user->tenant_id, 'debtor_id' => $debtor->id,
        'amount' => 10_000, 'currency' => 'IQD', 'payment_type' => 'lump_sum', 'receipt_number' => 1,
    ]);
    $debt->guarantors()->sync([$guarantor->id]);

    $this->actingAs($user)->delete(route('app.guarantors.destroy', $guarantor->id))
        ->assertSessionHas('error');

    expect($guarantor->fresh()->trashed())->toBeFalse();
});
