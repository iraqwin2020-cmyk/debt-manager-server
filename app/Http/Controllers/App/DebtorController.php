<?php

namespace App\Http\Controllers\App;

use App\Http\Controllers\Controller;
use App\Http\Requests\App\DebtorRequest;
use App\Models\ActivityLog;
use App\Models\Debtor;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;
use Inertia\Response;

class DebtorController extends Controller
{
    public function index(Request $request): Response
    {
        return $this->list($request, favoritesOnly: false);
    }

    public function favorites(Request $request): Response
    {
        return $this->list($request, favoritesOnly: true);
    }

    protected function list(Request $request, bool $favoritesOnly): Response
    {
        $tenant = $request->user()->tenant;

        $query = Debtor::query()
            ->when($request->filled('q'), function ($q) use ($request) {
                $term = $request->string('q');
                $q->where(function ($q) use ($term) {
                    $q->where('name', 'like', "%{$term}%")->orWhere('phone', 'like', "%{$term}%");
                });
            })
            ->when($favoritesOnly, fn ($q) => $q->where('is_favorite', true))
            ->when($request->input('filter') === 'favorites', fn ($q) => $q->where('is_favorite', true))
            ->orderBy('name');

        $debtors = $query->paginate($tenant->rows_per_page)->withQueryString();

        $debtors->getCollection()->transform(function (Debtor $debtor) {
            $debtor->remaining = $debtor->remainingByCurrency();

            return $debtor;
        });

        return Inertia::render(
            $favoritesOnly ? 'App/Debtors/Favorites' : 'App/Debtors/Index',
            [
                'debtors' => $debtors,
                'filters' => (object) $request->only('q', 'filter'),
            ]
        );
    }

    public function show(Debtor $debtor): Response
    {
        return Inertia::render('App/Debtors/Show', [
            'debtor' => $debtor,
            'hasIdDocument' => (bool) $debtor->id_document_image,
            'guarantors' => $debtor->guarantors(),
            'debts' => $debtor->debts()->latest()->get(),
        ]);
    }

    public function showDocument(Debtor $debtor)
    {
        abort_unless($debtor->id_document_image, 404);

        return Storage::disk('local')->response($debtor->id_document_image);
    }

    public function create(): Response
    {
        return Inertia::render('App/Debtors/Create');
    }

    public function store(DebtorRequest $request): RedirectResponse
    {
        $data = $request->validated();

        if ($request->hasFile('id_document_image')) {
            $data['id_document_image'] = $request->file('id_document_image')->store('id-documents', 'local');
        }

        $debtor = Debtor::create($data);

        ActivityLog::record('debtor_created', "إضافة عميل جديد: {$debtor->name}");

        return redirect()->route('app.debtors.index')->with('success', 'تمت إضافة العميل بنجاح.');
    }

    public function edit(Debtor $debtor): Response
    {
        return Inertia::render('App/Debtors/Edit', ['debtor' => $debtor]);
    }

    public function update(DebtorRequest $request, Debtor $debtor): RedirectResponse
    {
        $data = $request->validated();

        if ($request->hasFile('id_document_image')) {
            if ($debtor->id_document_image) {
                Storage::disk('local')->delete($debtor->id_document_image);
            }
            $data['id_document_image'] = $request->file('id_document_image')->store('id-documents', 'local');
        }

        $debtor->update($data);

        return redirect()->route('app.debtors.index')->with('success', 'تم تحديث بيانات العميل.');
    }

    public function destroy(Debtor $debtor): RedirectResponse
    {
        if ($debtor->hasOutstandingDebt()) {
            return back()->with('error', 'لا يمكن حذف عميل عليه دين قائم غير مسدَّد بالكامل.');
        }

        $debtor->delete();

        return back()->with('success', 'تم نقل العميل لسلة المحذوفات.');
    }

    public function toggleFavorite(Debtor $debtor): RedirectResponse
    {
        $debtor->update(['is_favorite' => ! $debtor->is_favorite]);

        return back();
    }
}
