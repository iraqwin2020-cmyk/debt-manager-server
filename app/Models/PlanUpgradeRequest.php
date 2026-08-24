<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['tenant_id', 'plan_id', 'status', 'reviewed_by', 'reviewed_at', 'note'])]
class PlanUpgradeRequest extends Model
{
    protected $table = 'plan_requests';

    protected function casts(): array
    {
        return [
            'created_at' => 'datetime:Y-m-d H:i',
            'reviewed_at' => 'date:Y-m-d',
        ];
    }

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    public function plan(): BelongsTo
    {
        return $this->belongsTo(Plan::class);
    }

    public function reviewer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reviewed_by');
    }
}
