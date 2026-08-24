<?php

namespace App\Providers;

use App\Models\MyDebtPayment;
use App\Models\Payment;
use App\Observers\MyDebtPaymentObserver;
use App\Observers\PaymentObserver;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        Payment::observe(PaymentObserver::class);
        MyDebtPayment::observe(MyDebtPaymentObserver::class);
    }
}
