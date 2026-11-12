'use strict';

document.querySelectorAll('[data-ticket-delete-confirmation]').forEach(form => {
    const submit = form.querySelector('button[type="submit"]');
    const matches = () => form.elements.confirmation.value === form.dataset.confirmation &&
        form.elements.password.value.length > 0 && form.elements.acknowledge.checked;
    const update = () => { submit.disabled = !matches(); };
    form.addEventListener('input', update);
    form.addEventListener('change', update);
    form.addEventListener('submit', event => {
        if (!matches()) { event.preventDefault(); update(); return; }
        submit.disabled = true;
        submit.textContent = 'Deleting ticket…';
    });
    window.addEventListener('pageshow', update);
    update();
});

document.querySelectorAll('[data-dashboard-table]').forEach(table => {
    if (typeof DataTable === 'undefined' || DataTable.isDataTable(table)) return;
    const headings = [...table.querySelectorAll('thead th')].map(cell => cell.textContent.trim());
    const actionIndex = table.dataset.tableActions === 'true' ? headings.length - 1 : -1;
    const [defaultColumn, defaultDirection] = (table.dataset.tableOrder || '0:asc').split(':');
    const mobile = matchMedia('(max-width: 768px)');
    const panel = table.closest('.portal-panel');
    const fallback = panel?.querySelector('.adapter-pagination');
    const emptyMessage = table.dataset.emptyMessage || 'No records available.';

    // DataTables creates its own empty state and requires one cell per column in actual data rows.
    [...table.tBodies[0].rows].forEach(row => {
        if (row.cells.length !== headings.length || row.querySelector('[colspan]')) row.remove();
    });
    const notice = document.createElement('div');
    notice.className = 'dashboard-table-notice'; notice.setAttribute('role', 'alert'); notice.hidden = true;
    table.parentElement.before(notice);
    let activeRequest;
    let sequence = 0;

    function labelRows(api) {
        api.rows({page: 'current'}).nodes().each(row => {
            [...row.cells].forEach((cell, index) => { cell.dataset.label = headings[index] || ''; });
        });
        // The empty row has a colspan and should remain a single, full-width message on mobile.
        table.querySelectorAll('td.dt-empty').forEach(cell => { delete cell.dataset.label; });
    }

    const options = {
        autoWidth: false, orderMulti: false, scrollX: false,
        ordering: table.dataset.tableOrdering !== 'false',
        stateSave: true,
        stateSaveCallback: (_settings, data) => {
            try { sessionStorage.setItem('helpdesk.table.' + table.id + location.pathname + location.search, JSON.stringify(data)); } catch (_) { /* Storage may be disabled. */ }
        },
        stateLoadCallback: () => {
            try { return JSON.parse(sessionStorage.getItem('helpdesk.table.' + table.id + location.pathname + location.search)) || null; } catch (_) { return null; }
        },
        pageLength: 15, lengthMenu: [10, 15, 25, 50, 100],
        order: [[Number(defaultColumn), defaultDirection]],
        layout: {topStart: 'pageLength', topEnd: 'search', bottomStart: 'info', bottomEnd: {paging: {buttons: 3}}},
        columnDefs: actionIndex >= 0 ? [{targets: actionIndex, orderable: false, searchable: false}] : [],
        language: {
            emptyTable: emptyMessage, zeroRecords: 'No matching records found.',
            search: 'Search:', searchPlaceholder: 'Search all records…', lengthMenu: 'Show _MENU_ entries',
            processing: 'Loading records…', info: 'Showing _START_–_END_ of _TOTAL_ records',
            infoEmpty: 'No records to show', infoFiltered: '(filtered from _MAX_ records)',
            paginate: {first: '«', previous: 'Previous', next: 'Next', last: '»'},
        },
        drawCallback: function () { labelRows(this.api()); },
        initComplete: function () { if (fallback) fallback.hidden = true; },
    };
    if (table.id === 'tickets-table') {
        const widths = ['8%', '16%', '16%', '22%', '8%', '12%', '10%', '8%'];
        options.columnDefs.push(...widths.map((width, targets) => ({targets, width})));
    }
    if (table.dataset.tableSource) {
        options.serverSide = true;
        options.processing = true;
        options.searchDelay = 350;
        options.search = {search: table.dataset.tableSearch || ''};
        options.ajax = async (parameters, callback) => {
            activeRequest?.abort(); activeRequest = new AbortController();
            const thisRequest = ++sequence;
            const url = new URL(table.dataset.tableSource, location.href);
            for (const key of ['draw', 'start', 'length']) url.searchParams.set(key, parameters[key]);
            url.searchParams.set('search[value]', parameters.search.value);
            if (parameters.order[0]) {
                url.searchParams.set('order[0][column]', parameters.order[0].column);
                url.searchParams.set('order[0][dir]', parameters.order[0].dir);
            }
            notice.hidden = true;
            try {
                const response = await fetch(url, {headers: {Accept: 'application/json'}, signal: activeRequest.signal});
                if (response.status === 401 || response.redirected) {
                    const error = new Error(); error.displayMessage = 'Your session expired. Sign in again to load the records.'; throw error;
                }
                const data = await response.json();
                if (!response.ok) {
                    const error = new Error();
                    error.displayMessage = response.status === 422 ? 'Check your table filters and try again.' : 'Unable to load records. Refresh the page to try again.';
                    throw error;
                }
                if (thisRequest !== sequence) return;
                const body = document.createElement('tbody');
                // Cells are escaped by the same Blade partials used for the initial server-rendered table.
                body.innerHTML = data.rows;
                callback({draw: data.draw, recordsTotal: data.recordsTotal, recordsFiltered: data.recordsFiltered,
                    data: [...body.rows].map(row => [...row.cells].map(cell => cell.innerHTML))});
            } catch (error) {
                if (error.name === 'AbortError' || thisRequest !== sequence) return;
                notice.textContent = error.displayMessage || 'Unable to load records. Check your connection and refresh the page.';
                notice.hidden = false;
                callback({draw: parameters.draw, recordsTotal: 0, recordsFiltered: 0, data: []});
            }
        };
    }
    table.classList.add('adapter-responsive-table', 'dashboard-datatable');
    const api = new DataTable(table, options);

    // Column headers remain available on desktop. Mobile cards have an equivalent explicit sort control.
    const controls = document.createElement('div'); controls.className = 'dashboard-mobile-sort';
    const label = document.createElement('label'); label.htmlFor = table.id + '-sort'; label.textContent = 'Sort by';
    const select = document.createElement('select'); select.id = label.htmlFor;
    headings.forEach((heading, index) => {
        if (index === actionIndex) return;
        const option = document.createElement('option'); option.value = String(index); option.textContent = heading;
        select.append(option);
    });
    const direction = document.createElement('button'); direction.type = 'button'; direction.className = 'btn btn-secondary';
    controls.append(label, select, direction);
    const layout = table.closest('.dt-layout-table') || table;
    layout.before(controls);
    function syncSort() {
        const order = api.order()[0] || [Number(defaultColumn), defaultDirection];
        select.value = String(order[0]);
        direction.textContent = order[1] === 'asc' ? '↑ Ascending' : '↓ Descending';
        direction.dataset.direction = order[1];
        direction.setAttribute('aria-label', 'Sorted ' + (order[1] === 'asc' ? 'ascending' : 'descending') + '. Reverse sort direction');
    }
    select.addEventListener('change', () => api.order([[Number(select.value), direction.dataset.direction || 'asc']]).draw());
    direction.addEventListener('click', () => api.order([[Number(select.value), direction.dataset.direction === 'asc' ? 'desc' : 'asc']]).draw());
    api.on('order', syncSort);
    function setMobile() {
        controls.hidden = !mobile.matches;
        table.tHead.inert = mobile.matches;
    }
    mobile.addEventListener('change', () => { setMobile(); api.columns.adjust(); });
    setMobile(); syncSort();
});
