<?php

namespace App\Models;

use App\Models\Concerns\BelongsToTenant;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Carbon;

#[Fillable([
    'tenant_id', 'debtor_id', 'amount', 'currency', 'description', 'due_date',
    'payment_type', 'receipt_number', 'paid_amount',
])]
class Debt extends Model
{
    use BelongsToTenant, SoftDeletes;

    protected function casts(): array
    {
        return [
            'due_date' => 'date:Y-m-d',
        ];
    }

    public function debtor(): BelongsTo
    {
        return $this->belongsTo(Debtor::class);
    }

    public function guarantors(): BelongsToMany
    {
        return $this->belongsToMany(Guarantor::class, 'debt_guarantor');
    }

    public function installments(): HasMany
    {
        return $this->hasMany(DebtInstallment::class);
    }

    public function payments(): HasMany
    {
        return $this->hasMany(Payment::class);
    }

    public function remaining(): int
    {
        return (int) $this->amount - (int) $this->paid_amount;
    }

    public function isFullyPaid(): bool
    {
        return $this->paid_amount >= $this->amount;
    }

    /** متأخر = تجاوز تاريخ الاستحقاق + فترة السماح، ولم يُسدَّد بالكامل (لدفعة واحدة فقط؛ الأقساط تُحسب لكل قسط) */
    public function isOverdue(): bool
    {
        if ($this->payment_type !== 'lump_sum' || ! $this->due_date || $this->isFullyPaid()) {
            return false;
        }

        $graceDays = $this->tenant->overdue_grace_days ?? 0;

        return Carbon::parse($this->due_date)->addDays($graceDays)->isPast();
    }
}
