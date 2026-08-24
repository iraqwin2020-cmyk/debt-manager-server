<?php

namespace App\Http\Controllers\Platform;

use App\Http\Controllers\Controller;
use App\Models\PlatformNotification;
use App\Models\Tenant;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response;

class NotificationController extends Controller
{
    public function index(): Response
    {
        $notifications = PlatformNotification::query()
            ->latest()
            ->paginate(20);

        $expiringSoon = Tenant::where(function ($q) {
            $q->whereBetween('trial_ends_at', [now(), now()->addDays(7)])
                ->orWhereBetween('subscription_ends_at', [now(), now()->addDays(7)]);
        })->get(['id', 'name', 'trial_ends_at', 'subscription_ends_at']);

        return Inertia::render('Platform/Notifications/Index', [
            'notifications' => $notifications,
            'expiringSoon' => $expiringSoon,
        ]);
    }

    public function markRead(PlatformNotification $notification): RedirectResponse
    {
        $notification->update(['read_at' => now()]);

        return back();
    }

    public function markAllRead(): RedirectResponse
    {
        PlatformNotification::whereNull('read_at')->update(['read_at' => now()]);

        return back()->with('success', 'تم تحديد الكل كمقروء.');
    }
}
