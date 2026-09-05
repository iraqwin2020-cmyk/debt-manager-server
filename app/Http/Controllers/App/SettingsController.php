<?php

namespace App\Http\Controllers\App;

use App\Http\Controllers\Controller;
use App\Http\Requests\App\UpdateAccountRequest;
use App\Mail\AboutContactMessage;
use App\Models\ActivationCode;
use App\Models\Plan;
use App\Models\PlanUpgradeRequest;
use App\Models\PlatformNotification;
use App\Models\PlatformSetting;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;
use Inertia\Inertia;
use Inertia\Response;

class SettingsController extends Controller
{
    public function edit(Request $request): Response
    {
        $tenant = $request->user()->tenant;

        return Inertia::render('App/Settings/Edit', [
            'tenant' => [
                ...$tenant->toArray(),
                'logo' => $tenant->logo ? Storage::disk('public')->url($tenant->logo) : null,
            ],
            'about' => [
                'description' => PlatformSetting::get('about_description', ''),
                'whatsapp' => PlatformSetting::get('about_whatsapp', ''),
                'email' => PlatformSetting::get('about_email', ''),
                'company_name' => PlatformSetting::get('about_company_name', ''),
            ],
        ]);
    }

    public function subscriptionEdit(Request $request): Response
    {
        $tenant = $request->user()->tenant;

        return Inertia::render('App/Subscription/Edit', [
            'tenant' => $tenant,
            'planRequests' => PlanUpgradeRequest::where('tenant_id', $tenant->id)->with('plan')->latest()->get(),
            'contactPhone' => PlatformSetting::get('about_whatsapp', ''),
        ]);
    }

    public function accountEdit(Request $request): Response
    {
        $tenant = $request->user()->tenant;

        return Inertia::render('App/Settings/Account', [
            'tenant' => [
                ...$tenant->toArray(),
                'logo' => $tenant->logo ? Storage::disk('public')->url($tenant->logo) : null,
            ],
            'user' => $request->user(),
        ]);
    }

    public function updateAccount(UpdateAccountRequest $request): RedirectResponse
    {
        $tenant = $request->user()->tenant;
        $data = $request->validated();

        if ($request->hasFile('logo')) {
            if ($tenant->logo) {
                Storage::disk('public')->delete($tenant->logo);
            }
            $tenant->logo = $request->file('logo')->store('logos', 'public');
        }

        $tenant->name = $data['office_name'];
        $tenant->save();

        $request->user()->update(['name' => $data['name']]);

        return back()->with('success', 'تم تحديث بيانات الحساب.');
    }

    public function destroyAccount(Request $request): RedirectResponse
    {
        $request->validate([
            'password' => ['required', 'current_password'],
        ], [
            'required' => 'حقل :attribute مطلوب.',
            'current_password' => 'كلمة المرور غير صحيحة.',
        ], ['password' => 'كلمة المرور']);

        $user = $request->user();
        $tenant = $user->tenant;

        Auth::guard('web')->logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        $user->delete();
        $tenant->delete();

        return redirect()->route('home')->with('success', 'تم حذف الحساب بنجاح.');
    }

    public function updatePassword(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'current_password' => ['required', 'current_password'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ], [
            'required' => 'حقل :attribute مطلوب.',
            'current_password' => 'كلمة المرور الحالية غير صحيحة.',
            'min' => 'يجب ألا يقل :attribute عن :min أحرف.',
            'confirmed' => 'تأكيد :attribute غير مطابق.',
        ], [
            'current_password' => 'كلمة المرور الحالية',
            'password' => 'كلمة المرور الجديدة',
        ]);

        $request->user()->update(['password' => Hash::make($validated['password'])]);

        return back()->with('success', 'تم تغيير كلمة المرور.');
    }

    public function updateGeneral(Request $request): RedirectResponse
    {
        $tenant = $request->user()->tenant;

        $validated = $request->validate([
            'locale' => ['required', 'in:ar,en,ku'],
            'rows_per_page' => ['required', 'integer', 'min:5', 'max:100'],
            'due_reminder_days' => ['required', 'integer', 'min:0', 'max:60'],
            'overdue_grace_days' => ['required', 'integer', 'min:0', 'max:60'],
        ], [
            'required' => 'حقل :attribute مطلوب.',
            'integer' => 'يجب أن يكون :attribute رقماً صحيحاً.',
            'min' => 'قيمة :attribute أقل من الحد المسموح.',
            'max' => 'قيمة :attribute أكبر من الحد المسموح.',
            'in' => 'قيمة :attribute غير صالحة.',
        ]);

        $tenant->update($validated);

        return back()->with('success', 'تم حفظ الإعدادات.');
    }

    public function updateTheme(Request $request): RedirectResponse
    {
        $validated = $request->validate(['theme' => ['required', 'in:light,dark']]);

        $request->user()->tenant->update($validated);

        return back();
    }

    public function redeemCode(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'code' => ['required', 'string'],
        ], [
            'required' => 'حقل :attribute مطلوب.',
        ], ['code' => 'الكود']);

        $tenant = $request->user()->tenant;

        $activationCode = ActivationCode::where('code', $validated['code'])->first();

        if (! $activationCode || $activationCode->assigned_tenant_id !== $tenant->id) {
            throw ValidationException::withMessages(['code' => 'هذا الكود غير صالح لحسابك.']);
        }

        if ($activationCode->status !== 'unused') {
            throw ValidationException::withMessages(['code' => 'هذا الكود مستخدَم أو ملغى مسبقاً.']);
        }

        if ($activationCode->isExpired()) {
            $activationCode->update(['status' => 'expired']);
            throw ValidationException::withMessages(['code' => 'انتهت صلاحية هذا الكود.']);
        }

        $plan = $activationCode->plan;

        $tenant->update([
            'plan_id' => $plan->id,
            'status' => 'active',
            'subscription_ends_at' => now()->addDays($plan->duration_days ?? 30),
        ]);

        $activationCode->update([
            'status' => 'used',
            'redeemed_by_tenant_id' => $tenant->id,
            'redeemed_at' => now(),
        ]);

        PlatformNotification::notify('code_used', "تم استخدام كود التفعيل من قبل {$tenant->name} لتفعيل باقة \"{$plan->name}\"", route('platform.subscribers.show', $tenant->id, absolute: false));

        return back()->with('success', "تم تفعيل باقة \"{$plan->name}\" بنجاح.");
    }

    public function requestPlan(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'plan_id' => ['required', 'exists:plans,id'],
        ], [
            'required' => 'حقل :attribute مطلوب.',
            'exists' => ':attribute غير موجودة.',
        ], ['plan_id' => 'الباقة']);

        $tenant = $request->user()->tenant;
        $plan = Plan::findOrFail($validated['plan_id']);

        PlanUpgradeRequest::create([
            'tenant_id' => $tenant->id,
            'plan_id' => $plan->id,
            'status' => 'pending',
        ]);

        PlatformNotification::notify('plan_request', "طلب باقة جديد من {$tenant->name}: \"{$plan->name}\"", route('platform.plan-requests.index', absolute: false));

        return back()->with('success', "تم إرسال طلب باقة \"{$plan->name}\" — بانتظار موافقة مدير المنصة.");
    }

    public function sendAboutMessage(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'email' => ['required', 'email', 'max:255'],
            'message' => ['required', 'string', 'max:2000'],
        ], [
            'required' => 'حقل :attribute مطلوب.',
            'email' => 'صيغة :attribute غير صحيحة.',
            'max' => 'قيمة :attribute أطول من الحد المسموح.',
        ], [
            'email' => 'البريد الإلكتروني',
            'message' => 'نص الرسالة',
        ]);

        $companyEmail = PlatformSetting::get('about_email');

        if (! $companyEmail) {
            throw ValidationException::withMessages(['message' => 'التواصل غير متاح حالياً.']);
        }

        Mail::to($companyEmail)->send(new AboutContactMessage(
            $validated['email'],
            $validated['message'],
            $request->user()->tenant->name,
        ));

        return back()->with('success', 'تم إرسال رسالتك بنجاح.');
    }
}
