<?php

namespace App\Models;

use App\Models\Concerns\BelongsToTenant;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

#[Fillable(['tenant_id', 'name', 'phone', 'address', 'note'])]
class Creditor extends Model
{
    use BelongsToTenant, SoftDeletes;

    public function myDebts(): HasMany
    {
        return $this->hasMany(MyDebt::class);
    }

    public function hasOutstandingDebt(): bool
    {
        return $this->myDebts()->whereColumn('paid_amount', '<', 'amount')->exists();
    }
}
