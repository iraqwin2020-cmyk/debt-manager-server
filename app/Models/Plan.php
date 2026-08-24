<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

#[Fillable(['name', 'price', 'duration_days', 'max_debtors', 'max_devices', 'is_default_trial'])]
class Plan extends Model
{
    use SoftDeletes;

    protected function casts(): array
    {
        return [
            'is_default_trial' => 'boolean',
        ];
    }

    public function tenants(): HasMany
    {
        return $this->hasMany(Tenant::class);
    }

    public static function defaultTrial(): self
    {
        return static::where('is_default_trial', true)->firstOrFail();
    }
}
