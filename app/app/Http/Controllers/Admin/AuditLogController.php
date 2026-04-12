<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\TrainingAudit;
use App\Models\Training;
use Illuminate\Http\Request;

class AuditLogController extends Controller
{
    protected function requireAdmin(Request $request): void
    {
        if (! $request->user() || ! $request->user()->is_admin) {
            abort(403);
        }
    }

    /**
     * Display all audit logs
     */
    public function index(Request $request)
    {
        $this->requireAdmin($request);

        $query = TrainingAudit::query()
            ->with(['training:id,title,start_at', 'performer:id,name'])
            ->orderByDesc('created_at');

        // Filter by training
        if ($trainingId = $request->query('training_id')) {
            $query->where('training_id', $trainingId);
        }

        // Filter by action
        if ($action = $request->query('action')) {
            $query->where('action', $action);
        }

        // Filter by user (who performed the action)
        if ($userId = $request->query('user_id')) {
            $query->where('performed_by_user_id', $userId);
        }

        // Date range filter
        if ($fromDate = $request->query('from_date')) {
            $query->whereDate('created_at', '>=', $fromDate);
        }
        if ($toDate = $request->query('to_date')) {
            $query->whereDate('created_at', '<=', $toDate);
        }

        $audits = $query->paginate(25)->withQueryString();

        // Get unique actions for filter dropdown
        $actions = TrainingAudit::query()
            ->distinct()
            ->pluck('action')
            ->sort()
            ->toArray();

        return view('admin.audit-logs.index', compact('audits', 'actions'));
    }

    /**
     * Display audit logs for a specific training
     */
    public function training(Request $request, Training $training)
    {
        $this->requireAdmin($request);

        $audits = TrainingAudit::query()
            ->where('training_id', $training->id)
            ->with('performer:id,name')
            ->orderByDesc('created_at')
            ->paginate(50);

        return view('admin.audit-logs.training', compact('training', 'audits'));
    }

    /**
     * Display detailed view of a single audit record
     */
    public function show(Request $request, TrainingAudit $audit)
    {
        $this->requireAdmin($request);

        $audit->load(['training:id,title,start_at,end_at,price', 'performer:id,name,email']);

        return view('admin.audit-logs.show', compact('audit'));
    }
}
