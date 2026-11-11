<?php

namespace App\Http\Controllers;

use App\Models\Support;
use App\Models\Technician;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;
use Illuminate\Validation\Rule;

class TechnicianDashboardController extends Controller
{
    public function index(Request $request)
    {
        $filters = $request->validate([
            'status' => ['nullable', Rule::in(array_merge(['all'], HelpdeskController::STATUSES))],
            'technician' => ['nullable', 'string', 'max:30'],
            'priority' => ['nullable', Rule::in(Support::PRIORITIES)],
            'q' => 'nullable|string|max:150',
        ]);
        $status = $filters['status'] ?? 'Pending';
        $priorityEnabled = Schema::hasColumn('supports', 'priority');
        $query = Support::with(['task', 'technician', 'employee' => fn ($employee) => $employee->withTrashed()->with('department')]);
        if ($status !== 'all') { $query->where('status', $status); }
        if (($filters['technician'] ?? '') === 'unassigned') {
            $query->whereNull('technician_id');
        } elseif (! empty($filters['technician'])) {
            $request->validate(['technician' => 'integer|exists:technicians,id']);
            $query->where('technician_id', $filters['technician']);
        }
        if ($priorityEnabled && ! empty($filters['priority'])) { $query->where('priority', $filters['priority']); }
        if (! empty($filters['q'])) {
            $term = '%'.$filters['q'].'%';
            $query->where(fn ($search) => $search->where('id', $filters['q'])
                ->orWhere('support_specifics', 'like', $term)
                ->orWhereHas('task', fn ($task) => $task->where('name', 'like', $term))
                ->orWhereHas('employee', fn ($employee) => $employee->withTrashed()
                    ->where('name', 'like', $term)->orWhereHas('department', fn ($department) => $department->where('name', 'like', $term))));
        }
        if ($status === 'all') {
            $query->orderByRaw("CASE WHEN status = 'Pending' THEN 0 ELSE 1 END");
        }
        if ($priorityEnabled) {
            $query->orderByRaw("CASE priority WHEN 'Urgent' THEN 0 WHEN 'High' THEN 1 WHEN 'Normal' THEN 2 ELSE 3 END");
        }
        return view('helpdesk.technician.index', [
            'tickets' => $query->orderBy('created_at')->orderBy('id')->paginate(12)->withQueryString(),
            'technicians' => Technician::orderBy('name')->get(),
            'statuses' => HelpdeskController::STATUSES, 'priorities' => Support::PRIORITIES,
            'priorityEnabled' => $priorityEnabled, 'activeStatus' => $status,
            'pendingCount' => Support::where('status', 'Pending')->count(),
            'unassignedCount' => Support::where('status', 'Pending')->whereNull('technician_id')->count(),
        ]);
    }
}
