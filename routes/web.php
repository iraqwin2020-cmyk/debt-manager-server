<?php

use App\Http\Controllers\Auth\AuthenticatedSessionController;
use App\Http\Controllers\Auth\RegisteredUserController;
use App\Models\PlatformSetting;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

Route::get('/', function () {
    return Auth::guard('web')->check()
        ? redirect()->route('app.dashboard')
        : Inertia::render('Welcome');
})->name('home');

Route::get('privacy-policy', function () {
    return Inertia::render('PrivacyPolicy', [
        'content' => PlatformSetting::get('privacy_policy', ''),
    ]);
})->name('privacy-policy');

// لا يوجد قيد "guest" هنا عمداً: يمكن أن يكون المستخدم مسجَّلاً دخوله بحساب
// (مشترك أو مدير منصة) ويريد تسجيل الدخول بالحساب الآخر في نفس المتصفح.
Route::get('register', [RegisteredUserController::class, 'create'])->name('register');
Route::post('register', [RegisteredUserController::class, 'store'])->middleware('throttle:6,1');

Route::get('login', [AuthenticatedSessionController::class, 'create'])->name('login');
Route::post('login', [AuthenticatedSessionController::class, 'store']);

Route::middleware('auth:web')->group(function () {
    Route::post('logout', [AuthenticatedSessionController::class, 'destroy'])->name('logout');
});

// اختصار: /dashboard يُوجَّه حسب الحساب المسجَّل دخوله في هذا المتصفح
Route::middleware('auth:web,platform')->get('dashboard', function () {
    return Auth::guard('platform')->check()
        ? redirect()->route('platform.dashboard')
        : redirect()->route('app.dashboard');
})->name('dashboard');

require __DIR__.'/app.php';
require __DIR__.'/platform.php';
