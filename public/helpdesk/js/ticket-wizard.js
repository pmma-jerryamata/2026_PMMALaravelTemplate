'use strict';
const issueCards = document.querySelectorAll('[data-issue], [data-manual-issue]');
const issueEditor = document.querySelector('[data-issue-editor]');
const issueNext = document.querySelector('[data-issue-next]');
if (issueEditor) {
    const hasDescription = Boolean(document.getElementById('support_specifics').value.trim());
    issueEditor.hidden = !hasDescription;
    if (issueNext) issueNext.disabled = !hasDescription;
}
issueCards.forEach(card => card.addEventListener('click', () => {
    issueCards.forEach(other => other.setAttribute('aria-pressed', String(other === card)));
    const description = document.getElementById('support_specifics');
    if (card.dataset.issue) description.value = card.dataset.issue;
    if (issueEditor) issueEditor.hidden = false;
    if (issueNext) issueNext.disabled = false;
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
    function registration(required, type = 'employee') {
        const cadet = type === 'cadet';
        fields.hidden = !required;
        fullName.required = required;
        fullName.disabled = !required;
        const department = emailForm.elements.registration_department_id;
        department.required = required && !cadet;
        department.disabled = !required || cadet;
        const departmentSection = fields.querySelector('[data-employee-department]');
        if (departmentSection) departmentSection.hidden = cadet;
        const title = fields.querySelector('[data-registration-title]');
        const description = fields.querySelector('[data-registration-description]');
        if (title) title.textContent = cadet ? 'Cadet registration' : 'Employee registration';
        if (description) description.textContent = (cadet
            ? 'Enter your full name in ALL CAPS. You will be registered under STUDENTS automatically.'
            : 'Enter your full name in ALL CAPS and select your department / office.')
            + ' Your record will stay inactive until administrator activation. Your ticket will still be submitted.';
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
            const type = data.registration_type || (value.endsWith('@cdt.pmma.edu.ph') ? 'cadet' : 'employee');
            registration(data.registration_required === true, type);
            notice.textContent = data.registration_required
                ? (type === 'cadet' ? 'Cadet email accepted. Enter your full name in ALL CAPS.' : 'Employee email accepted. Enter your full name in ALL CAPS and select your department.')
                : 'PMMA email accepted. You can submit your ticket.';
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
        email.removeAttribute('aria-invalid'); notice.textContent = 'Employees: @pmma.edu.ph · Cadets: @cdt.pmma.edu.ph.';
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
