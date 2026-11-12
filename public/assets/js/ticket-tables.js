document.querySelectorAll('[data-server-tickets]').forEach(table => {
    const mobile = matchMedia('(max-width: 768px)');
    const staff = table.dataset.source.includes('/dashboard/');
    const visibleColumns = staff ? [0, 3] : [0, 1];
    const headings = [...table.querySelectorAll('thead th')].map(th => th.textContent.trim());
    table.classList.add('ticket-responsive-table');
    function addExpandButtons(api) {
        api.rows({page:'current'}).every(function () {
            const cell = this.node().querySelector('td');
            if (!cell || cell.querySelector('.ticket-expand')) return;
            const button = document.createElement('button');
            button.type = 'button';
            button.className = 'ticket-expand';
            button.textContent = '＋ Details';
            button.setAttribute('aria-expanded', 'false');
            button.setAttribute('aria-label', 'Show details for '+cell.textContent.trim());
            cell.append(button);
        });
    }
    const error = document.createElement('p'); error.setAttribute('role', 'alert'); table.parentElement.before(error);
    const api = new DataTable(table, {
        autoWidth: false,
        stateSave: staff, stateDuration: -1,
        stateSaveCallback: (settings, data) => { try { sessionStorage.setItem('ticket-table:'+location.pathname+location.search, JSON.stringify(data)); } catch {} },
        stateLoadCallback: () => { try { const data = JSON.parse(sessionStorage.getItem('ticket-table:'+location.pathname+location.search)); if(data?.columns) data.columns.forEach(column => delete column.visible); return data; } catch { return null; } },
        columns: headings.map((heading, index) => ({visible: !mobile.matches || visibleColumns.includes(index)})),
        drawCallback: function () { addExpandButtons(this.api()); },
        serverSide:true, processing:true, pageLength:10, lengthMenu:[10,25,50,100], searchDelay:500,
        order:[], columnDefs:[{targets:table.dataset.source.includes('/dashboard/') ? [4,6,7] : [2,4,5],orderable:false}],
        ajax:async (parameters, callback) => {
            const query = new URLSearchParams(location.search);
            query.set('draw', parameters.draw); query.set('start', parameters.start); query.set('length', parameters.length);
            query.set('search[value]', parameters.search.value); error.textContent='';
            if (parameters.order[0]) {
                query.set('order[0][column]', parameters.order[0].column); query.set('order[0][dir]', parameters.order[0].dir);
            }
            try {
                const response = await fetch(table.dataset.source+'?'+query, {headers:{Accept:'application/json'}});
                const data = await response.json();
                if (!response.ok) throw new Error(data.message || 'Unable to load tickets. Please try again.');
                const body = document.createElement('tbody'); body.innerHTML = data.rows;
                callback({...data, data:[...body.rows].map(row => [...row.cells].map(cell => cell.innerHTML))});
            } catch (failure) {
                error.textContent=failure.message;
                callback({draw:parameters.draw,recordsTotal:0,recordsFiltered:0,data:[]});
            }
        },
        language:{emptyTable:'No support tickets found.'},
    });
    table.addEventListener('click', event => {
        const button = event.target.closest('.ticket-expand');
        if (!button) return;
        const row = api.row(button.closest('tr'));
        const expanded = !row.child.isShown();
        if (expanded) {
            api.rows().every(function () {
                if (this.index() === row.index() || !this.child.isShown()) return;
                this.child.hide();
                const previousButton = this.node().querySelector('.ticket-expand');
                if (previousButton) {
                    previousButton.setAttribute('aria-expanded', 'false');
                    previousButton.textContent = '＋ Details';
                }
            });
            const details = document.createElement('dl');
            details.className = 'ticket-mobile-details';
            row.data().forEach((html, index) => {
                if (visibleColumns.includes(index)) return;
                const label = document.createElement('dt');
                label.textContent = headings[index];
                const value = document.createElement('dd');
                // Cells are escaped/rendered by the server's existing Blade partials.
                value.innerHTML = html;
                details.append(label, value);
            });
            row.child(details, 'ticket-child-row').show();
        } else row.child.hide();
        button.setAttribute('aria-expanded', String(expanded));
        button.textContent = expanded ? '− Hide details' : '＋ Details';
    });
    mobile.addEventListener('change', () => {
        api.rows().every(function () { this.child.remove(); });
        api.columns().every(function (index) { this.visible(!mobile.matches || visibleColumns.includes(index), false); });
        table.querySelectorAll('.ticket-expand').forEach(button => {
            button.setAttribute('aria-expanded', 'false'); button.textContent = '＋ Details';
        });
        api.columns.adjust();
    });
});
