<?php

namespace App\Http\Controllers;

use App\Models\Department;
use App\Models\Employee;
use App\Models\RatingResult;
use App\Models\Support;
use App\Models\ServiceIssue;
use App\Models\Task;
use App\Models\Technician;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

/** Read-only DataTables endpoints; sorting columns are exclusively server-defined. */
class DashboardTableController extends Controller
{
    private function parameters(Request $request): array
    {
        $input = $request->validate([
            'draw' => 'nullable|integer|min:0|max:2147483647', 'start' => 'nullable|integer|min:0|max:100000000',
            'length' => 'nullable|integer|min:-1|max:100', 'search' => 'nullable|array',
            'search.value' => 'nullable|string|max:200', 'order' => 'nullable|array|max:1',
            'order.0.column' => 'nullable|integer|min:0|max:20', 'order.0.dir' => ['nullable', Rule::in(['asc', 'desc'])],
        ]);
        return [
            'draw' => (int) ($input['draw'] ?? 0), 'start' => (int) ($input['start'] ?? 0),
            'length' => ($input['length'] ?? 15) == -1 ? 100 : max(1, (int) ($input['length'] ?? 15)),
            'search' => trim($input['search']['value'] ?? ''),
            'column' => (int) ($input['order'][0]['column'] ?? -1), 'direction' => $input['order'][0]['dir'] ?? 'asc',
        ];
    }

    private function response($query, array $parameters, int $total, array $columns, string $defaultColumn,
        string $defaultDirection, string $partial, array $data = [])
    {
        $filtered = (clone $query)->count();
        $column = $columns[$parameters['column']] ?? null;
        $query->orderBy($column ?: $defaultColumn, $column ? $parameters['direction'] : $defaultDirection);
        if (($column ?: $defaultColumn) !== $query->getModel()->getTable().'.id') {
            $query->orderBy($query->getModel()->getTable().'.id', 'desc');
        }
        $records = $query->skip($parameters['start'])->take($parameters['length'])->get();
        return response()->json([
            'draw' => $parameters['draw'], 'recordsTotal' => $total, 'recordsFiltered' => $filtered,
            'rows' => view('helpdesk.dashboard.rows.'.$partial, $data + ['records' => $records, 'datatable' => true])->render(),
        ])->header('Cache-Control', 'private, no-store');
    }

    public function tickets(Request $request)
    {
        $parameters = $this->parameters($request);
        $filters = $request->validate([
            'status' => ['nullable', Rule::in(HelpdeskController::STATUSES)],
            'task_id' => 'nullable|integer|exists:tasks,id', 'department_id' => 'nullable|integer|exists:departments,id',
            'technician_id' => 'nullable|integer|exists:technicians,id',
        ]);
        $query = Support::with(['task', 'technician', 'employee' => fn ($employee) => $employee->withTrashed()->with('department')])
            ->leftJoin('employees', 'employees.id', '=', 'supports.employee_id')
            ->leftJoin('departments', 'departments.id', '=', 'employees.department_id')
            ->leftJoin('tasks', 'tasks.id', '=', 'supports.task_id')
            ->leftJoin('technicians', 'technicians.id', '=', 'supports.technician_id')->select('supports.*');
        foreach (['status', 'task_id', 'technician_id'] as $field) {
            if (! empty($filters[$field])) { $query->where('supports.'.$field, $filters[$field]); }
        }
        if (! empty($filters['department_id'])) { $query->where('employees.department_id', $filters['department_id']); }
        $total = (clone $query)->count();
        if ($parameters['search'] !== '') {
            $term = '%'.$parameters['search'].'%';
            $query->where(function ($search) use ($term) {
                foreach (['supports.id', 'employees.name', 'employees.email', 'departments.name', 'tasks.name',
                    'supports.support_specifics', 'supports.findings', 'supports.status', 'technicians.name', 'supports.created_at'] as $column) {
                    $search->orWhere($column, 'like', $term);
                }
            });
        }
        return $this->response($query, $parameters, $total, [
            'supports.id', 'employees.name', 'departments.name', 'tasks.name', 'supports.status',
            'technicians.name', 'supports.created_at', null,
        ], 'supports.created_at', 'desc', 'tickets');
    }

    public function catalog(Request $request, string $type)
    {
        $models = ['employees' => Employee::class, 'departments' => Department::class,
            'technicians' => Technician::class, 'services' => Task::class, 'issues' => ServiceIssue::class];
        abort_unless(isset($models[$type]), 404);
        $parameters = $this->parameters($request);
        $model = $models[$type];
        $table = (new $model)->getTable();
        if ($type === 'employees') {
            $query = Employee::withTrashed()->with('department')
                ->leftJoin('departments', 'departments.id', '=', 'employees.department_id')->select('employees.*');
            $columns = ['employees.name', 'employees.email', 'departments.name', 'employees.deleted_at', null];
            $searchColumns = ['employees.name', 'employees.email', 'departments.name'];
            if ($request->query('status') === 'activation') {
                $query->where('employees.activation_pending', true)->whereNotNull('employees.deleted_at');
            }
        } else {
            $query = $model::query();
            $columns = [$table.'.name'];
            $searchColumns = $columns;
            if ($type === 'technicians') { $columns[] = 'technicians.email'; $searchColumns[] = 'technicians.email'; }
            if ($type === 'services') { $columns[] = 'tasks.content'; $searchColumns[] = 'tasks.content'; }
            $columns[] = null;
            if ($type === 'issues') {
                $query->with('task');
                $columns = ['service_issues.name', null, null];
            }
        }
        $serviceOrderEnabled = $type === 'services' && \Illuminate\Support\Facades\Schema::hasColumn('tasks', 'sort_order');
        if ($type === 'services') { $query->displayOrder(); }
        $total = (clone $query)->count();
        if ($parameters['search'] !== '') {
            $term = '%'.$parameters['search'].'%';
            $query->where(function ($search) use ($searchColumns, $term, $type) {
                foreach ($searchColumns as $column) { $search->orWhere($column, 'like', $term); }
                if ($type === 'employees') {
                    $search->orWhereRaw("CASE WHEN employees.deleted_at IS NULL THEN 'Active' ELSE 'Inactive' END LIKE ?", [$term]);
                }
            });
        }
        return $this->response($query, $parameters, $total, $columns, $table.'.name', 'asc', 'catalog', compact('type', 'serviceOrderEnabled'));
    }

    public function surveys(Request $request)
    {
        $parameters = $this->parameters($request);
        $query = RatingResult::with(['rating', 'rating_measure', 'support.task'])
            ->leftJoin('supports', 'supports.id', '=', 'ratings_result.support_id')
            ->leftJoin('tasks', 'tasks.id', '=', 'supports.task_id')
            ->leftJoin('ratings', 'ratings.id', '=', 'ratings_result.rating_id')
            ->leftJoin('ratings_measure', 'ratings_measure.id', '=', 'ratings_result.rating_measure_id')
            ->select('ratings_result.*');
        $total = (clone $query)->count();
        if ($parameters['search'] !== '') {
            $term = '%'.$parameters['search'].'%';
            $query->where(function ($search) use ($term) {
                foreach (['ratings_result.support_id', 'tasks.name', 'ratings_measure.name', 'ratings.name',
                    'ratings_result.feedback', 'ratings_result.created_at'] as $column) {
                    $search->orWhere($column, 'like', $term);
                }
                $search->orWhereRaw("COALESCE(ratings_measure.name, 'Overall satisfaction') LIKE ?", [$term]);
            });
        }
        return $this->response($query, $parameters, $total, [
            'ratings_result.support_id', 'tasks.name', 'ratings_measure.name', 'ratings.name',
            'ratings_result.feedback', 'ratings_result.created_at',
        ], 'ratings_result.created_at', 'desc', 'surveys');
    }
}
