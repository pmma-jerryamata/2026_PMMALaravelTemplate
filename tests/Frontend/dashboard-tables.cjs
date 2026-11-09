// DOM integration tests use the actual bundled jQuery/DataTables files; no browser or live database is controlled.
const assert = require('node:assert/strict');
const fs = require('node:fs');
const path = require('node:path');
let JSDOM;
try { ({JSDOM} = require('jsdom')); }
catch { ({JSDOM} = require(path.join(process.env.LOCALAPPDATA, 'PMMA-Support-Local/dom-tests/node_modules/jsdom'))); }
const project = path.resolve(__dirname, '../..');
const source = relative => fs.readFileSync(path.join(project, relative), 'utf8');
const scripts = ['public/helpdesk/vendor/datatables/jquery.min.js', 'public/helpdesk/vendor/datatables/dataTables.min.js',
    'public/helpdesk/js/dashboard-tables.js'].map(source);

async function until(predicate) {
    const deadline = Date.now() + 2500;
    while (!predicate()) {
        if (Date.now() > deadline) throw new Error('Timed out waiting for a table draw.');
        await new Promise(resolve => setTimeout(resolve, 5));
    }
}

function fixture({mobile = false, rows = '', server = false, fetch} = {}) {
    const dom = new JSDOM(`<!doctype html><html><body class="admin-workspace"><section class="portal-panel">
        <div class="dashboard-table-wrap"><table id="test-table" class="dashboard-table" data-dashboard-table
        data-table-order="0:asc" ${server ? 'data-table-source="/table-data" data-table-actions="true"' : ''}>
        <thead><tr><th>Date</th><th>Accomplishments</th>${server ? '<th>Actions</th>' : ''}</tr></thead>
        <tbody>${rows}</tbody></table></div><nav class="adapter-pagination">Fallback pagination</nav></section></body></html>`,
        {url: 'http://unit.test/dashboard', runScripts: 'outside-only', pretendToBeVisual: true});
    const {window} = dom;
    const failures = [];
    window.addEventListener('error', event => failures.push(event.error || event.message));
    window.alert = message => { throw new Error(message); };
    window.matchMedia = query => ({matches: mobile, media: query, addEventListener() {}, removeEventListener() {}});
    window.fetch = fetch;
    scripts.forEach(script => window.eval(script));
    const api = new window.DataTable.Api(window.document.getElementById('test-table'));
    return {window, api, failures, close: () => window.close()};
}

async function main() {
    let checks = 0;
    const confirmation = new JSDOM(`<form data-ticket-delete-confirmation data-confirmation="DELETE #2998">
        <input name="confirmation"><input name="password" type="password">
        <input name="acknowledge" type="checkbox"><button type="submit">Delete</button></form>`,
        {runScripts: 'outside-only'});
    confirmation.window.eval(scripts[2]);
    const deleteForm = confirmation.window.document.querySelector('form');
    const deleteButton = deleteForm.querySelector('button');
    const updateConfirmation = () => deleteForm.dispatchEvent(new confirmation.window.Event('input', {bubbles: true}));
    assert.equal(deleteButton.disabled, true); checks++;
    deleteForm.elements.confirmation.value = 'DELETE #2997';
    deleteForm.elements.password.value = 'test-password';
    deleteForm.elements.acknowledge.checked = true;
    updateConfirmation();
    assert.equal(deleteButton.disabled, true); checks++;
    const rejected = new confirmation.window.Event('submit', {cancelable: true});
    deleteForm.dispatchEvent(rejected);
    assert.equal(rejected.defaultPrevented, true); checks++;
    deleteForm.elements.confirmation.value = 'DELETE #2998';
    updateConfirmation();
    assert.equal(deleteButton.disabled, false); checks++;
    deleteForm.elements.acknowledge.checked = false;
    updateConfirmation();
    assert.equal(deleteButton.disabled, true); checks++;
    deleteForm.elements.acknowledge.checked = true;
    updateConfirmation();
    const accepted = new confirmation.window.Event('submit', {cancelable: true});
    deleteForm.dispatchEvent(accepted);
    assert.equal(accepted.defaultPrevented, false); checks++;
    assert.equal(deleteButton.disabled, true); checks++;
    confirmation.window.close();
    const clientRows = Array.from({length: 31}, (_, index) => `<tr><td data-order="2026-10-${String(index + 1).padStart(2, '0')}">October ${index + 1}, 2026</td><td>${index === 30 ? 'Unique final accomplishment' : 'Technical support ' + index}</td></tr>`).join('');
    const desktop = fixture({rows: clientRows});
    assert.equal(desktop.api.page.info().recordsTotal, 31); checks++;
    assert.equal(desktop.api.page.info().pages, 3); checks++;
    desktop.api.search('Unique final').draw();
    assert.equal(desktop.api.rows({search: 'applied'}).count(), 1); checks++;
    assert.match(desktop.window.document.querySelector('tbody').textContent, /Unique final accomplishment/); checks++;
    desktop.api.search('').order([[0, 'desc']]).draw();
    assert.match(desktop.window.document.querySelector('tbody tr').textContent, /October 31, 2026/); checks++;
    assert.equal(desktop.window.document.querySelector('.dashboard-mobile-sort').hidden, true); checks++;
    assert.equal(desktop.failures.length, 0); checks++;
    desktop.close();

    const mobile = fixture({mobile: true, rows: clientRows});
    const controls = mobile.window.document.querySelector('.dashboard-mobile-sort');
    assert.equal(controls.hidden, false); checks++;
    assert.equal(mobile.window.document.querySelector('thead').inert, true); checks++;
    assert.equal(mobile.window.document.querySelector('.dt-scroll'), null); checks++;
    const select = controls.querySelector('select'); select.value = '1';
    select.dispatchEvent(new mobile.window.Event('change', {bubbles: true}));
    assert.equal(mobile.api.order()[0][0], 1); checks++;
    controls.querySelector('button').click();
    assert.equal(mobile.api.order()[0][1], 'desc'); checks++;
    mobile.api.search('Unique final').draw();
    const cells = mobile.window.document.querySelectorAll('tbody tr td');
    assert.equal(cells[0].dataset.label, 'Date'); checks++;
    assert.equal(cells[1].dataset.label, 'Accomplishments'); checks++;
    assert.equal(mobile.failures.length, 0); checks++;
    mobile.close();

    const records = Array.from({length: 35}, (_, index) => ['Record ' + index, index === 34 ? 'Unique final record' : 'Standard entry', '<a href="/edit">Edit</a>']);
    const requests = [];
    const server = fixture({mobile: true, server: true,
        rows: records.slice(0, 15).map(row => '<tr>' + row.map(cell => '<td>' + cell + '</td>').join('') + '</tr>').join(''), fetch: async input => {
        const url = new URL(String(input)); requests.push(url);
        const query = url.searchParams.get('search[value]') || '';
        const filtered = records.filter(row => row.join(' ').includes(query));
        const start = Number(url.searchParams.get('start'));
        const length = Number(url.searchParams.get('length'));
        return {ok: true, json: async () => ({draw: Number(url.searchParams.get('draw')),
            recordsTotal: records.length, recordsFiltered: filtered.length,
            rows: filtered.slice(start, start + length).map(row => '<tr>' + row.map(cell => '<td>' + cell + '</td>').join('') + '</tr>').join('')})};
    }});
    await until(() => server.api.page.info().recordsTotal === 35);
    assert.equal(server.window.document.querySelector('.adapter-pagination').hidden, true); checks++;
    server.api.page('next').draw('page');
    await until(() => requests.some(url => url.searchParams.get('start') === '15'));
    assert.equal(server.api.page.info().page, 1); checks++;
    const search = server.window.document.querySelector('.dt-search input');
    search.value = 'Unique final record'; search.dispatchEvent(new server.window.Event('input', {bubbles: true}));
    await until(() => server.api.page.info().recordsDisplay === 1);
    assert.match(server.window.document.querySelector('tbody').textContent, /Record 34/); checks++;
    assert.equal(server.window.document.querySelectorAll('tbody td')[2].dataset.label, 'Actions'); checks++;
    assert.equal(server.window.document.querySelector('.dashboard-mobile-sort select').options.length, 2); checks++;
    assert.equal(server.failures.length, 0); checks++;
    server.close();

    const empty = fixture({mobile: true, rows: '<tr><td colspan="2">No records</td></tr>'});
    assert.equal(empty.api.rows().count(), 0); checks++;
    assert.ok(empty.window.document.querySelector('td.dt-empty')); checks++;
    assert.equal(empty.failures.length, 0); checks++;
    empty.close();

    const expired = fixture({mobile: true, server: true, fetch: async () => ({ok: false, status: 401, json: async () => ({message: 'Unauthenticated'})})});
    await until(() => expired.window.document.querySelector('.dashboard-table-notice').hidden === false);
    assert.match(expired.window.document.querySelector('.dashboard-table-notice').textContent, /session expired/); checks++;
    assert.equal(expired.api.rows().count(), 0); checks++;
    assert.equal(expired.failures.length, 0); checks++;
    expired.close();
    console.log(`PASS: ${checks} DataTables DOM checks (search, sorting, pagination, AJAX, mobile controls, redraw labels and empty tables).`);
}
main().catch(error => { console.error(error); process.exitCode = 1; });
