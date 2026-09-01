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
                    ->where('due_date', '<', now()->subDays($tenant->overdue_grace_days)->toDateString())
                    ->count(),
                'dueTodayCount' => (clone $debtsQuery)
                    ->whereColumn('paid_amount', '<', 'amount')
                    ->where('due_date', now()->toDateString())
                    ->count(),
                'favoritesCount' => $tenant->debtors()->where('is_favorite', true)->count(),
            ],
        ]);
    }
}
