<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\RegisterTenantRequest;
use App\Models\ActivityLog;
use App\Models\Plan;
use App\Models\PlatformNotification;
use App\Models\PlatformSetting;
use App\Models\Tenant;
use App\Models\User;
use App\Services\DeviceService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Inertia\Inertia;
use Inertia\Response;

class RegisteredUserController extends Controller
{
    public function create(): Response
    {
        return Inertia::render('Auth/Register');
    }

    public function store(RegisterTenantRequest $request, DeviceService $devices)
    {
        $validated = $request->validated();

        $tenant = DB::transaction(function () use ($validated) {
            $trialDays = (int) PlatformSetting::get('trial_days', 14);

            $tenant = Tenant::create([
                'name' => $validated['office_name'],
                'phone' => $validated['phone'],
                'plan_id' => Plan::defaultTrial()->id,
                'trial_ends_at' => now()->addDays($trialDays),
                'status' => 'trial',
            ]);

            User::create([
                'tenant_id' => $tenant->id,
                'name' => $validated['name'],
                'phone' => $validated['phone'],
                'password' => Hash::make($validated['password']),
                'role' => 'owner',
            ]);

            ActivityLog::record('tenant_registered', "تسجيل حساب جديد: {$tenant->name}", $tenant->id);

            PlatformNotification::notify('new_tenant', "مشترك جديد سجّل حساباً: {$tenant->name}", route('platform.subscribers.show', $tenant->id, absolute: false));

            return $tenant;
        });

        $user = $tenant->users()->first();
        Auth::login($user);

        $result = $devices->checkAndRegister($tenant);
        $response = redirect()->intended(route('dashboard', absolute: false));

        if ($result['allowed']) {
            $response->cookie(DeviceService::COOKIE_NAME, $result['token'], 60 * 24 * 365);
        }

        return $response;
    }
}
