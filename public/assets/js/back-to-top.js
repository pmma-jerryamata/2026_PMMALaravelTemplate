(() => {
    const button = document.querySelector('[data-back-to-top]');
    if (!button) return;
    const update = () => { button.hidden = window.scrollY < 300; };
    window.addEventListener('scroll', update, {passive: true});
    window.addEventListener('pageshow', update);
    button.addEventListener('click', () => {
        const target = document.getElementById('main-content');
        if (target) {
            if (!target.hasAttribute('tabindex')) target.setAttribute('tabindex', '-1');
            target.focus({preventScroll: true});
        }
        window.scrollTo({top: 0, behavior: window.matchMedia('(prefers-reduced-motion: reduce)').matches ? 'instant' : 'smooth'});
    });
    update();
})();
