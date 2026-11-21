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
        event.stopPropagation();
        disclosure.open = false;
        summary.focus();
    });

    document.addEventListener('click', (event) => {
        if (!disclosure.contains(event.target)) disclosure.open = false;
    });
}

const navToggle = document.querySelector('.registrar-nav-toggle');

if (navToggle) {
    const navigation = navToggle.closest('.registrar-nav');
    navToggle.hidden = false;

    navToggle.addEventListener('click', () => {
        const expanded = navToggle.getAttribute('aria-expanded') === 'true';
        navToggle.setAttribute('aria-expanded', String(!expanded));
    });

    navigation.addEventListener('keydown', (event) => {
        if (event.key !== 'Escape' || navToggle.getAttribute('aria-expanded') !== 'true') return;
        navToggle.setAttribute('aria-expanded', 'false');
        if (navToggle.offsetWidth) navToggle.focus();
    });

    document.addEventListener('click', (event) => {
        if (!navigation.contains(event.target)) navToggle.setAttribute('aria-expanded', 'false');
    });
}
