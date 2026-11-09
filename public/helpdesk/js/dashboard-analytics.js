'use strict';

(() => {
    const panel = document.querySelector('.dashboard-analytics');
    if (!panel) return;
    const period = panel.querySelector('#analytics-period');
    const dates = [...panel.querySelectorAll('[data-analytics-date]')];
    function updateDates() {
        const custom = period.value === 'custom';
        dates.forEach(input => { input.disabled = !custom; input.required = custom; });
        panel.querySelector('#analytics-to').min = custom ? panel.querySelector('#analytics-from').value : '';
    }
    period.addEventListener('change', updateDates);
    dates.forEach(input => input.addEventListener('change', updateDates));
    updateDates();

    const source = document.getElementById('helpdesk-chart-data');
    if (!source || typeof Chart === 'undefined') return;
    const data = JSON.parse(source.textContent);
    const palette = ['#17385e', '#367ab2', '#d4a747', '#4e9b96', '#a0b7d1', '#8b71a8'];
    const reducedMotion = matchMedia('(prefers-reduced-motion: reduce)').matches;
    const narrow = matchMedia('(max-width: 768px)').matches;
    Chart.defaults.font.family = 'Inter, system-ui, sans-serif';
    function bar(id, labels, values, department = false) {
        return new Chart(document.getElementById(id), {
            type: 'bar',
            data: {labels, datasets: [{label: 'Tickets', data: values, backgroundColor: department ? '#367ab2' : palette, borderRadius: 5, maxBarThickness: 28}]},
            options: {
                responsive: true, maintainAspectRatio: false, indexAxis: 'y', animation: reducedMotion ? false : {duration: 250},
                plugins: {legend: {display: false}},
                scales: {
                    x: {beginAtZero: true, ticks: {precision: 0}, grid: {color: '#e5ebf3'}},
                    y: {grid: {display: false}, ticks: {font: {size: narrow ? 10 : 11}, callback: function (value) {
                        const text = this.getLabelForValue(value);
                        const limit = narrow ? 20 : 26;
                        return text.length > limit ? text.slice(0, limit - 1) + '…' : text;
                    }}},
                },
            },
        });
    }
    bar('category-chart', data.categories, data.categoryCounts);
    new Chart(document.getElementById('status-chart'), {
        type: 'doughnut',
        data: {labels: data.statuses, datasets: [{data: data.statusCounts,
            backgroundColor: data.statuses.map(status => ({Pending: '#d4a747', Completed: '#4e9b96', Cancelled: '#a0b7d1'}[status] || '#17385e')), borderWidth: 3}]},
        options: {responsive: true, maintainAspectRatio: false, animation: reducedMotion ? false : {duration: 250}, cutout: '68%', plugins: {legend: {position: 'bottom'}}},
    });
    bar('department-chart', data.departments, data.departmentCounts, true);
})();
