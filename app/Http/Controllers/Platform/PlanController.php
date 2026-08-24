<?php

namespace App\Http\Controllers\Platform;

use App\Http\Controllers\Controller;
use App\Http\Requests\Platform\PlanRequest;
use App\Models\ActivityLog;
use App\Models\Plan;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response;

class PlanController extends Controller
{
    public function index(): Response
    {
        return Inertia::render('Platform/Plans/Index', [
            'plans' => Plan::orderBy('price')->get(),
        ]);
    }

    public function store(PlanRequest $request): RedirectResponse
    {
        $plan = Plan::create($request->validated());

        ActivityLog::record('plan_created', "إنشاء باقة جديدة: {$plan->name}");

        return back()->with('success', 'تمت إضافة الباقة.');
    }

    public function update(PlanRequest $request, Plan $plan): RedirectResponse
    {
        $plan->update($request->validated());

        ActivityLog::record('plan_updated', "تعديل الباقة: {$plan->name}");

        return back()->with('success', 'تم تحديث الباقة.');
    }

    public function destroy(Plan $plan): RedirectResponse
    {
        if ($plan->is_default_trial) {
            return back()->with('error', 'باقة التجربة المجانية الافتراضية غير قابلة للحذف.');
        }

        if ($plan->tenants()->exists()) {
            return back()->with('error', 'لا يمكن حذف باقة مرتبط بها مشتركون حالياً.');
        }

        $plan->delete();

        return back()->with('success', 'تم حذف الباقة.');
    }
}
