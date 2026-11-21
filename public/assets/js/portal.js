const menu = document.querySelector('.mobile-menu-toggle');
menu?.addEventListener('click', () => {
    const expanded = menu.getAttribute('aria-expanded') !== 'true';
    menu.setAttribute('aria-expanded', String(expanded));
    document.getElementById('main-navigation')?.classList.toggle('active', expanded);
});
document.querySelectorAll('[data-copy]').forEach(button => button.addEventListener('click', async () => {
    let copied = false;
    try {
        if (navigator.clipboard?.writeText) {
            await navigator.clipboard.writeText(button.dataset.copy);
            copied = true;
        }
    } catch { /* Try the compatibility path when clipboard permission is denied. */ }
    if (!copied) {
        const field = document.createElement('textarea');
        field.value = button.dataset.copy;
        field.setAttribute('readonly', '');
        field.style.cssText = 'position:fixed;left:0;top:0;opacity:0;pointer-events:none;font-size:16px';
        document.body.append(field);
        field.select();
        field.setSelectionRange(0, field.value.length);
        try { copied = document.execCommand('copy'); } catch { copied = false; }
        field.remove();
        button.focus();
    }
    const message = copied ? 'Reference Copied' : 'Copy unavailable. Select and copy your reference number.';
    if (copied) {
        const icon = button.querySelector('i');
        if (icon) icon.className = 'bi bi-check-lg';
        const label = button.querySelector('span');
        if (label) label.textContent = 'Reference Copied';
    } else {
        const reference = document.querySelector('.ticket-reference');
        if (reference) {
            const range = document.createRange();
            range.selectNodeContents(reference);
            const selection = getSelection();
            selection.removeAllRanges();
            selection.addRange(range);
        }
    }
    button.title = message;
    button.setAttribute('aria-label', message);
    const status = document.getElementById('copy-status');
    if (status) status.textContent = message;
}));
document.querySelectorAll('form[method="post"]').forEach(form => form.addEventListener('submit', () => {
    const button = form.querySelector('button[type="submit"], button:not([type])');
    if (button) { button.disabled = true; button.setAttribute('aria-busy', 'true'); button.title = 'Please wait…'; }
}));
