'use strict';
const issueCards = document.querySelectorAll('[data-issue], [data-manual-issue]');
const issueEditor = document.querySelector('[data-issue-editor]');
const issueNext = document.querySelector('[data-issue-next]');
const issueActions = document.querySelector('[data-issue-actions]');
if (issueEditor) {
    const description = document.getElementById('support_specifics');
    const hasDescription = Boolean(description.value.trim()) && !description.closest('form')?.elements.issue_name?.value;
    issueEditor.hidden = !hasDescription;
    if (issueActions) issueActions.hidden = !hasDescription;
    if (issueNext) issueNext.disabled = !hasDescription;
}
issueCards.forEach(card => card.addEventListener('click', event => {
    event.preventDefault();
    issueCards.forEach(other => other.setAttribute('aria-pressed', String(other === card)));
    const description = document.getElementById('support_specifics');
    const form = description.closest('form');
    if (card.dataset.issue) {
        description.value = card.dataset.issue;
        if (form?.elements.issue_name) form.elements.issue_name.value = card.dataset.issueName || '';
        if (issueEditor) issueEditor.hidden = true;
        if (issueNext) issueNext.disabled = false;
        form?.requestSubmit();
        return;
    }
    if (form?.elements.issue_name) form.elements.issue_name.value = '';
    if (issueEditor) issueEditor.hidden = false;
    if (issueActions) issueActions.hidden = false;
    if (issueNext) issueNext.disabled = false;
    description.focus();
}));
const wizardDialog = document.querySelector('.wizard-fullscreen');
if (wizardDialog) wizardDialog.focus({preventScroll: true});
const emailForm = document.querySelector('[data-public-email-form]');
if (emailForm) {
    const email = emailForm.elements.email;
    const fields = emailForm.querySelector('[data-registration-fields]');
    const notice = document.getElementById('email-status');
    const button = emailForm.querySelector('[type=submit]');
    const nameFields = [emailForm.elements.first_name, emailForm.elements.last_name];
    nameFields.forEach(field => field.addEventListener('input', () => {
        const start = field.selectionStart, end = field.selectionEnd;
        field.value = field.value.toUpperCase();
        field.setSelectionRange(start, end);
    }));
    let controller, timer, verified = '';
    function registration(required, type = 'employee') {
        const cadet = type === 'cadet';
        fields.hidden = !required;
        nameFields.forEach(field => { field.required = required; field.disabled = !required; });
        const department = emailForm.elements.registration_department_id;
        department.required = required && !cadet;
        department.disabled = !required || cadet;
        const departmentSection = fields.querySelector('[data-employee-department]');
        if (departmentSection) departmentSection.hidden = cadet;
        const title = fields.querySelector('[data-registration-title]');
        const description = fields.querySelector('[data-registration-description]');
        if (title) title.textContent = cadet ? 'Cadet registration' : 'Employee registration';
        if (description) description.textContent = (cadet
            ? 'Enter your first and last names in ALL CAPS. You will be registered under STUDENTS automatically.'
            : 'Enter your first and last names in ALL CAPS and select your department / office.')
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
                ? (type === 'cadet' ? 'Cadet email accepted. Enter your first and last names in ALL CAPS.' : 'Employee email accepted. Enter your first and last names in ALL CAPS and select your department.')
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
