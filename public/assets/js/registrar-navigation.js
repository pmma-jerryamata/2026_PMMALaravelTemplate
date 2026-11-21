const disclosure = document.querySelector('.registrar-nav-menu');

if (disclosure) {
    const summary = disclosure.querySelector('summary');

    summary.addEventListener('click', (event) => {
        if (event.pointerType === 'mouse' && disclosure.open) event.preventDefault();
    });

    disclosure.addEventListener('pointerenter', (event) => {
        if (event.pointerType === 'mouse') disclosure.open = true;
    });

    disclosure.addEventListener('pointerleave', (event) => {
        if (event.pointerType === 'mouse' && !disclosure.contains(document.activeElement)) {
            disclosure.open = false;
        }
    });

    disclosure.addEventListener('focusout', (event) => {
        if (!disclosure.contains(event.relatedTarget)) disclosure.open = false;
    });

    disclosure.addEventListener('keydown', (event) => {
        if (event.key !== 'Escape' || !disclosure.open) return;
        disclosure.open = false;
        summary.focus();
    });

    document.addEventListener('click', (event) => {
        if (!disclosure.contains(event.target)) disclosure.open = false;
    });
}
