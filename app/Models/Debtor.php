<?php

namespace App\Models;

use App\Models\Concerns\BelongsToTenant;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Collection;

#[Fillable(['tenant_id', 'name', 'phone', 'address', 'id_document_images', 'is_favorite', 'note'])]
#[Hidden(['id_document_images'])]
class Debtor extends Model
{
    use BelongsToTenant, SoftDeletes;

    protected function casts(): array
    {
        return [
            'is_favorite' => 'boolean',
            'id_document_images' => 'array',
        ];
    }

    public function debts(): HasMany
    {
        return $this->hasMany(Debt::class);
    }

    public function payments(): HasMany
    {
        return $this->hasMany(Payment::class);
    }

    /** الكفلاء المرتبطون بهذا العميل عبر كل ديونه (قد يختلف الكفيل من دين لآخر) */
    public function guarantors(): Collection
    {
        return Guarantor::query()
            ->whereHas('debts', fn ($query) => $query->where('debtor_id', $this->id))
            ->get();
    }

    /** إجمالي الدين المتبقي على هذا العميل، مجموعاً حسب العملة */
    public function remainingByCurrency(): array
    {
        return $this->debts()
            ->selectRaw('currency, SUM(amount - paid_amount) as remaining')
            ->groupBy('currency')
            ->pluck('remaining', 'currency')
            ->toArray();
    }

    /** هل عليه دين قائم (غير مسدَّد بالكامل)؟ — يمنع الحذف */
    public function hasOutstandingDebt(): bool
    {
        return $this->debts()->whereColumn('paid_amount', '<', 'amount')->exists();
    }
}
