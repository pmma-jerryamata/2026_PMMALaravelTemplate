<?php

namespace App\Services;

use App\Models\Department;
use App\Models\ServiceCategory;
use App\Models\Ticket;
use Carbon\CarbonImmutable;

class TicketAnalytics
{
    public function build(string $period, array $filters = []): array
    {
        $now = CarbonImmutable::now('Asia/Manila');
        [$count, $unit] = match ($period) {
            'day' => [14, 'day'], 'week' => [12, 'week'], default => [12, 'month'],
        };
        $current = match ($unit) {
            'day' => $now->startOfDay(), 'week' => $now->startOfWeek(), default => $now->startOfMonth(),
        };
        $start = $current->sub($unit, $count - 1);
        $buckets = [];
        for ($i = 0; $i < $count; $i++) {
            $date = $start->add($unit, $i);
            $buckets[$date->format('Y-m-d')] = ['label' => $date->format($unit === 'month' ? 'M Y' : 'M d, Y'), 'total' => 0];
        }
        $categories = ServiceCategory::pluck('title', 'slug')->all();
        $departments = Department::pluck('name', 'id')->all();
        $categoryCounts = [];
        $departmentCounts = [];
        $query = Ticket::whereBetween('created_at', [$start->utc(), $now->utc()]);
        foreach (['status', 'department_id', 'category'] as $field) {
            if (!empty($filters[$field])) $query->where($field, $filters[$field]);
        }
        if (!empty($filters['q'])) {
            $q = $filters['q'];
            $query->where(fn ($query) => $query->where('reference', 'like', '%'.$q.'%')->orWhere('subject', 'like', '%'.$q.'%')->orWhere('name', 'like', '%'.$q.'%'));
        }
        foreach ($query->select(['id', 'created_at', 'category', 'department_id'])->lazyById(500) as $ticket) {
            $date = CarbonImmutable::instance($ticket->created_at)->setTimezone('Asia/Manila');
            $bucket = match ($unit) {
                'day' => $date->startOfDay(), 'week' => $date->startOfWeek(), default => $date->startOfMonth(),
            };
            $key = $bucket->format('Y-m-d');
            $buckets[$key]['total']++;
            $categoryCounts[$ticket->category][$key] = ($categoryCounts[$ticket->category][$key] ?? 0) + 1;
            $department = $ticket->department_id ?? 'unspecified';
            $departmentCounts[$department] = ($departmentCounts[$department] ?? 0) + 1;
        }
        $datasets = [];
        // Include active categories at zero as well as historical categories with data.
        $slugs = array_unique(array_merge(ServiceCategory::where('is_active', true)->pluck('slug')->all(), array_keys($categoryCounts)));
        foreach ($slugs as $slug) {
            if (!empty($filters['category']) && $filters['category'] !== $slug) continue;
            $datasets[] = ['label' => $categories[$slug] ?? $slug, 'data' => array_map(fn ($key) => $categoryCounts[$slug][$key] ?? 0, array_keys($buckets))];
        }
        arsort($departmentCounts);
        return [
            'period' => $period, 'timezone' => 'Asia/Manila', 'weekStartsOn' => 'Monday',
            'range' => $start->format('M d, Y').' – '.$now->format('M d, Y'),
            'labels' => array_column($buckets, 'label'), 'volume' => array_column($buckets, 'total'),
            'categories' => $datasets,
            'departments' => ['labels' => array_map(fn ($id) => $departments[$id] ?? 'Not Specified', array_keys($departmentCounts)), 'data' => array_values($departmentCounts)],
            'total' => array_sum(array_column($buckets, 'total')),
        ];
    }
}
