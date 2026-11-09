<?php

namespace App\Http\Controllers;

use App\Models\Ticket;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class TicketTableController extends Controller
{
    public function publicData(Request $request) { return $this->data($request, true); }
    public function staffData(Request $request) { return $this->data($request, false); }

    private function data(Request $request, bool $public)
    {
        $data = $request->validate([
            'draw' => 'nullable|integer|min:0', 'start' => 'nullable|integer|min:0|max:1000000',
            'length' => 'nullable|integer|min:1|max:100', 'search.value' => 'nullable|string|max:200',
            'q' => 'nullable|string|max:200', 'status' => ['nullable', Rule::in(DashboardController::STATUSES)],
            'department_id' => 'nullable|integer', 'category' => 'nullable|string|max:120',
            'order.0.column' => 'nullable|integer|min:0|max:7', 'order.0.dir' => ['nullable', Rule::in(['asc', 'desc'])],
        ]);
        $query = Ticket::query();

        $total = (clone $query)->count();
        if (!$public) foreach (['status', 'department_id', 'category'] as $field) {
            if (!empty($data[$field])) $query->where($field, $data[$field]);
        }
        foreach (array_filter([$request->input('search.value'), $public ? null : ($data['q'] ?? null)]) as $search) {
            $query->where(function ($query) use ($search, $public) {
                $query->where('reference', 'like', '%'.$search.'%')->orWhere('subject', 'like', '%'.$search.'%')
                    ->orWhereHas('department', fn ($department) => $department->where('name', 'like', '%'.$search.'%'));
                if (!$public) $query->orWhere('name', 'like', '%'.$search.'%');
            });
        }
        $filtered = (clone $query)->count();
        if ($public) $query->select(['id', 'reference', 'subject', 'department_id', 'status', 'created_at']);
        $columns = $public ? ['reference', 'subject', null, 'status'] : ['reference', 'name', 'priority', 'status', null, 'created_at'];
        $column = $columns[(int) $request->input('order.0.column', 99)] ?? 'created_at';
        $rows = $query->with($public ? ['department:id,name'] : ['department:id,name', 'technician:id,name'])
            ->with(['latestAction', 'latestUpdate'])
            ->orderBy($column, $request->input('order.0.dir', 'desc'))->orderByDesc('id')->skip($data['start'] ?? 0)->take($data['length'] ?? 10)->get();
        return response()->json([
            'draw' => (int) ($data['draw'] ?? 0), 'recordsTotal' => $total, 'recordsFiltered' => $filtered,
            'rows' => view($public ? 'portal.partials.recent-ticket-rows' : 'dashboard.ticket-rows', [$public ? 'recentTickets' : 'tickets' => $rows])->render(),
        ])->header('Cache-Control', 'no-store');
    }
}
