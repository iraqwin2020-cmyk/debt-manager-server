<?php

namespace App\Models;

use App\Models\Concerns\BelongsToTenant;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

#[Fillable(['tenant_id', 'my_debt_id', 'creditor_id', 'installment_id', 'amount', 'paid_at', 'note'])]
class MyDebtPayment extends Model
{
    use BelongsToTenant, SoftDeletes;

    protected function casts(): array
    {
        return [
            'paid_at' => 'date:Y-m-d',
        ];
    }

    public function myDebt(): BelongsTo
    {
        return $this->belongsTo(MyDebt::class);
    }

    public function creditor(): BelongsTo
    {
        return $this->belongsTo(Creditor::class);
    }

    public function installment(): BelongsTo
    {
        return $this->belongsTo(MyDebtInstallment::class, 'installment_id');
    }
}
