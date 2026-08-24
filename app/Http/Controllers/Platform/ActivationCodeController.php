<?php

namespace App\Http\Controllers\Platform;

use App\Http\Controllers\Controller;
use App\Http\Requests\Platform\GenerateActivationCodeRequest;
use App\Models\ActivationCode;
use App\Models\ActivityLog;
use App\Models\Plan;
use App\Models\Tenant;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Inertia\Inertia;
use Inertia\Response;

class ActivationCodeController extends Controller
{
    public function index(Request $request): Response
    {
        $codes = ActivationCode::query()
            ->with(['plan:id,name', 'assignedTenant:id,name,phone'])
            ->when($request->filled('q'), fn ($q) => $q->where('code', 'like', '%'.$request->input('q').'%'))
            ->when($request->filled('status'), fn ($q) => $q->where('status', $request->input('status')))
            ->latest()
            ->paginate(20)
            ->withQueryString();

        return Inertia::render('Platform/ActivationCodes/Index', [
            'codes' => $codes,
            'filters' => (object) $request->only('q', 'status'),
            'tenants' => Tenant::orderBy('name')->get(['id', 'name', 'phone']),
            'plans' => Plan::orderBy('price')->get(['id', 'name']),
        ]);
    }

    public function store(GenerateActivationCodeRequest $request): RedirectResponse
    {
        $validated = $request->validated();

        $code = ActivationCode::create([
            'code' => strtoupper(Str::random(10)),
            'plan_id' => $validated['plan_id'],
            'assigned_tenant_id' => $validated['assigned_tenant_id'],
            'expires_at' => $validated['expires_at'],
            'created_by' => $request->user()->id,
            'status' => 'unused',
        ]);

        ActivityLog::record('activation_code_generated', "توليد كود تفعيل {$code->code}", $validated['assigned_tenant_id']);

        return back()->with('success', "تم توليد الكود: {$code->code}");
    }

    public function cancel(ActivationCode $activationCode): RedirectResponse
    {
        if ($activationCode->status !== 'unused') {
            return back()->with('error', 'لا يمكن إلغاء كود مستخدَم مسبقاً.');
        }

        $activationCode->update(['status' => 'cancelled']);

        return back()->with('success', 'تم إلغاء الكود.');
    }
}
