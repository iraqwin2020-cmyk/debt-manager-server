<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use App\Models\User;
use App\Services\DeviceService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Validation\ValidationException;
use Inertia\Inertia;
use Inertia\Response;

class AuthenticatedSessionController extends Controller
{
    public function create(): Response
    {
        return Inertia::render('Auth/Login', [
            'rememberedPhone' => request()->cookie('remembered_phone'),
        ]);
    }

    public function store(LoginRequest $request, DeviceService $devices): RedirectResponse
    {
        $request->ensureIsNotRateLimited();

        $user = User::where('phone', $request->input('phone'))->first();

        if (! $user || ! Hash::check($request->input('password'), $user->password)) {
            RateLimiter::hit($request->throttleKey());

            throw ValidationException::withMessages([
                'phone' => 'رقم الهاتف أو كلمة المرور غير صحيحة.',
            ]);
        }

        RateLimiter::clear($request->throttleKey());

        $remember = $request->boolean('remember', true);

        if ($user->isPlatformAdmin()) {
            Auth::guard('platform')->login($user, $remember);
            $request->session()->regenerate();

            return redirect()->intended(route('platform.dashboard', absolute: false));
        }

        Auth::guard('web')->login($user, $remember);
        $request->session()->regenerate();

        $tenant = $user->tenant;

        if (in_array($tenant->status, ['suspended', 'cancelled'], true)) {
            Auth::guard('web')->logout();

            throw ValidationException::withMessages([
                'phone' => $tenant->status === 'suspended'
                    ? 'حسابك معطَّل حالياً. تواصل مع الدعم الفني.'
                    : 'هذا الحساب ملغى.',
            ]);
        }

        $result = $devices->checkAndRegister($tenant);

        if (! $result['allowed']) {
            Auth::guard('web')->logout();

            throw ValidationException::withMessages([
                'phone' => 'وصلت للحد الأقصى لعدد الأجهزة المسموح بها بباقتك الحالية. سجّل خروج من جهاز آخر أولاً، أو رقّي باقتك.',
            ]);
        }

        return redirect()->intended(route('app.dashboard', absolute: false))
            ->cookie(DeviceService::COOKIE_NAME, $result['token'], 60 * 24 * 365)
            ->cookie('remembered_phone', $user->phone, 60 * 24 * 365);
    }

    public function destroy(Request $request, DeviceService $devices): RedirectResponse
    {
        $guardName = $request->is('platform*') ? 'platform' : 'web';
        $user = Auth::guard($guardName)->user();

        if ($guardName === 'web' && $user?->tenant_id) {
            $devices->releaseCurrentDevice($user->tenant);
        }

        Auth::guard($guardName)->logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        $response = redirect($guardName === 'platform' ? '/login' : '/');

        return $guardName === 'web'
            ? $response->withCookie(cookie()->forget(DeviceService::COOKIE_NAME))
            : $response;
    }
}
