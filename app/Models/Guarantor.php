<?php

namespace App\Models;

use App\Models\Concerns\BelongsToTenant;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\SoftDeletes;

#[Fillable(['tenant_id', 'name', 'phone', 'address', 'id_document_image', 'note'])]
#[Hidden(['id_document_image'])]
class Guarantor extends Model
{
    use BelongsToTenant, SoftDeletes;

    /** الديون التي يتكفّل بها هذا الكفيل — الكفيل نفسه ليس عليه دين */
    public function debts(): BelongsToMany
    {
        return $this->belongsToMany(Debt::class, 'debt_guarantor');
    }

    /** هل يتكفّل حالياً بعميل عليه دين قائم؟ — يمنع الحذف */
    public function guaranteesOutstandingDebt(): bool
    {
        return $this->debts()->whereColumn('paid_amount', '<', 'amount')->exists();
    }
}
