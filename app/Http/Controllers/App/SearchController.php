<?php

namespace App\Http\Controllers\App;

use App\Http\Controllers\Controller;
use App\Models\Creditor;
use App\Models\Debtor;
use App\Models\Guarantor;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * بحث سريع للاستخدام بشريط البحث/الإضافة الموحّد بنماذج الدين — يطابق الاسم أو الهاتف.
 */
class SearchController extends Controller
{
    public function people(Request $request): JsonResponse
    {
        $type = $request->string('type')->toString();
        $term = $request->string('q')->toString();

        $model = match ($type) {
            'debtor' => Debtor::class,
            'guarantor' => Guarantor::class,
            'creditor' => Creditor::class,
            default => null,
        };

        if (! $model || strlen($term) < 1) {
            return response()->json([]);
        }

        $results = $model::query()
            ->where(fn ($q) => $q->where('name', 'like', "%{$term}%")->orWhere('phone', 'like', "%{$term}%"))
            ->limit(10)
            ->get(['id', 'name', 'phone']);

        return response()->json($results);
    }
}
