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
            'documentCount' => count($debtor->id_document_images ?? []),
            'guarantors' => $debtor->guarantors(),
            'debts' => $debtor->debts()->latest()->get(),
        ]);
    }

    public function showDocument(Debtor $debtor, int $index)
    {
        $paths = $debtor->id_document_images ?? [];
        abort_unless(array_key_exists($index, $paths), 404);

        return Storage::disk('local')->response($paths[$index]);
    }

    public function create(): Response
    {
        return Inertia::render('App/Debtors/Create');
    }

    public function store(DebtorRequest $request): RedirectResponse
    {
        $data = $request->safe()->except(['new_images', 'existing_images']);

        $data['id_document_images'] = array_map(
            fn ($file) => $file->store('id-documents', 'local'),
            $request->file('new_images', [])
        );

        $debtor = Debtor::create($data);

        ActivityLog::record('debtor_created', "إضافة عميل جديد: {$debtor->name}");

        return redirect()->route('app.debtors.index')->with('success', 'تمت إضافة العميل بنجاح.');
    }

    public function edit(Debtor $debtor): Response
    {
        return Inertia::render('App/Debtors/Edit', [
            'debtor' => $debtor,
            'documentCount' => count($debtor->id_document_images ?? []),
        ]);
    }

    public function update(DebtorRequest $request, Debtor $debtor): RedirectResponse
    {
        $data = $request->safe()->except(['new_images', 'keep_indexes']);

        $keepIndexes = array_map('intval', $request->input('keep_indexes', []));
        $oldPaths = $debtor->id_document_images ?? [];
        $kept = [];
        foreach ($oldPaths as $i => $path) {
            if (in_array($i, $keepIndexes, true)) {
                $kept[] = $path;
            } else {
                Storage::disk('local')->delete($path);
            }
        }

        $newPaths = array_map(
            fn ($file) => $file->store('id-documents', 'local'),
            $request->file('new_images', [])
        );

        $data['id_document_images'] = array_values([...$kept, ...$newPaths]);

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
