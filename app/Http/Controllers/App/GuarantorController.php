<?php

namespace App\Http\Controllers\App;

use App\Http\Controllers\Controller;
use App\Http\Requests\App\GuarantorRequest;
use App\Models\ActivityLog;
use App\Models\Guarantor;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;
use Inertia\Response;

class GuarantorController extends Controller
{
    public function index(Request $request): Response
    {
        $tenant = $request->user()->tenant;

        $guarantors = Guarantor::query()
            ->when($request->filled('q'), function ($q) use ($request) {
                $term = $request->string('q');
                $q->where(fn ($q) => $q->where('name', 'like', "%{$term}%")->orWhere('phone', 'like', "%{$term}%"));
            })
            ->withCount('debts as debtors_count')
            ->orderBy('name')
            ->paginate($tenant->rows_per_page)
            ->withQueryString();

        return Inertia::render('App/Guarantors/Index', [
            'guarantors' => $guarantors,
            'filters' => (object) $request->only('q'),
        ]);
    }

    public function show(Guarantor $guarantor): Response
    {
        $debts = $guarantor->debts()->with('debtor:id,name,phone')->latest()->get();

        return Inertia::render('App/Guarantors/Show', [
            'guarantor' => $guarantor,
            'hasIdDocument' => (bool) $guarantor->id_document_image,
            'debts' => $debts,
        ]);
    }

    public function showDocument(Guarantor $guarantor)
    {
        abort_unless($guarantor->id_document_image, 404);

        return Storage::disk('local')->response($guarantor->id_document_image);
    }

    public function create(): Response
    {
        return Inertia::render('App/Guarantors/Create');
    }

    public function store(GuarantorRequest $request): RedirectResponse
    {
        $data = $request->validated();

        if ($request->hasFile('id_document_image')) {
            $data['id_document_image'] = $request->file('id_document_image')->store('id-documents', 'local');
        }

        $guarantor = Guarantor::create($data);

        ActivityLog::record('guarantor_created', "إضافة كفيل جديد: {$guarantor->name}");

        return redirect()->route('app.guarantors.index')->with('success', 'تمت إضافة الكفيل بنجاح.');
    }

    public function edit(Guarantor $guarantor): Response
    {
        return Inertia::render('App/Guarantors/Edit', ['guarantor' => $guarantor]);
    }

    public function update(GuarantorRequest $request, Guarantor $guarantor): RedirectResponse
    {
        $data = $request->validated();

        if ($request->hasFile('id_document_image')) {
            if ($guarantor->id_document_image) {
                Storage::disk('local')->delete($guarantor->id_document_image);
            }
            $data['id_document_image'] = $request->file('id_document_image')->store('id-documents', 'local');
        }

        $guarantor->update($data);

        return redirect()->route('app.guarantors.index')->with('success', 'تم تحديث بيانات الكفيل.');
    }

    public function destroy(Guarantor $guarantor): RedirectResponse
    {
        if ($guarantor->guaranteesOutstandingDebt()) {
            return back()->with('error', 'لا يمكن حذف كفيل يتكفّل حالياً بعميل عليه دين قائم.');
        }

        $guarantor->delete();

        return back()->with('success', 'تم نقل الكفيل لسلة المحذوفات.');
    }
}
