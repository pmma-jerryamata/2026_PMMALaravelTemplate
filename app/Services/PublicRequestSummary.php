<?php

namespace App\Services;

use App\Models\Ticket;
use App\Models\ServiceCategory;
use Illuminate\Http\Request;

class PublicRequestSummary
{
    public function data(Request $request): array
    {
        $range = $request->query('summary_range', 'all');
        if (! in_array($range, ['today', '7', '28', 'all'], true)) $range = 'all';
        $now = now('Asia/Manila');
        $query = Ticket::query();
        if ($range !== 'all') $query->whereBetween('created_at', [$now->copy()->subDays(($range === 'today' ? 1 : (int) $range) - 1)->startOfDay()->utc(), $now->copy()->endOfDay()->utc()]);
        $statuses = (clone $query)->selectRaw('status, COUNT(*) as total')->groupBy('status')->pluck('total', 'status');
        $categories = (clone $query)->selectRaw('category, COUNT(*) as total')->groupBy('category')->pluck('total', 'category');
        $services = ServiceCategory::orderBy('display_order')->orderBy('id')->get()->filter(fn ($service) => $service->is_active || ($categories[$service->slug] ?? 0) > 0)->map(fn ($service) => ['name' => $service->title, 'total' => (int) ($categories[$service->slug] ?? 0)]);
        $total = (int) $statuses->sum();
        if ($total > $services->sum('total')) $services->push(['name' => 'Other / unavailable service', 'total' => $total - $services->sum('total')]);
        return compact('range', 'statuses', 'services', 'total', 'now');
    }
}
