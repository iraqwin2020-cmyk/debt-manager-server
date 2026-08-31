<?php

use App\Models\Plan;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

uses(TestCase::class)->in('Feature', 'Unit');
uses(RefreshDatabase::class)->in('Feature');

/** رقم هاتف عراقي صالح (11 رقماً بالضبط) وفريد لكل استدعاء ضمن تشغيلة الاختبارات. */
function testPhone(): string
{
    static $counter = 0;
    $counter++;

    return '07'.str_pad((string) $counter, 9, '0', STR_PAD_LEFT);
}

/**
 * ينشئ باقة، ومستأجراً (tenant)، وصاحب حساب (owner) عليها — الوحدة الأساسية
 * المتكررة بكل اختبارات الميزات (Feature) التي تحتاج مستخدماً مسجَّل دخوله.
 *
 * @return array{tenant: Tenant, user: User}
 */
function tenantWithOwner(array $tenantAttrs = [], array $userAttrs = []): array
{
    $plan = Plan::create([
        'name' => 'باقة اختبار',
        'price' => 0,
        'duration_days' => 30,
        'max_debtors' => 100,
        'max_devices' => 1,
        'is_default_trial' => false,
    ]);

    $tenant = Tenant::create(array_merge([
        'name' => 'مكتب اختبار',
        'phone' => testPhone(),
        'plan_id' => $plan->id,
        'status' => 'active',
    ], $tenantAttrs));

    $user = User::create(array_merge([
        'tenant_id' => $tenant->id,
        'name' => 'مستخدم اختبار',
        'phone' => testPhone(),
        'password' => Hash::make('password'),
        'role' => 'owner',
    ], $userAttrs));

    return ['tenant' => $tenant, 'user' => $user];
}
