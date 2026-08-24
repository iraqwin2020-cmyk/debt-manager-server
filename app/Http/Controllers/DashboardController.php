<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
use Inertia\Response;

class DashboardController extends Controller
{
    public function index(): Response
    {
        $tenant = Auth::user()->tenant;

        $debtsQuery = $tenant->debts();

        $owedToMe = (clone $debtsQuery)
            ->selectRaw('currency, SUM(amount - paid_amount) as remaining')
            ->groupBy('currency')
            ->pluck('remaining', 'currency');

        return Inertia::render('App/Dashboard', [
            'stats' => [
                'owedToMe' => $owedToMe,
                'debtorsCount' => $tenant->debtors()->count(),
                'overdueCount' => (clone $debtsQuery)
                    ->whereColumn('paid_amount', '<', 'amount')
                    ->whereNotNull('due_date')
                    ->whereRaw('DATE_ADD(due_date, INTERVAL ? DAY) < CURDATE()', [$tenant->overdue_grace_days])
                    ->count(),
                'dueTodayCount' => (clone $debtsQuery)
                    ->whereColumn('paid_amount', '<', 'amount')
                    ->where('due_date', now()->toDateString())
                    ->count(),
            ],
        ]);
    }
}
