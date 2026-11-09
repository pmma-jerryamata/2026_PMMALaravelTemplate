<?php

namespace App\Http\Controllers;

use App\Models\ActivityLog;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class ActivityLogController extends Controller
{
    public function index()
    {
        return view('dashboard.activity-logs', ['operations' => ActivityLog::distinct()->orderBy('operation')->pluck('operation')]);
    }

    public function data(Request $request)
    {
        $filters = $request->validate([
            'ip' => 'nullable|string|max:45', 'user' => 'nullable|string|max:254', 'operation' => 'nullable|string|max:120',
            'outcome' => ['nullable', Rule::in(['success', 'failed'])], 'from' => 'nullable|date_format:Y-m-d',
            'to' => ['nullable', 'date_format:Y-m-d', Rule::when($request->filled('from'), 'after_or_equal:from')],
            'start' => 'nullable|integer|min:0', 'length' => 'nullable|integer|min:1|max:100', 'draw' => 'nullable|integer|min:0',
            'search.value' => 'nullable|string|max:254', 'order.0.column' => 'nullable|integer|min:0|max:6',
            'order.0.dir' => ['nullable', Rule::in(['asc', 'desc'])],
        ]);
        $query = ActivityLog::query();
        if (!empty($filters['ip'])) $query->where('ip_address', 'like', '%'.$filters['ip'].'%');
        if (!empty($filters['user'])) {
            $user = $filters['user'];
            $query->where(fn ($query) => $query->where('actor_name', 'like', '%'.$user.'%')->orWhere('actor_login', 'like', '%'.$user.'%')->orWhere('user_id', ctype_digit($user) ? (int) $user : -1));
        }
        foreach (['operation', 'outcome'] as $field) if (!empty($filters[$field])) $query->where($field, $filters[$field]);
        if (!empty($filters['from'])) $query->where('created_at', '>=', \Carbon\CarbonImmutable::parse($filters['from'], 'Asia/Manila')->startOfDay()->utc());
        if (!empty($filters['to'])) $query->where('created_at', '<', \Carbon\CarbonImmutable::parse($filters['to'], 'Asia/Manila')->addDay()->startOfDay()->utc());
        if ($search = $request->input('search.value')) $query->where(fn ($query) => $query->where('actor_name', 'like', '%'.$search.'%')->orWhere('actor_login', 'like', '%'.$search.'%')->orWhere('ip_address', 'like', '%'.$search.'%')->orWhere('operation', 'like', '%'.$search.'%')->orWhere('path', 'like', '%'.$search.'%'));
        $filtered = (clone $query)->count();
        $columns = ['created_at', 'actor_name', 'ip_address', 'operation', 'outcome', 'status_code', 'path'];
        $column = $columns[(int) $request->input('order.0.column', 0)] ?? 'created_at';
        $direction = $request->input('order.0.dir', 'desc');
        $rows = $query->orderBy($column, $direction)->orderByDesc('id')->skip((int) ($filters['start'] ?? 0))->take((int) ($filters['length'] ?? 25))->get()->map(fn ($log) => [
            'time' => $log->created_at->timezone('Asia/Manila')->format('M d, Y h:i:s A'),
            'user' => $log->actor_name.($log->actor_login ? ' ('.$log->actor_login.')' : ''),
            'ip' => $log->ip_address ?? 'Unknown', 'operation' => $log->operation,
            'outcome' => ucfirst($log->outcome), 'http' => $log->method.' '.$log->status_code,
            'details' => $log->path.' · '.json_encode($log->details, JSON_UNESCAPED_SLASHES),
        ]);
        return response()->json(['draw' => (int) ($filters['draw'] ?? 0), 'recordsTotal' => ActivityLog::count(), 'recordsFiltered' => $filtered, 'data' => $rows])->header('Cache-Control', 'private, no-store');
    }
}
