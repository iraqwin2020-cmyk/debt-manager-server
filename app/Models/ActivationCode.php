<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

#[Fillable(['code', 'plan_id', 'assigned_tenant_id', 'expires_at', 'created_by', 'redeemed_by_tenant_id', 'redeemed_at', 'status'])]
class ActivationCode extends Model
{
    use SoftDeletes;

    protected function casts(): array
    {
        return [
            'expires_at' => 'date:Y-m-d',
            'redeemed_at' => 'date:Y-m-d',
        ];
    }

    public function plan(): BelongsTo
    {
        return $this->belongsTo(Plan::class);
    }

    public function assignedTenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class, 'assigned_tenant_id');
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function isExpired(): bool
    {
        return $this->expires_at->isPast();
    }
}
