<?php

namespace App\Http\Controllers\Platform;

use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
use App\Models\Plan;
use App\Models\Tenant;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Inertia\Inertia;
use Inertia\Response;

class SubscriberController extends Controller
{
    public function index(Request $request): Response
    {
        $tenants = Tenant::query()
            ->when($request->filled('q'), function ($q) use ($request) {
                $term = $request->string('q');
                $q->where(fn ($q) => $q->where('name', 'like', "%{$term}%")->orWhere('phone', 'like', "%{$term}%"));
            })
            ->when($request->filled('status'), fn ($q) => $q->where('status', $request->input('status')))
            ->when($request->filled('type'), fn ($q) => $q->where('type', $request->input('type')))
            ->latest()
            ->paginate(20)
            ->withQueryString();

        return Inertia::render('Platform/Subscribers/Index', [
            'tenants' => $tenants,
            'filters' => (object) $request->only('q', 'status', 'type'),
        ]);
    }

    public function show(Tenant $tenant): Response
    {
        return Inertia::render('Platform/Subscribers/Show', [
            'tenant' => $tenant->load('plan'),
            'debtorsCount' => $tenant->debtors()->count(),
            'devicesCount' => $tenant->devices()->count(),
            'logs' => ActivityLog::where('tenant_id', $tenant->id)->latest()->limit(20)->get(),
            'plans' => Plan::orderBy('price')->get(['id', 'name', 'duration_days']),
        ]);
    }

    public function activateDirectly(Request $request, Tenant $tenant): RedirectResponse
    {
        $validated = $request->validate([
            'plan_id' => ['required', 'exists:plans,id'],
        ], [
            'required' => 'حقل :attribute مطلوب.',
            'exists' => ':attribute غير موجودة.',
        ], ['plan_id' => 'الباقة']);

        $plan = Plan::findOrFail($validated['plan_id']);

        $tenant->update([
            'plan_id' => $plan->id,
            'status' => 'active',
            'subscription_ends_at' => now()->addDays($plan->duration_days ?? 30),
        ]);

        ActivityLog::record('tenant_activated_directly', "تفعيل فوري لباقة \"{$plan->name}\" لصالح {$tenant->name} من لوحة الإدارة", $tenant->id);

        return back()->with('success', "تم تفعيل باقة \"{$plan->name}\" لهذا المشترك فوراً.");
    }

    public function updateStatus(Request $request, Tenant $tenant): RedirectResponse
    {
        $validated = $request->validate([
            'status' => ['required', Rule::in(['active', 'suspended', 'cancelled'])],
        ], [
            'required' => 'حقل :attribute مطلوب.',
            'in' => 'قيمة :attribute غير صالحة.',
        ]);

        $tenant->update(['status' => $validated['status']]);

        ActivityLog::record('tenant_status_changed', "تغيير حالة {$tenant->name} إلى {$validated['status']}", $tenant->id);

        return back()->with('success', 'تم تحديث حالة المشترك.');
    }

    public function destroy(Tenant $tenant): RedirectResponse
    {
        if ($tenant->status !== 'cancelled') {
            return back()->with('error', 'لا يمكن حذف مشترك إلا بحالة "ملغى".');
        }

        $tenant->delete();

        ActivityLog::record('tenant_deleted', "حذف المشترك {$tenant->name}");

        return redirect()->route('platform.subscribers.index')->with('success', 'تم نقل المشترك لسلة المحذوفات.');
    }

    public function logoutAllDevices(Tenant $tenant): RedirectResponse
    {
        $tenant->devices()->delete();

        ActivityLog::record('tenant_devices_cleared', "تسجيل خروج {$tenant->name} من كل الأجهزة (دعم فني)", $tenant->id);

        return back()->with('success', 'تم تسجيل الخروج من كل أجهزة هذا المشترك.');
    }

    public function updateSubscriptionDate(Request $request, Tenant $tenant): RedirectResponse
    {
        $validated = $request->validate([
            'subscription_ends_at' => ['required', 'date'],
        ], [
            'required' => 'حقل :attribute مطلوب.',
            'date' => 'تاريخ :attribute غير صالح.',
        ], ['subscription_ends_at' => 'تاريخ انتهاء الاشتراك']);

        $tenant->update(['subscription_ends_at' => $validated['subscription_ends_at']]);

        ActivityLog::record('tenant_subscription_date_adjusted', "تعديل يدوي لتاريخ انتهاء اشتراك {$tenant->name} إلى {$validated['subscription_ends_at']}", $tenant->id);

        return back()->with('success', 'تم تحديث تاريخ انتهاء الاشتراك.');
    }
}
