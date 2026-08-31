<?php

use App\Models\Device;
use App\Services\DeviceService;
use Illuminate\Support\Facades\Hash;

it('allows the first login on a fresh tenant and registers its device', function () {
    ['user' => $user] = tenantWithOwner(userAttrs: ['password' => Hash::make('secret123')]);

    $response = $this->post(route('login'), ['phone' => $user->phone, 'password' => 'secret123']);

    $response->assertRedirect();
    $this->assertAuthenticatedAs($user);
    expect(Device::withoutGlobalScopes()->where('tenant_id', $user->tenant_id)->count())->toBe(1);
});

it('blocks a second device from logging in once the plan\'s device limit is reached', function () {
    ['user' => $user] = tenantWithOwner(userAttrs: ['password' => Hash::make('secret123')]);

    // الجهاز الأول يسجّل دخوله بنجاح ويحجز الفتحة الوحيدة المسموحة
    $this->withCookie(DeviceService::COOKIE_NAME, 'device-one')
        ->post(route('login'), ['phone' => $user->phone, 'password' => 'secret123']);
    auth('web')->logout();

    // جهاز مختلف (كوكي مختلف) يحاول الدخول بنفس بيانات الحساب
    $response = $this->withCookie(DeviceService::COOKIE_NAME, 'device-two')
        ->post(route('login'), ['phone' => $user->phone, 'password' => 'secret123']);

    $response->assertSessionHasErrors('phone');
    $this->assertGuest('web');
    expect(Device::withoutGlobalScopes()->where('tenant_id', $user->tenant_id)->count())->toBe(1);
});

it('lets the same already-registered device check in again without consuming a new slot', function () {
    ['tenant' => $tenant] = tenantWithOwner();
    $service = app(DeviceService::class);

    request()->cookies->set(DeviceService::COOKIE_NAME, 'device-one');
    $first = $service->checkAndRegister($tenant);
    $second = $service->checkAndRegister($tenant);

    expect($first['allowed'])->toBeTrue();
    expect($second['allowed'])->toBeTrue();
    expect(Device::withoutGlobalScopes()->where('tenant_id', $tenant->id)->count())->toBe(1);
});

it('releases the device slot on logout so another device can take it', function () {
    ['tenant' => $tenant, 'user' => $user] = tenantWithOwner();

    Device::withoutGlobalScopes()->create([
        'tenant_id' => $tenant->id,
        'device_token' => 'device-one',
        'last_active_at' => now(),
    ]);

    $this->withCookie(DeviceService::COOKIE_NAME, 'device-one')
        ->actingAs($user)
        ->post(route('logout'));

    expect(Device::withoutGlobalScopes()->where('tenant_id', $tenant->id)->count())->toBe(0);
});
