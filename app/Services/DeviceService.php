<?php

namespace App\Services;

use App\Models\Device;
use App\Models\Tenant;
use Illuminate\Support\Str;

/**
 * إنفاذ حد الأجهزة المتزامنة (plans.max_devices) — كل جهاز له device_token
 * محفوظ بكوكي طويل الأمد على جهاز المستخدم نفسه (لا علاقة له بحفظ رقم الهاتف بالمتصفح).
 */
class DeviceService
{
    public const COOKIE_NAME = 'device_token';

    public function currentToken(): string
    {
        return request()->cookie(self::COOKIE_NAME) ?? Str::uuid()->toString();
    }

    /**
     * @return array{allowed: bool, token: string}
     */
    public function checkAndRegister(Tenant $tenant): array
    {
        $token = $this->currentToken();

        $existing = Device::withoutGlobalScopes()
            ->where('tenant_id', $tenant->id)
            ->where('device_token', $token)
            ->first();

        if ($existing) {
            $existing->update(['last_active_at' => now()]);

            return ['allowed' => true, 'token' => $token];
        }

        $maxDevices = $tenant->plan?->max_devices ?? 1;
        $activeCount = Device::withoutGlobalScopes()->where('tenant_id', $tenant->id)->count();

        if ($activeCount >= $maxDevices) {
            return ['allowed' => false, 'token' => $token];
        }

        Device::withoutGlobalScopes()->create([
            'tenant_id' => $tenant->id,
            'device_token' => $token,
            'last_active_at' => now(),
        ]);

        return ['allowed' => true, 'token' => $token];
    }

    public function releaseCurrentDevice(Tenant $tenant): void
    {
        $token = request()->cookie(self::COOKIE_NAME);

        if (! $token) {
            return;
        }

        Device::withoutGlobalScopes()
            ->where('tenant_id', $tenant->id)
            ->where('device_token', $token)
            ->forceDelete();
    }

    public function releaseAllExcept(Tenant $tenant, string $keepToken): void
    {
        Device::withoutGlobalScopes()
            ->where('tenant_id', $tenant->id)
            ->where('device_token', '!=', $keepToken)
            ->forceDelete();
    }
}
