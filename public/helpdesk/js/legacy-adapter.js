'use strict';

// Keep tables readable on phones without forcing the entire page to scroll sideways.
document.querySelectorAll('.dashboard-table:not(.adapter-wide-table):not([data-dashboard-table])').forEach(table => {
    const headings = [...table.querySelectorAll('thead th')].map(heading => heading.textContent.trim());
    table.querySelectorAll('tbody tr').forEach(row => {
        if (row.cells.length !== headings.length) return;
        [...row.cells].forEach((cell, index) => { cell.dataset.label = headings[index]; });
    });
    table.classList.add('adapter-responsive-table');
});

// The ZIP presentation, using the existing Laravel endpoints and database fields.
const staffNavigation = document.getElementById('staff-sidebar');
const menuButton = document.querySelector('.admin-menu-toggle');
const menuBackdrop = document.querySelector('.admin-menu-backdrop');
const mobileStaff = matchMedia('(max-width: 768px)');
function setStaffMenu(open) {
    if (!staffNavigation) return;
    menuButton?.setAttribute('aria-expanded', String(open));
    staffNavigation.classList.toggle('is-open', open);
    staffNavigation.inert = mobileStaff.matches && !open;
    if (menuBackdrop) menuBackdrop.hidden = !open;
    document.body.classList.toggle('staff-menu-open', open);
    const shell = document.querySelector('.admin-shell');
    if (shell) shell.inert = open && mobileStaff.matches;
    if (open) document.querySelector('.admin-menu-close')?.focus();
}
menuButton?.addEventListener('click', () => setStaffMenu(true));
function dismissStaffMenu() { setStaffMenu(false); menuButton?.focus(); }
menuBackdrop?.addEventListener('click', dismissStaffMenu);
document.querySelector('.admin-menu-close')?.addEventListener('click', dismissStaffMenu);
mobileStaff.addEventListener('change', () => setStaffMenu(false));
document.addEventListener('keydown', event => {
    if (menuButton?.getAttribute('aria-expanded') !== 'true') return;
    if (event.key === 'Escape') dismissStaffMenu();
    if (event.key === 'Tab') {
        const controls = [...staffNavigation.querySelectorAll('a[href], button')];
        const first = controls[0], last = controls.at(-1);
        if (event.shiftKey && document.activeElement === first) { event.preventDefault(); last.focus(); }
        else if (!event.shiftKey && document.activeElement === last) { event.preventDefault(); first.focus(); }
    }
});
setStaffMenu(false);

const requesterForm = document.querySelector('[data-requester-url]');
if (requesterForm) {
    const email = requesterForm.elements.email;
    const notice = document.getElementById('requester-status');
    const check = document.getElementById('requester-check');
    const field = email.closest('.requester-email-field');
    let pendingLookup;
    let requestSequence = 0;
    email.addEventListener('input', () => {
        requestSequence++;
        email.setCustomValidity(''); email.removeAttribute('aria-invalid');
        check.hidden = true; field.classList.remove('is-verified'); notice.classList.remove('sr-only');
        notice.textContent = 'Enter your registered PMMA email.';
        pendingLookup?.abort();
    });
    email.addEventListener('change', async () => {
        if (!email.value || !email.checkValidity()) return;
        const sequence = ++requestSequence;
        pendingLookup?.abort(); pendingLookup = new AbortController();
        notice.textContent = 'Checking the PMMA directory…';
        notice.classList.remove('sr-only'); check.hidden = true; field.classList.remove('is-verified');
        try {
            const response = await fetch(requesterForm.dataset.requesterUrl, {
                method: 'POST', signal: pendingLookup.signal,
                headers: {'Content-Type': 'application/json', 'Accept': 'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content},
                body: JSON.stringify({email: email.value}),
            });
            const data = await response.json();
            if (sequence !== requestSequence) return;
            if (!response.ok) throw new Error(data.message || 'Unable to verify this email.');
            if (data.valid !== true) throw new Error('Unable to verify this email. Please try again.');
            email.removeAttribute('aria-invalid'); check.hidden = false; field.classList.add('is-verified');
            notice.textContent = 'Email verified.'; notice.classList.add('sr-only');
        } catch (error) {
            if (error.name === 'AbortError' || sequence !== requestSequence) return;
            check.hidden = true; field.classList.remove('is-verified'); notice.classList.remove('sr-only');
            notice.textContent = error.message || 'Unable to check the directory. Please try again.';
        }
    });
}

const chartData = document.getElementById('helpdesk-chart-data');
if (chartData && typeof Chart !== 'undefined') {
    const data = JSON.parse(chartData.textContent);
    const colors = ['#17385e', '#367ab2', '#d4a747', '#4e9b96', '#a0b7d1', '#8b71a8'];
    Chart.defaults.font.family = 'Inter, system-ui, sans-serif';
    new Chart(document.getElementById('category-chart'), {
        type: 'bar', data: {labels: data.categories, datasets: [{label: 'Tickets', data: data.categoryCounts, backgroundColor: colors, borderRadius: 5}]},
        options: {responsive: true, maintainAspectRatio: false, indexAxis: 'y', plugins: {legend: {display: false}}, scales: {x: {beginAtZero: true, ticks: {precision: 0}}, y: {grid: {display: false}, ticks: {callback: function(value) { const text = this.getLabelForValue(value); return text.length > 25 ? text.slice(0, 24) + '…' : text; }}}}},
    });
    new Chart(document.getElementById('status-chart'), {
        type: 'doughnut', data: {labels: data.statuses, datasets: [{data: data.statusCounts, backgroundColor: data.statuses.map(status => ({Pending: '#d4a747', Completed: '#4e9b96', Cancelled: '#a0b7d1'}[status] || '#17385e')), borderWidth: 3}]},
        options: {responsive: true, maintainAspectRatio: false, cutout: '68%', plugins: {legend: {position: 'bottom'}}},
    });
}

if (document.querySelector('[data-queue-refresh]')) {
    const updateQueueClock = () => {
        const time = document.getElementById('queue-time');
        const date = document.getElementById('queue-date');
        if (time) time.textContent = new Intl.DateTimeFormat('en-PH', {timeZone: 'Asia/Manila', hour: '2-digit', minute: '2-digit', second: '2-digit'}).format(new Date());
        if (date) date.textContent = new Intl.DateTimeFormat('en-PH', {timeZone: 'Asia/Manila', dateStyle: 'medium'}).format(new Date()) + ' · PHT';
    };
    updateQueueClock(); setInterval(updateQueueClock, 1000);
    setTimeout(() => { if (!document.hidden) location.reload(); }, 30000);
}

document.querySelectorAll('form[method="post"]').forEach(form => form.addEventListener('submit', () => {
    if (!form.checkValidity()) return;
    const button = form.querySelector('[type=submit], button:not([type])');
    if (button) { button.disabled = true; button.setAttribute('aria-busy', 'true'); }
}));
