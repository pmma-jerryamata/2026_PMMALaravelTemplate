'use strict';

function configureLink(link) {
    const href = link.getAttribute('href')?.trim();
    if (!href || href.startsWith('#') || link.hasAttribute('download')) return;
    if (!['http:', 'https:'].includes(link.protocol)) return;

    link.target = '_blank';
    link.relList.add('noopener');
}

function configureLinks(root) {
    if (root instanceof HTMLAnchorElement) configureLink(root);
    root.querySelectorAll?.('a[href]').forEach(configureLink);
}

configureLinks(document);
new MutationObserver(records => {
    for (const record of records) {
        if (record.type === 'attributes') configureLinks(record.target);
        for (const node of record.addedNodes) configureLinks(node);
    }
}).observe(document.body, { childList: true, subtree: true, attributes: true, attributeFilter: ['href'] });
