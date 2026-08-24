<?php

namespace App\Models;

use App\Models\Concerns\BelongsToTenant;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

#[Fillable(['tenant_id', 'my_debt_id', 'seq_number', 'amount', 'due_date', 'paid_amount'])]
class MyDebtInstallment extends Model
{
    use BelongsToTenant, SoftDeletes;

    protected function casts(): array
    {
        return [
            'due_date' => 'date:Y-m-d',
        ];
    }

    public function myDebt(): BelongsTo
    {
        return $this->belongsTo(MyDebt::class);
    }

    public function payments(): HasMany
    {
        return $this->hasMany(MyDebtPayment::class, 'installment_id');
    }

    public function isFullyPaid(): bool
    {
        return $this->paid_amount >= $this->amount;
    }
}
