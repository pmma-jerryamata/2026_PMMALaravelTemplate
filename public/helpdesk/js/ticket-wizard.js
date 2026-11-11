'use strict';
const issueCards = document.querySelectorAll('[data-issue], [data-manual-issue]');
issueCards.forEach(card => card.addEventListener('click', () => {
    issueCards.forEach(other => other.setAttribute('aria-pressed', String(other === card)));
    const description = document.getElementById('support_specifics');
    if (card.dataset.issue) description.value = card.dataset.issue;
    description.focus();
}));
const emailForm = document.querySelector('[data-public-email-form]');
if (emailForm) {
    const email = emailForm.elements.email;
    const fields = emailForm.querySelector('[data-registration-fields]');
    const notice = document.getElementById('email-status');
    const button = emailForm.querySelector('[type=submit]');
    const fullName = emailForm.elements.name;
    fullName.addEventListener('input', () => {
        const start = fullName.selectionStart, end = fullName.selectionEnd;
        fullName.value = fullName.value.toUpperCase();
        fullName.setSelectionRange(start, end);
    });
    let controller, timer, verified = '';
    function registration(required) {
        fields.hidden = !required;
        fields.querySelectorAll('input, select').forEach(field => { field.required = required; field.disabled = !required; });
    }
    registration(false);
    async function verify() {
        if (!email.value || !email.checkValidity()) return false;
        controller?.abort(); controller = new AbortController();
        const value = email.value.trim().toLowerCase();
        notice.textContent = 'Checking your PMMA email…';
        try {
            const response = await fetch(emailForm.dataset.lookupUrl, {method: 'POST', signal: controller.signal,
                headers: {'Content-Type': 'application/json', Accept: 'application/json', 'X-CSRF-TOKEN': emailForm.elements._token.value},
                body: JSON.stringify({email: value})});
            const data = await response.json();
            if (email.value.trim().toLowerCase() !== value) return false;
            if (!response.ok || !data.valid) throw new Error(data.errors?.email?.[0] || data.message || 'Unable to validate email.');
            verified = value;
            registration(data.registration_required === true);
            notice.textContent = data.registration_required ? 'PMMA email accepted. Enter your full name in ALL CAPS and select your department. Cadets / students: choose STUDENTS.' : 'PMMA email accepted. You can submit your ticket.';
            email.removeAttribute('aria-invalid');
            return true;
        } catch (error) {
            if (error.name === 'AbortError') return false;
            notice.textContent = error.message || 'Unable to validate email. Please try again.';
            email.setAttribute('aria-invalid', 'true');
            return false;
        }
    }
    email.addEventListener('input', () => {
        controller?.abort(); clearTimeout(timer); verified = ''; registration(false);
        email.removeAttribute('aria-invalid'); notice.textContent = 'Use your @pmma.edu.ph email.';
        timer = setTimeout(verify, 500);
    });
    emailForm.addEventListener('submit', async event => {
        if (verified === email.value.trim().toLowerCase()) return;
        event.preventDefault(); clearTimeout(timer);
        const valid = await verify();
        button.disabled = false; button.removeAttribute('aria-busy');
        if (valid) emailForm.requestSubmit();
    });
    if (email.value) verify();
}
