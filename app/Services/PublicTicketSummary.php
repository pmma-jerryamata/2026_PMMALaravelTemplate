<?php

namespace App\Services;

use App\Models\Support;
use App\Models\Task;
use Illuminate\Http\Request;

class PublicTicketSummary
{
    public function data(Request $request): array
    {
        $range = $request->query('summary_range', 'all');
        if (! in_array($range, ['today', '7', '28', 'all'], true)) $range = 'all';
        $query = Support::query();
        $now = now();
        if ($range !== 'all') {
            $days = $range === 'today' ? 1 : (int) $range;
            $query->whereBetween('created_at', [$now->copy()->subDays($days - 1)->startOfDay(), $now->copy()->endOfDay()]);
        }
        $statuses = (clone $query)->selectRaw('status, COUNT(*) AS total')->groupBy('status')->pluck('total', 'status');
        $counts = (clone $query)->selectRaw('task_id, COUNT(*) AS total')->groupBy('task_id')->pluck('total', 'task_id');
        $total = (int) $statuses->sum();
        $services = Task::displayOrder()->get(['id', 'name'])->map(fn ($service) => [
            'name' => $service->name, 'total' => (int) ($counts[$service->id] ?? 0),
        ]);
        $unavailable = $total - $services->sum('total');
        if ($unavailable > 0) $services->push(['name' => 'Other / unavailable service', 'total' => $unavailable]);
        return ['range' => $range, 'total' => $total, 'completed' => (int) ($statuses['Completed'] ?? 0),
            'pending' => (int) ($statuses['Pending'] ?? 0), 'cancelled' => (int) ($statuses['Cancelled'] ?? 0),
            'services' => $services, 'asOf' => $now];
    }
}
