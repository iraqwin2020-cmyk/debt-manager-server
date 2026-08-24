<?php

namespace App\Models;

use App\Models\Concerns\BelongsToTenant;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Carbon;

#[Fillable(['tenant_id', 'debt_id', 'seq_number', 'amount', 'due_date', 'paid_amount'])]
class DebtInstallment extends Model
{
    use BelongsToTenant, SoftDeletes;

    protected function casts(): array
    {
        return [
            'due_date' => 'date:Y-m-d',
        ];
    }

    public function debt(): BelongsTo
    {
        return $this->belongsTo(Debt::class);
    }

    public function payments(): HasMany
    {
        return $this->hasMany(Payment::class, 'installment_id');
    }

    public function isFullyPaid(): bool
    {
        return $this->paid_amount >= $this->amount;
    }

    /** التأخر يُحسب لكل قسط على حدة من تاريخ استحقاقه الخاص */
    public function isOverdue(): bool
    {
        if ($this->isFullyPaid()) {
            return false;
        }

        $graceDays = $this->tenant->overdue_grace_days ?? 0;

        return Carbon::parse($this->due_date)->addDays($graceDays)->isPast();
    }
}
