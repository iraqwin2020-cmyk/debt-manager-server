<?php

use App\Models\ActivationCode;
use App\Models\Plan;

function activationCodeFor(int $tenantId, array $attrs = []): ActivationCode
{
    $plan = Plan::create([
        'name' => 'باقة مدفوعة', 'price' => 5000, 'duration_days' => 30,
        'max_debtors' => 500, 'max_devices' => 1, 'is_default_trial' => false,
    ]);

    return ActivationCode::create(array_merge([
        'code' => strtoupper(str()->random(10)),
        'plan_id' => $plan->id,
        'assigned_tenant_id' => $tenantId,
        'expires_at' => now()->addDays(30),
        'status' => 'unused',
    ], $attrs));
}

it('activates the tenant\'s plan when redeeming a valid code assigned to it', function () {
    ['user' => $user, 'tenant' => $tenant] = tenantWithOwner(['status' => 'trial']);
    $code = activationCodeFor($tenant->id);

    $this->actingAs($user)->post(route('app.settings.redeem-code'), ['code' => $code->code])
        ->assertSessionDoesntHaveErrors();

    expect($tenant->fresh())
        ->status->toBe('active')
        ->plan_id->toBe($code->plan_id);
    expect($code->fresh()->status)->toBe('used');
});

it('rejects a code that is assigned to a different tenant', function () {
    ['user' => $user] = tenantWithOwner();
    ['tenant' => $otherTenant] = tenantWithOwner();
    $code = activationCodeFor($otherTenant->id);

    $this->actingAs($user)->post(route('app.settings.redeem-code'), ['code' => $code->code])
        ->assertSessionHasErrors('code');

    expect($code->fresh()->status)->toBe('unused');
});

it('rejects a code that was already used', function () {
    ['user' => $user, 'tenant' => $tenant] = tenantWithOwner();
    $code = activationCodeFor($tenant->id, ['status' => 'used']);

    $this->actingAs($user)->post(route('app.settings.redeem-code'), ['code' => $code->code])
        ->assertSessionHasErrors('code');
});

it('rejects and auto-expires a code past its expiry date', function () {
    ['user' => $user, 'tenant' => $tenant] = tenantWithOwner();
    $code = activationCodeFor($tenant->id, ['expires_at' => now()->subDay()]);

    $this->actingAs($user)->post(route('app.settings.redeem-code'), ['code' => $code->code])
        ->assertSessionHasErrors('code');

    expect($code->fresh()->status)->toBe('expired');
});

it('rejects a code that does not exist at all', function () {
    ['user' => $user] = tenantWithOwner();

    $this->actingAs($user)->post(route('app.settings.redeem-code'), ['code' => 'DOES-NOT-EXIST'])
        ->assertSessionHasErrors('code');
});
