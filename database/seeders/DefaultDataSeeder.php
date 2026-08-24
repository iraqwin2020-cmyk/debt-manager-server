<?php

namespace Database\Seeders;

use App\Models\Plan;
use App\Models\PlatformSetting;
use Illuminate\Database\Seeder;

/**
 * بيانات أساسية لازمة لعمل النظام من أول يوم — باقة التجربة المجانية الافتراضية
 * (لا يبقى plan_id فارغاً أبداً لأي مشترك) وإعداد مدة التجربة العام.
 */
class DefaultDataSeeder extends Seeder
{
    public function run(): void
    {
        Plan::query()->updateOrCreate(
            ['is_default_trial' => true],
            [
                'name' => 'تجربة مجانية',
                'price' => 0,
                'duration_days' => null,
                'max_debtors' => 15,
                'max_devices' => 1,
                'is_default_trial' => true,
            ]
        );

        PlatformSetting::set('trial_days', 14);
    }
}
