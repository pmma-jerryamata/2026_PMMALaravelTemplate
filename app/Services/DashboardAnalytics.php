<?php

namespace App\Services;

use App\Http\Controllers\HelpdeskController;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class DashboardAnalytics
{
    public function summarize(Request $request): array
    {
        $filters = $request->validate([
            'analytics_period' => ['nullable', Rule::in(['all', 'today', '7days', '30days', 'month', 'year', 'custom'])],
            'analytics_from' => 'nullable|required_if:analytics_period,custom|date_format:Y-m-d',
            'analytics_to' => 'nullable|required_if:analytics_period,custom|date_format:Y-m-d|after_or_equal:analytics_from',
            'analytics_status' => ['nullable', Rule::in(HelpdeskController::STATUSES)],
            'analytics_task_id' => 'nullable|integer|exists:tasks,id',
            'analytics_department_id' => 'nullable|integer|exists:departments,id',
            'analytics_technician_id' => 'nullable|integer|exists:technicians,id',
        ]);
        $period = $filters['analytics_period'] ?? 'all';
        $today = Carbon::today('Asia/Manila');
        $from = match ($period) {
            'today' => $today->copy(),
            '7days' => $today->copy()->subDays(6),
            '30days' => $today->copy()->subDays(29),
            'month' => $today->copy()->startOfMonth(),
            'year' => $today->copy()->startOfYear(),
            'custom' => Carbon::createFromFormat('!Y-m-d', $filters['analytics_from'], 'Asia/Manila'),
            default => null,
        };
        $to = $period === 'custom'
            ? Carbon::createFromFormat('!Y-m-d', $filters['analytics_to'], 'Asia/Manila')
            : ($from ? $today->copy() : null);

        $query = DB::table('supports')
            // Historical requests still belong to deactivated employees.
            ->leftJoin('employees', 'employees.id', '=', 'supports.employee_id')
            ->leftJoin('departments', 'departments.id', '=', 'employees.department_id')
            ->leftJoin('tasks', 'tasks.id', '=', 'supports.task_id');
        if ($from) {
            $query->where('supports.created_at', '>=', $from->toDateTimeString())
                ->where('supports.created_at', '<', $to->copy()->addDay()->toDateTimeString());
        }
        foreach (['analytics_status' => 'supports.status', 'analytics_task_id' => 'supports.task_id',
            'analytics_department_id' => 'employees.department_id', 'analytics_technician_id' => 'supports.technician_id'] as $input => $column) {
            if (isset($filters[$input]) && $filters[$input] !== '') { $query->where($column, $filters[$input]); }
        }

        $categories = (clone $query)->select('tasks.id', 'tasks.name', DB::raw('COUNT(*) AS total'))
            ->groupBy('tasks.id', 'tasks.name')->orderByDesc('total')->orderBy('tasks.name')->get();
        $departments = (clone $query)->select('departments.id', 'departments.name', DB::raw('COUNT(*) AS total'))
            ->groupBy('departments.id', 'departments.name')->orderByDesc('total')->orderBy('departments.name')->get();
        $statuses = (clone $query)->select('supports.status', DB::raw('COUNT(*) AS total'))
            ->groupBy('supports.status')->pluck('total', 'status');
        $filters['analytics_period'] = $period;

        return [
            'total' => (int) $categories->sum('total'), 'filters' => $filters,
            'rangeLabel' => $from ? $from->format('M j, Y').' – '.$to->format('M j, Y') : 'All time',
            'categories' => $categories->map(fn ($item) => $item->name ?? 'Unavailable service')->values(),
            'categoryCounts' => $categories->map(fn ($item) => (int) $item->total)->values(),
            'departments' => $departments->map(fn ($item) => $item->name ?? 'Not specified')->values(),
            'departmentCounts' => $departments->map(fn ($item) => (int) $item->total)->values(),
            'statuses' => HelpdeskController::STATUSES,
            'statusCounts' => array_map(fn ($status) => (int) $statuses->get($status, 0), HelpdeskController::STATUSES),
        ];
    }
}
