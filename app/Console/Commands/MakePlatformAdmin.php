<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

/**
 * الطريقة الوحيدة لإنشاء مدير مشروع — لا رابط تسجيل عام له إطلاقاً (معيار الهيكلية).
 */
class MakePlatformAdmin extends Command
{
    protected $signature = 'app:make-platform-admin';

    protected $description = 'إنشاء حساب مدير مشروع جديد (يدوياً على السيرفر فقط)';

    public function handle(): int
    {
        $name = $this->ask('اسم مدير المشروع');
        $phone = $this->ask('رقم الهاتف (11 رقم)');
        $password = $this->secret('كلمة المرور');

        $validator = Validator::make(
            ['name' => $name, 'phone' => $phone, 'password' => $password],
            [
                'name' => ['required', 'string', 'max:255'],
                'phone' => ['required', 'digits:11', 'unique:users,phone'],
                'password' => ['required', 'string', 'min:8'],
            ],
            [
                'required' => 'حقل :attribute مطلوب.',
                'digits' => 'يجب أن يتكون :attribute من 11 رقماً بالضبط.',
                'unique' => ':attribute مستخدم مسبقاً.',
                'min' => 'يجب ألا يقل :attribute عن :min أحرف.',
            ],
            [
                'name' => 'الاسم',
                'phone' => 'رقم الهاتف',
                'password' => 'كلمة المرور',
            ]
        );

        if ($validator->fails()) {
            foreach ($validator->errors()->all() as $error) {
                $this->error($error);
            }

            return self::FAILURE;
        }

        User::create([
            'tenant_id' => null,
            'name' => $name,
            'phone' => $phone,
            'password' => Hash::make($password),
            'role' => 'platform_admin',
        ]);

        $this->info('تم إنشاء حساب مدير المشروع بنجاح.');

        return self::SUCCESS;
    }
}
