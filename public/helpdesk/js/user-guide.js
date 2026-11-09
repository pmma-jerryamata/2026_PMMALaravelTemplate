'use strict';

(() => {
    const guide = document.getElementById('user-guide');
    if (!guide) return;
    const input = guide.querySelector('#guide-search');
    const chapters = [...guide.querySelectorAll('[data-guide-chapter]')].map(article => ({
        article, text: article.textContent.replace(/\s+/g, ' ').toLowerCase(),
        nav: guide.querySelector(`[data-guide-nav="${article.dataset.guideChapter}"]`),
    }));
    const count = guide.querySelector('[data-guide-count]');
    const empty = guide.querySelector('[data-guide-empty]');
    function filter() {
        const words = input.value.trim().toLowerCase().split(/\s+/).filter(Boolean);
        let visible = 0;
        chapters.forEach(({article, text, nav}) => {
            const matches = words.every(word => text.includes(word));
            article.hidden = !matches;
            nav.hidden = !matches;
            if (matches) visible++;
        });
        count.textContent = `${words.length ? `${visible} ${guide.dataset.ofLabel} ` : ''}${chapters.length} ${guide.dataset.topicLabel}`;
        empty.hidden = visible > 0;
    }
    guide.querySelector('[data-guide-tools]').hidden = false;
    input.addEventListener('input', filter);
    // Jump links also work when a search has hidden the requested tutorial.
    guide.addEventListener('click', event => {
        const anchor = event.target.closest('a[href^="#guide-"]');
        if (!anchor) return;
        const target = document.getElementById(anchor.hash.slice(1));
        if (target?.hidden) { input.value = ''; filter(); }
    });
    const toc = guide.querySelector('.guide-toc');
    if (matchMedia('(max-width: 1100px)').matches) toc.open = false;
    guide.querySelector('[data-guide-print]').addEventListener('click', () => window.print());
    // Printing always includes every tutorial in the selected language.
    let printSearch;
    window.addEventListener('beforeprint', () => { printSearch = input.value; input.value = ''; filter(); });
    window.addEventListener('afterprint', () => { input.value = printSearch ?? ''; filter(); });
    filter();
})();
