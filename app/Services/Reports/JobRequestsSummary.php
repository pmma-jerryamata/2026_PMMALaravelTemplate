<?php

namespace App\Services\Reports;

use App\Models\Department;
use App\Models\Task;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class JobRequestsSummary
{
    public function data(Request $request): array
    {
        $filters = $request->validate([
            'from' => 'nullable|date_format:Y-m-d', 'to' => 'nullable|date_format:Y-m-d|after_or_equal:from',
            'all_dates' => 'nullable|boolean', 'technician_id' => 'nullable|integer|exists:users,id',
            'department_id' => 'nullable|integer|exists:departments,id', 'task_id' => 'nullable|integer|exists:tasks,id',
            'status' => 'nullable|in:Pending,Completed,Cancelled', 'download' => 'nullable|boolean',
            'report_title' => 'nullable|string|max:150', 'introduction' => 'nullable|string|max:3000', 'intro_default' => 'nullable|boolean',
        ]);
        $from = $filters['from'] ?? now()->startOfMonth()->toDateString();
        $to = $filters['to'] ?? now()->toDateString();
        if ($to < $from) throw \Illuminate\Validation\ValidationException::withMessages(['to' => 'The end date must be on or after the start date.']);
        $query = DB::table('supports')->leftJoin('employees', 'employees.id', '=', 'supports.employee_id')
            ->leftJoin('departments', 'departments.id', '=', 'employees.department_id');
        if (empty($filters['all_dates'])) $query->whereBetween('supports.created_at', [$from.' 00:00:00', $to.' 23:59:59']);
        foreach (['technician_id', 'task_id', 'status'] as $field) {
            if (! empty($filters[$field])) $query->where('supports.'.$field, $filters[$field]);
        }
        if (! empty($filters['department_id'])) $query->where('departments.id', $filters['department_id']);
        $counts = $query->select('departments.id as department_id', 'supports.task_id', DB::raw('COUNT(*) AS total'))
            ->groupBy('departments.id', 'supports.task_id')->get();
        $departments = Department::orderBy('name')->get();
        $services = Task::displayOrder()->get();
        $reportServices = $services->filter(fn ($service) => empty($filters['task_id']) || $service->id == $filters['task_id'])->values();
        $offices = $departments->filter(fn ($office) => empty($filters['department_id']) || $office->id == $filters['department_id'])
            ->map(fn ($office) => ['id' => $office->id, 'name' => $office->name])->values();
        if ($counts->contains(fn ($row) => $row->department_id === null)) $offices->push(['id' => 0, 'name' => 'Not specified / unavailable office']);
        if (empty($filters['task_id']) && $counts->contains(fn ($row) => ! $services->contains('id', $row->task_id))) {
            $reportServices->push((object) ['id' => 0, 'name' => 'Unavailable service']);
        }
        $cells = [];
        foreach ($counts as $count) {
            $serviceId = $services->contains('id', $count->task_id) ? $count->task_id : 0;
            $officeId = $count->department_id ?? 0;
            $cells[$officeId][$serviceId] = ($cells[$officeId][$serviceId] ?? 0) + (int) $count->total;
        }
        $rows = $offices->map(function ($office) use ($cells, $reportServices) {
            $values = $reportServices->mapWithKeys(fn ($service) => [$service->id => $cells[$office['id']][$service->id] ?? 0])->all();
            return $office + ['counts' => $values, 'total' => array_sum($values)];
        });
        $columnTotals = $reportServices->mapWithKeys(fn ($service) => [$service->id => $rows->sum(fn ($row) => $row['counts'][$service->id])])->all();
        $technicians = User::orderBy('name')->get();
        $scope = [empty($filters['all_dates']) ? $from.' to '.$to : 'All dates',
            empty($filters['technician_id']) ? 'All technicians' : $technicians->firstWhere('id', $filters['technician_id'])->name,
            empty($filters['department_id']) ? 'All offices' : $departments->firstWhere('id', $filters['department_id'])->name,
            empty($filters['task_id']) ? 'All service types' : $services->firstWhere('id', $filters['task_id'])->name,
            $filters['status'] ?? 'All statuses'];
        $total = $rows->sum('total');
        $reportTitle = trim($filters['report_title'] ?? '') ?: 'JOB REQUESTS REPORT';
        $period = empty($filters['all_dates']) ? \Carbon\Carbon::parse($from)->format('F j, Y').' - '.\Carbon\Carbon::parse($to)->format('F j, Y') : 'All dates';
        $defaultIntroduction = 'Submitted herewith is the job requests report '.(empty($filters['all_dates']) ? 'for the period as of '.$period : 'covering all recorded dates').'.';
        $scopeText = fn ($value) => str_starts_with($value, 'All ') ? lcfirst($value) : $value;
        $defaultIntroduction .= ' This report covers '.$scopeText($scope[1]).', '.$scopeText($scope[2]).', and '.$scopeText($scope[3]).' ('.$scopeText($scope[4]).').';
        $introduction = !empty($filters['intro_default']) ? $defaultIntroduction : (trim($filters['introduction'] ?? '') ?: $defaultIntroduction);
        $filters['report_title'] = $reportTitle;
        $filters['introduction'] = $introduction;
        $filters['intro_default'] = (int) ($introduction === $defaultIntroduction);
        return compact('reportTitle', 'period', 'introduction', 'filters', 'from', 'to', 'rows', 'columnTotals', 'total', 'departments', 'services', 'reportServices', 'technicians', 'scope');
    }
}
