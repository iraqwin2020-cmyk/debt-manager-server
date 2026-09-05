<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class EnsureOwner
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if (! $user || $user->role !== 'owner') {
            abort(403);
        }

        if (! $user->tenant) {
            Auth::guard('web')->logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            return redirect()->route('login')->with('error', 'هذا الحساب لم يعد متاحاً. الرجاء تسجيل الدخول من جديد أو إنشاء حساب جديد.');
        }

        return $next($request);
    }
}
