<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

#[Fillable(['tenant_id', 'causer_id', 'action', 'description'])]
class ActivityLog extends Model
{
    use SoftDeletes;

    protected function casts(): array
    {
        return [
            'created_at' => 'datetime:Y-m-d H:i',
        ];
    }

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    public function causer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'causer_id');
    }

    public static function record(string $action, ?string $description = null, ?int $tenantId = null): self
    {
        return static::create([
            'tenant_id' => $tenantId ?? auth()->user()?->tenant_id,
            'causer_id' => auth()->id(),
            'action' => $action,
            'description' => $description,
        ]);
    }
}
