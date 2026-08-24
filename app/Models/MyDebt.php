<?php

namespace App\Models;

use App\Models\Concerns\BelongsToTenant;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

#[Fillable(['tenant_id', 'creditor_id', 'amount', 'currency', 'description', 'due_date', 'payment_type', 'paid_amount'])]
class MyDebt extends Model
{
    use BelongsToTenant, SoftDeletes;

    protected function casts(): array
    {
        return [
            'due_date' => 'date:Y-m-d',
        ];
    }

    public function creditor(): BelongsTo
    {
        return $this->belongsTo(Creditor::class);
    }

    public function installments(): HasMany
    {
        return $this->hasMany(MyDebtInstallment::class);
    }

    public function payments(): HasMany
    {
        return $this->hasMany(MyDebtPayment::class);
    }

    public function remaining(): int
    {
        return (int) $this->amount - (int) $this->paid_amount;
    }

    public function isFullyPaid(): bool
    {
        return $this->paid_amount >= $this->amount;
    }
}
