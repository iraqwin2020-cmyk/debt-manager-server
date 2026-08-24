<?php

use App\Http\Controllers\Platform\ActivationCodeController;
use App\Http\Controllers\Platform\ActivityLogController;
use App\Http\Controllers\Platform\NotificationController;
use App\Http\Controllers\Platform\PlanController;
use App\Http\Controllers\Platform\PlanRequestController;
use App\Http\Controllers\Platform\PlatformDashboardController;
use App\Http\Controllers\Platform\PlatformSettingsController;
use App\Http\Controllers\Platform\SubscriberController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth', 'platform_admin'])->prefix('platform')->name('platform.')->group(function () {
    Route::get('/', [PlatformDashboardController::class, 'index'])->name('dashboard');

    Route::get('subscribers', [SubscriberController::class, 'index'])->name('subscribers.index');
    Route::get('subscribers/{tenant}', [SubscriberController::class, 'show'])->name('subscribers.show');
    Route::patch('subscribers/{tenant}/status', [SubscriberController::class, 'updateStatus'])->name('subscribers.update-status');
    Route::patch('subscribers/{tenant}/subscription-date', [SubscriberController::class, 'updateSubscriptionDate'])->name('subscribers.update-subscription-date');
    Route::post('subscribers/{tenant}/logout-devices', [SubscriberController::class, 'logoutAllDevices'])->name('subscribers.logout-devices');
    Route::delete('subscribers/{tenant}', [SubscriberController::class, 'destroy'])->name('subscribers.destroy');

    Route::get('plans', [PlanController::class, 'index'])->name('plans.index');
    Route::post('plans', [PlanController::class, 'store'])->name('plans.store');
    Route::patch('plans/{plan}', [PlanController::class, 'update'])->name('plans.update');
    Route::delete('plans/{plan}', [PlanController::class, 'destroy'])->name('plans.destroy');

    Route::get('activation-codes', [ActivationCodeController::class, 'index'])->name('activation-codes.index');
    Route::post('activation-codes', [ActivationCodeController::class, 'store'])->name('activation-codes.store');
    Route::patch('activation-codes/{activationCode}/cancel', [ActivationCodeController::class, 'cancel'])->name('activation-codes.cancel');

    Route::get('plan-requests', [PlanRequestController::class, 'index'])->name('plan-requests.index');
    Route::get('plan-requests/{planRequest}', [PlanRequestController::class, 'show'])->name('plan-requests.show');
    Route::patch('plan-requests/{planRequest}/approve', [PlanRequestController::class, 'approve'])->name('plan-requests.approve');
    Route::patch('plan-requests/{planRequest}/reject', [PlanRequestController::class, 'reject'])->name('plan-requests.reject');

    Route::get('activity-logs', [ActivityLogController::class, 'index'])->name('activity-logs.index');

    Route::get('notifications', [NotificationController::class, 'index'])->name('notifications.index');
    Route::patch('notifications/{notification}/read', [NotificationController::class, 'markRead'])->name('notifications.read');
    Route::patch('notifications/read-all', [NotificationController::class, 'markAllRead'])->name('notifications.read-all');

    Route::get('settings', [PlatformSettingsController::class, 'edit'])->name('settings.edit');
    Route::patch('settings/account', [PlatformSettingsController::class, 'updateAccount'])->name('settings.account');
    Route::patch('settings/password', [PlatformSettingsController::class, 'updatePassword'])->name('settings.password');
    Route::patch('settings/general', [PlatformSettingsController::class, 'updateGeneral'])->name('settings.general');
    Route::patch('settings/theme', [PlatformSettingsController::class, 'updateTheme'])->name('settings.theme');
    Route::patch('settings/about', [PlatformSettingsController::class, 'updateAbout'])->name('settings.about');
});
