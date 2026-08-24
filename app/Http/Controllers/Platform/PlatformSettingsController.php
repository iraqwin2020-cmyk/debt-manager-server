<?php

namespace App\Http\Controllers\Platform;

use App\Http\Controllers\Controller;
use App\Models\PlatformSetting;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Inertia\Inertia;
use Inertia\Response;

class PlatformSettingsController extends Controller
{
    public function edit(Request $request): Response
    {
        return Inertia::render('Platform/Settings/Edit', [
            'user' => $request->user(),
            'settings' => [
                'trial_days' => (int) PlatformSetting::get('trial_days', 14),
                'country_code' => PlatformSetting::get('country_code', '964'),
                'theme' => PlatformSetting::get('theme', 'light'),
                'rows_per_page' => (int) PlatformSetting::get('rows_per_page', 20),
            ],
            'about' => [
                'about_description' => PlatformSetting::get('about_description', ''),
                'about_whatsapp' => PlatformSetting::get('about_whatsapp', ''),
                'about_email' => PlatformSetting::get('about_email', ''),
                'about_company_name' => PlatformSetting::get('about_company_name', ''),
            ],
        ]);
    }

    public function updateAccount(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
        ], [
            'required' => 'حقل :attribute مطلوب.',
        ], ['name' => 'الاسم']);

        $request->user()->update($validated);

        return back()->with('success', 'تم تحديث بيانات الحساب.');
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
        $validated = $request->validate([
            'trial_days' => ['required', 'integer', 'min:1', 'max:365'],
            'country_code' => ['required', 'digits_between:1,4'],
            'rows_per_page' => ['required', 'integer', 'min:5', 'max:100'],
        ], [
            'required' => 'حقل :attribute مطلوب.',
            'integer' => 'يجب أن يكون :attribute رقماً صحيحاً.',
            'digits_between' => 'قيمة :attribute غير صالحة.',
            'min' => 'قيمة :attribute أقل من الحد المسموح.',
            'max' => 'قيمة :attribute أكبر من الحد المسموح.',
        ]);

        foreach ($validated as $key => $value) {
            PlatformSetting::set($key, (string) $value);
        }

        return back()->with('success', 'تم حفظ الإعدادات.');
    }

    public function updateTheme(Request $request): RedirectResponse
    {
        $validated = $request->validate(['theme' => ['required', 'in:light,dark']]);

        PlatformSetting::set('theme', $validated['theme']);

        return back();
    }

    public function updateAbout(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'about_description' => ['required', 'string', 'max:2000'],
            'about_whatsapp' => ['required', 'string', 'max:20'],
            'about_email' => ['required', 'email', 'max:255'],
            'about_company_name' => ['required', 'string', 'max:255'],
        ], [
            'required' => 'حقل :attribute مطلوب.',
            'email' => 'صيغة :attribute غير صحيحة.',
            'max' => 'قيمة :attribute أطول من الحد المسموح.',
        ], [
            'about_description' => 'شرح التطبيق',
            'about_whatsapp' => 'رقم واتساب',
            'about_email' => 'البريد الإلكتروني',
            'about_company_name' => 'اسم الشركة',
        ]);

        foreach ($validated as $key => $value) {
            PlatformSetting::set($key, $value);
        }

        return back()->with('success', 'تم حفظ محتوى تبويب "حول".');
    }
}
