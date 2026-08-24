<?php

namespace App\Http\Controllers\Platform;

use App\Http\Controllers\Controller;
use App\Models\ActivationCode;
use App\Models\PlanUpgradeRequest;
use App\Models\Tenant;
use Inertia\Inertia;
use Inertia\Response;

class PlatformDashboardController extends Controller
{
    public function index(): Response
    {
        return Inertia::render('Platform/Dashboard', [
            'stats' => [
                'total' => Tenant::count(),
                'active' => Tenant::where('status', 'active')->count(),
                'trial' => Tenant::where('status', 'trial')->count(),
                'expired' => Tenant::where('status', 'expired')->count(),
                'suspended' => Tenant::where('status', 'suspended')->count(),
                'cancelled' => Tenant::where('status', 'cancelled')->count(),
                'expiringSoon' => Tenant::where(function ($q) {
                    $q->whereBetween('trial_ends_at', [now(), now()->addDays(7)])
                        ->orWhereBetween('subscription_ends_at', [now(), now()->addDays(7)]);
                })->count(),
                'pendingPlanRequests' => PlanUpgradeRequest::where('status', 'pending')->count(),
                'unusedCodes' => ActivationCode::where('status', 'unused')->count(),
            ],
        ]);
    }
}
