<section class="dashboard-analytics" aria-labelledby="analytics-heading" data-analytics-url="{{ route('dashboard.analytics') }}">
    <div class="analytics-header"><div><h2 id="analytics-heading">Ticket Analytics</h2><p>Based on submission date · Philippine time · Weeks start Monday</p></div><div class="analytics-periods" role="group" aria-label="Analytics Time Period">
        @foreach(['day' => ['calendar-date', 'Day'], 'week' => ['calendar-week', 'Week'], 'month' => ['calendar-month', 'Month']] as $value => [$icon, $label])
        <button type="button" class="analytics-period" data-period="{{ $value }}" aria-label="{{ $label }}" title="Group by {{ strtolower($label) }}" aria-pressed="{{ $analytics['period'] === $value ? 'true' : 'false' }}"><x-action-icon :name="$icon" /><span>{{ $label }}</span></button>
        @endforeach
    </div></div>
    <p id="analytics-summary" class="analytics-summary" role="status">{{ $analytics['total'] }} tickets · {{ $analytics['range'] }} · {{ ucfirst($analytics['period']) }} view</p>
    <p class="analytics-filter-note">Graphs follow the status, department, category, and search filters below. Summary cards show all-time totals.</p>
    <div class="analytics-grid">
        <article class="portal-panel chart-card"><h3>Tickets by Concern Category</h3><p>Category mix across the selected time periods.</p><div class="chart-area"><canvas id="category-chart" role="img" aria-label="Ticket counts by concern category over time"></canvas></div></article>
        <article class="portal-panel chart-card"><h3>Tickets by Department</h3><p>Total requests per office within the date range.</p><div class="chart-area"><canvas id="department-chart" role="img" aria-label="Ticket counts by department"></canvas></div></article>
        <article class="portal-panel chart-card"><h3>Ticket Volume</h3><p>New requests per day, week, or month.</p><div class="chart-area"><canvas id="volume-chart" role="img" aria-label="Ticket volume over time"></canvas></div></article>
    </div>
    <p id="analytics-empty" @if($analytics['total'] > 0) hidden @endif>No tickets in this date range for the selected filters.</p>
    <details class="analytics-data"><summary>View Chart Data</summary><div id="analytics-data-content"></div></details>
</section>
@push('dashboard-data')
<script type="application/json" id="analytics-initial">@json($analytics)</script>
@endpush
