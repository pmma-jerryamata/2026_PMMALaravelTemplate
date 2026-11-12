(() => {
    const dialog = document.querySelector('[data-request-wizard]');
    if (!dialog) return;
    const form = dialog.querySelector('form');
    const steps = [...form.querySelectorAll('[data-wizard-step]')];
    const issues = JSON.parse(document.getElementById('request-issue-options').textContent);
    const choices = form.querySelector('[data-issue-options]');
    const description = form.elements.description;
    const custom = form.querySelector('[data-custom-description]');
    let current = 0, selectedCategory = '', sending = false;
    function show(index) {
        current = index;
        steps.forEach((section, i) => { section.hidden = i !== index; });
        dialog.querySelector('[data-wizard-progress]').textContent = `Step ${index + 1} of 3 ? ${['Service', 'Concern', 'Your details'][index]}`;
        dialog.scrollTo({top: 0});
        const heading = steps[index].querySelector('h2'); heading.tabIndex = -1; heading.focus();
    }
    function renderIssues() {
        const category = form.elements.category.value;
        if (category === selectedCategory) return;
        selectedCategory = category; choices.replaceChildren();
        const previous = form.elements.issue_name.value;
        for (const [label, text] of [...(issues[category] || []), ['Other / Write my own', '']]) {
            const button = document.createElement('button'); button.type = 'button'; button.className = 'wizard-issue'; button.textContent = label;
            button.setAttribute('aria-pressed', String(label === previous));
            button.addEventListener('click', () => {
                choices.querySelectorAll('button').forEach(other => other.setAttribute('aria-pressed', String(other === button)));
                description.value = text; form.elements.issue_name.value = text ? label : ''; custom.hidden = !!text;
                if (text) show(2); else description.focus();
            });
            choices.append(button);
        }
        custom.hidden = !!previous;
    }
    form.querySelectorAll('[name=category]').forEach(input => input.addEventListener('change', () => {
        form.elements.issue_name.value = ''; description.value = ''; renderIssues(); show(1);
    }));
    form.querySelectorAll('[data-wizard-next]').forEach(button => button.addEventListener('click', () => {
        if (current === 0) { if (!form.elements.category.value) { steps[0].querySelector('input').reportValidity(); return; } renderIssues(); }
        if (current === 1 && !description.reportValidity()) return;
        show(Math.min(2, current + 1));
    }));
    form.querySelectorAll('[data-wizard-back]').forEach(button => button.addEventListener('click', () => show(Math.max(0, current - 1))));
    // Preserve one token through validation failures and uncertain network retries.
    form.addEventListener('invalid', event => {
        const step = event.target.closest('[data-wizard-step]');
        if (step && step.hidden) show(Number(step.dataset.wizardStep));
    }, true);
    form.addEventListener('submit', event => {
        if (sending) { event.preventDefault(); return; }
        sending = true; form.querySelector('[data-submit-status]').textContent = 'Submitting your request. Please wait?';
    });
    window.addEventListener('pageshow', () => { sending = false; form.querySelectorAll('[type=submit]').forEach(button => { button.disabled = false; button.removeAttribute('aria-busy'); }); });
    dialog.addEventListener('cancel', event => { event.preventDefault(); location.assign(dialog.querySelector('[aria-label="Close request wizard"]').href); });
    if (typeof dialog.showModal === 'function') { dialog.removeAttribute('open'); dialog.showModal(); }
    if (form.elements.category.value) renderIssues();
    show(form.elements.description.value || document.querySelector('.portal-alert.error') ? 2 : form.elements.category.value ? 1 : 0);
})();
