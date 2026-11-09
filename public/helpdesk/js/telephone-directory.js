'use strict';

(() => {
    const directory = document.getElementById('telephone-directory');
    if (!directory) return;
    const search = directory.querySelector('#telephone-search');
    const rows = [...directory.querySelectorAll('[data-directory-entry]')];
    const entries = rows.map(row => ({row, text: row.textContent.replace(/\s+/g, ' ').trim().toLowerCase()}));
    const count = directory.querySelector('[data-directory-count]');
    const empty = directory.querySelector('[data-directory-empty]');
    directory.querySelector('[data-directory-search]').hidden = false;
    function filter() {
        const terms = search.value.trim().toLowerCase().split(/\s+/).filter(Boolean);
        let visible = 0;
        entries.forEach(({row, text}) => {
            row.hidden = !terms.every(term => text.includes(term));
            if (!row.hidden) visible++;
        });
        count.textContent = terms.length ? `${visible} of ${rows.length} offices and units` : `${rows.length} offices and units`;
        empty.hidden = visible !== 0;
        directory.querySelector('.telephone-table-wrap').hidden = visible === 0;
    }
    search.addEventListener('input', filter);
    filter();
})();
