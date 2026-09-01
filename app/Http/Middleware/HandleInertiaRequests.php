<?php

namespace App\Http\Middleware;

use App\Models\PlanUpgradeRequest;
use App\Models\PlatformNotification;
use App\Models\PlatformSetting;
use App\Models\Tenant;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Storage;
use Inertia\Middleware;

class HandleInertiaRequests extends Middleware
{
    protected $rootView = 'app';

    public function version(Request $request): ?string
    {
        return parent::version($request);
    }

    public function share(Request $request): array
    {
        $user = $request->is('platform*') ? Auth::guard('platform')->user() : Auth::guard('web')->user();

        return [
            ...parent::share($request),
            'auth' => [
                'user' => $user ? [
                    'id' => $user->id,
                    'name' => $user->name,
                    'phone' => $user->phone,
                    'role' => $user->role,
                ] : null,
                'tenant' => $user?->tenant ? [
                    'id' => $user->tenant->id,
                    'name' => $user->tenant->name,
                    'logo' => $user->tenant->logo ? Storage::disk('public')->url($user->tenant->logo) : null,
                    'status' => $user->tenant->status,
                    'trial_ends_at' => $user->tenant->trial_ends_at,
                    'subscription_ends_at' => $user->tenant->subscription_ends_at,
                    'theme' => $user->tenant->theme,
                    'locale' => $user->tenant->locale,
                    'rows_per_page' => $user->tenant->rows_per_page,
                ] : null,
            ],
            'flash' => [
                'success' => fn () => $request->session()->get('success'),
                'error' => fn () => $request->session()->get('error'),
                'receiptUrl' => fn () => $request->session()->get('receiptUrl'),
                'shareUrl' => fn () => $request->session()->get('shareUrl'),
                'generatedCode' => fn () => $request->session()->get('generatedCode'),
            ],
            'platformTheme' => fn () => $user?->isPlatformAdmin() ? PlatformSetting::get('theme', 'light') : null,
            'countryCode' => fn () => PlatformSetting::get('country_code', '964'),
            'pendingPlanRequestsCount' => fn () => $user?->isPlatformAdmin() ? PlanUpgradeRequest::where('status', 'pending')->count() : null,
            'notifications' => fn () => $user?->isPlatformAdmin() ? [
                'unreadCount' => PlatformNotification::whereNull('read_at')->count()
                    + Tenant::where(function ($q) {
                        $q->whereBetween('trial_ends_at', [now(), now()->addDays(7)])
                            ->orWhereBetween('subscription_ends_at', [now(), now()->addDays(7)]);
                    })->count(),
                'recent' => PlatformNotification::latest()->take(6)->get(['id', 'type', 'title', 'link', 'read_at', 'created_at']),
            ] : null,
            'tenantNotifications' => fn () => ($user && ! $user->isPlatformAdmin() && $user->tenant)
                ? $this->tenantNotifications($user->tenant)
                : null,
        ];
    }

    private function tenantNotifications(Tenant $tenant): array
    {
        $debtsQuery = $tenant->debts();

        $overdue = (clone $debtsQuery)
            ->whereColumn('paid_amount', '<', 'amount')
            ->whereNotNull('due_date')
            ->where('due_date', '<', now()->subDays($tenant->overdue_grace_days)->toDateString())
            ->with('debtor:id,name')
            ->latest('due_date')
            ->take(5)
            ->get(['id', 'debtor_id', 'amount', 'paid_amount', 'currency', 'due_date'])
            ->toBase();

        $dueToday = (clone $debtsQuery)
            ->whereColumn('paid_amount', '<', 'amount')
            ->where('due_date', now()->toDateString())
            ->with('debtor:id,name')
            ->take(5)
            ->get(['id', 'debtor_id', 'amount', 'paid_amount', 'currency', 'due_date'])
            ->toBase();

        $format = function ($debt, string $prefix) {
            $remaining = number_format($debt->amount - $debt->paid_amount).' '.($debt->currency === 'USD' ? '$' : 'د.ع');

            return [
                'id' => $prefix.'-'.$debt->id,
                'title' => "{$prefix}: ".($debt->debtor?->name ?? '—'),
                'meta' => $remaining,
                'link' => Route::has('app.debtors.show') ? route('app.debtors.show', $debt->debtor_id) : null,
            ];
        };

        $recent = $overdue->map(fn ($d) => $format($d, 'دين متأخر'))
            ->merge($dueToday->map(fn ($d) => $format($d, 'استحقاق اليوم')));

        return [
            'count' => $overdue->count() + $dueToday->count(),
            'recent' => $recent->values(),
        ];
    }
}
