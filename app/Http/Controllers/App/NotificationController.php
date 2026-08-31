<?php

namespace App\Http\Controllers\App;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
use Inertia\Response;

class NotificationController extends Controller
{
    public function index(): Response
    {
        $tenant = Auth::user()->tenant;
        $debtsQuery = $tenant->debts();

        $overdue = (clone $debtsQuery)
            ->whereColumn('paid_amount', '<', 'amount')
            ->whereNotNull('due_date')
            ->whereRaw('DATE_ADD(due_date, INTERVAL ? DAY) < CURDATE()', [$tenant->overdue_grace_days])
            ->with('debtor:id,name')
            ->orderBy('due_date')
            ->get(['id', 'debtor_id', 'amount', 'paid_amount', 'currency', 'due_date']);

        $dueToday = (clone $debtsQuery)
            ->whereColumn('paid_amount', '<', 'amount')
            ->where('due_date', now()->toDateString())
            ->with('debtor:id,name')
            ->get(['id', 'debtor_id', 'amount', 'paid_amount', 'currency', 'due_date']);

        return Inertia::render('App/Notifications/Index', [
            'overdue' => $overdue,
            'dueToday' => $dueToday,
        ]);
    }
}
