<?php

namespace App\Http\Controllers\Platform;

use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class ActivityLogController extends Controller
{
    public function index(Request $request): Response
    {
        $logs = ActivityLog::query()
            ->with('tenant:id,name')
            ->when($request->filled('q'), function ($q) use ($request) {
                $term = $request->string('q');
                $q->whereHas('tenant', fn ($q) => $q->where('name', 'like', "%{$term}%"));
            })
            ->when($request->filled('action'), fn ($q) => $q->where('action', $request->input('action')))
            ->latest()
            ->paginate(30)
            ->withQueryString();

        return Inertia::render('Platform/ActivityLogs/Index', [
            'logs' => $logs,
            'actions' => ActivityLog::query()->distinct()->orderBy('action')->pluck('action'),
            'filters' => (object) $request->only('q', 'action'),
        ]);
    }
}
