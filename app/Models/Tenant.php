<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

#[Fillable([
    'name', 'phone', 'logo', 'plan_id', 'trial_ends_at', 'subscription_ends_at',
    'status', 'type', 'locale', 'theme', 'rows_per_page',
    'due_reminder_days', 'overdue_grace_days',
    'debt_receipt_start_number', 'next_debt_receipt_number',
    'payment_receipt_start_number', 'next_payment_receipt_number',
])]
class Tenant extends Model
{
    use SoftDeletes;

    protected function casts(): array
    {
        return [
            'trial_ends_at' => 'date:Y-m-d',
            'subscription_ends_at' => 'date:Y-m-d',
        ];
    }

    public function plan(): BelongsTo
    {
        return $this->belongsTo(Plan::class);
    }

    public function users(): HasMany
    {
        return $this->hasMany(User::class);
    }

    public function devices(): HasMany
    {
        return $this->hasMany(Device::class);
    }

    public function debtors(): HasMany
    {
        return $this->hasMany(Debtor::class);
    }

    public function guarantors(): HasMany
    {
        return $this->hasMany(Guarantor::class);
    }

    public function debts(): HasMany
    {
        return $this->hasMany(Debt::class);
    }

    public function creditors(): HasMany
    {
        return $this->hasMany(Creditor::class);
    }

    public function myDebts(): HasMany
    {
        return $this->hasMany(MyDebt::class);
    }

    public function planRequests(): HasMany
    {
        return $this->hasMany(PlanUpgradeRequest::class);
    }

    /** هل انتهت فترة التجربة/الاشتراك ولا يوجد اشتراك فعّال؟ */
    public function isExpired(): bool
    {
        return $this->status === 'expired';
    }

    public function isActive(): bool
    {
        return in_array($this->status, ['active', 'trial'], true);
    }
}
