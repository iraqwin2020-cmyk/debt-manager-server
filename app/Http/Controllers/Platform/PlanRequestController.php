<?php

namespace App\Http\Controllers\Platform;

use App\Http\Controllers\Controller;
use App\Models\ActivationCode;
use App\Models\ActivityLog;
use App\Models\PlanUpgradeRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Inertia\Inertia;
use Inertia\Response;

class PlanRequestController extends Controller
{
    public function index(Request $request): Response
    {
        $requests = PlanUpgradeRequest::query()
            ->with(['tenant', 'plan'])
            ->when($request->filled('q'), function ($q) use ($request) {
                $term = $request->string('q');
                $q->whereHas('tenant', fn ($q) => $q->where('name', 'like', "%{$term}%"));
            })
            ->when($request->filled('status'), fn ($q) => $q->where('status', $request->input('status')))
            ->latest()
            ->paginate(20)
            ->withQueryString();

        return Inertia::render('Platform/PlanRequests/Index', [
            'planRequests' => $requests,
            'filters' => (object) $request->only('q', 'status'),
        ]);
    }

    public function show(PlanUpgradeRequest $planRequest): Response
    {
        $planRequest->load(['tenant.plan', 'plan']);

        return Inertia::render('Platform/PlanRequests/Show', [
            'planRequest' => $planRequest,
            'debtorsCount' => $planRequest->tenant->debtors()->count(),
        ]);
    }

    public function approve(PlanUpgradeRequest $planRequest): RedirectResponse
    {
        if ($planRequest->status !== 'pending') {
            return back()->with('error', 'هذا الطلب تمت معالجته مسبقاً.');
        }

        $tenant = $planRequest->tenant;
        $plan = $planRequest->plan;

        $code = ActivationCode::create([
            'code' => strtoupper(Str::random(10)),
            'plan_id' => $plan->id,
            'assigned_tenant_id' => $tenant->id,
            'expires_at' => now()->addDays(7),
            'created_by' => auth()->id(),
            'status' => 'unused',
        ]);

        ActivityLog::record('plan_request_approved', "الموافقة على طلب باقة \"{$plan->name}\" لصالح {$tenant->name} — كود التفعيل: {$code->code}", $tenant->id);

        $planRequest->delete();

        return redirect()->route('platform.plan-requests.index')
            ->with('success', 'تمت الموافقة على الطلب. أرسل كود التفعيل للمشترك لإتمام تفعيل الباقة.')
            ->with('generatedCode', [
                'code' => $code->code,
                'tenantName' => $tenant->name,
                'tenantPhone' => $tenant->phone,
                'planName' => $plan->name,
            ]);
    }

    public function reject(Request $request, PlanUpgradeRequest $planRequest): RedirectResponse
    {
        if ($planRequest->status !== 'pending') {
            return back()->with('error', 'هذا الطلب تمت معالجته مسبقاً.');
        }

        $validated = $request->validate([
            'note' => ['required', 'string', 'max:500'],
        ], [
            'required' => 'حقل :attribute مطلوب.',
        ], ['note' => 'سبب الرفض']);

        ActivityLog::record('plan_request_rejected', "رفض طلب باقة لصالح {$planRequest->tenant->name}: {$validated['note']}", $planRequest->tenant_id);

        $planRequest->delete();

        return redirect()->route('platform.plan-requests.index')->with('success', 'تم رفض الطلب.');
    }
}
