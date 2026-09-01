<?php

use App\Http\Controllers\App\DebtController;
use App\Http\Controllers\App\DebtorController;
use App\Http\Controllers\App\GuarantorController;
use App\Http\Controllers\App\MyDebtController;
use App\Http\Controllers\App\NotificationController;
use App\Http\Controllers\App\PaymentController;
use App\Http\Controllers\App\ReceiptController;
use App\Http\Controllers\App\SearchController;
use App\Http\Controllers\App\SettingsController;
use App\Http\Controllers\DashboardController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth:web', 'owner'])->prefix('app')->name('app.')->group(function () {
    Route::get('/', [DashboardController::class, 'index'])->name('dashboard');

    Route::get('debtors/favorites', [DebtorController::class, 'favorites'])->name('debtors.favorites');
    Route::patch('debtors/{debtor}/favorite', [DebtorController::class, 'toggleFavorite'])->name('debtors.toggle-favorite');
    Route::get('debtors/{debtor}/id-document/{index}', [DebtorController::class, 'showDocument'])->name('debtors.id-document');
    Route::resource('debtors', DebtorController::class);

    Route::get('guarantors/{guarantor}/id-document/{index}', [GuarantorController::class, 'showDocument'])->name('guarantors.id-document');
    Route::resource('guarantors', GuarantorController::class);

    Route::resource('debts', DebtController::class)->only(['index', 'create', 'store', 'show']);
    Route::resource('my-debts', MyDebtController::class)->only(['index', 'create', 'store', 'show']);
    Route::post('my-debts/{myDebt}/payments', [MyDebtController::class, 'pay'])->name('my-debts.pay');

    Route::get('payments/create', [PaymentController::class, 'create'])->name('payments.create');
    Route::post('payments', [PaymentController::class, 'store'])->name('payments.store');
    Route::get('debtors/{debtor}/debts', [PaymentController::class, 'debtorDebts'])->name('debtors.debts');

    Route::get('debts/{debt}/receipt', [ReceiptController::class, 'debt'])->name('debts.receipt');
    Route::get('payments/{payment}/receipt', [ReceiptController::class, 'payment'])->name('payments.receipt');
    Route::get('debtors/{debtor}/statement', [ReceiptController::class, 'statement'])->name('debtors.statement');

    Route::get('search/people', [SearchController::class, 'people'])->name('search.people');

    Route::get('notifications', [NotificationController::class, 'index'])->name('notifications.index');

    Route::get('subscription', [SettingsController::class, 'subscriptionEdit'])->name('subscription.edit');

    Route::get('settings', [SettingsController::class, 'edit'])->name('settings.edit');
    Route::get('settings/account', [SettingsController::class, 'accountEdit'])->name('settings.account.edit');
    Route::patch('settings/account', [SettingsController::class, 'updateAccount'])->name('settings.account');
    Route::delete('settings/account', [SettingsController::class, 'destroyAccount'])->name('settings.account.destroy');
    Route::patch('settings/password', [SettingsController::class, 'updatePassword'])->name('settings.password');
    Route::patch('settings/general', [SettingsController::class, 'updateGeneral'])->name('settings.general');
    Route::patch('settings/theme', [SettingsController::class, 'updateTheme'])->name('settings.theme');
    Route::post('settings/redeem-code', [SettingsController::class, 'redeemCode'])->name('settings.redeem-code')->middleware('throttle:10,1');
    Route::post('settings/request-plan', [SettingsController::class, 'requestPlan'])->name('settings.request-plan');
    Route::post('settings/about-message', [SettingsController::class, 'sendAboutMessage'])->name('settings.about-message')->middleware('throttle:3,1');
});
