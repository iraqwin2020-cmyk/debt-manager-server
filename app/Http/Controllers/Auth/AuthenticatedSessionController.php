<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use App\Services\DeviceService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
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

        if (! Auth::attempt($request->only('phone', 'password'), true)) {
            RateLimiter::hit($request->throttleKey());

            throw ValidationException::withMessages([
                'phone' => 'رقم الهاتف أو كلمة المرور غير صحيحة.',
            ]);
        }

        RateLimiter::clear($request->throttleKey());
        $request->session()->regenerate();

        $user = Auth::user();

        if ($user->isPlatformAdmin()) {
            return redirect()->intended(route('platform.dashboard', absolute: false));
        }

        $tenant = $user->tenant;

        if (in_array($tenant->status, ['suspended', 'cancelled'], true)) {
            Auth::logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            throw ValidationException::withMessages([
                'phone' => $tenant->status === 'suspended'
                    ? 'حسابك معطَّل حالياً. تواصل مع الدعم الفني.'
                    : 'هذا الحساب ملغى.',
            ]);
        }

        $result = $devices->checkAndRegister($tenant);

        if (! $result['allowed']) {
            Auth::logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            throw ValidationException::withMessages([
                'phone' => 'وصلت للحد الأقصى لعدد الأجهزة المسموح بها بباقتك الحالية. سجّل خروج من جهاز آخر أولاً، أو رقّي باقتك.',
            ]);
        }

        return redirect()->intended(route('dashboard', absolute: false))
            ->cookie(DeviceService::COOKIE_NAME, $result['token'], 60 * 24 * 365)
            ->cookie('remembered_phone', $user->phone, 60 * 24 * 365);
    }

    public function destroy(Request $request, DeviceService $devices): RedirectResponse
    {
        $user = Auth::user();

        if ($user && $user->tenant_id) {
            $devices->releaseCurrentDevice($user->tenant);
        }

        Auth::guard('web')->logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect('/')->withCookie(cookie()->forget(DeviceService::COOKIE_NAME));
    }
}
