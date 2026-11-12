(() => {
    const dialog = document.getElementById('tech-create-dialog');
    const trigger = document.querySelector('[data-open-create-ticket]');
    if (!dialog || !trigger || !dialog.showModal) return;
    const open = () => { dialog.showModal(); document.body.classList.add('tech-modal-open'); };
    trigger.addEventListener('click', event => { event.preventDefault(); open(); });
    dialog.querySelectorAll('[data-close-create-ticket]').forEach(button => button.addEventListener('click', () => dialog.close()));
    dialog.addEventListener('close', () => { document.body.classList.remove('tech-modal-open'); trigger.focus(); });
    const form = dialog.querySelector('form');
    const department = form.elements.department_id, employee = form.elements.employee_id;
    const employees = [...employee.options].slice(1);
    const notice = document.getElementById('create-employee-status');
    function filter() {
        const selected = employee.value;
        const matching = employees.filter(option => option.dataset.department === department.value);
        employee.replaceChildren(new Option('Choose a requester', ''), ...matching);
        employee.value = matching.some(option => option.value === selected) ? selected : '';
        employee.disabled = !department.value || !matching.length;
        notice.textContent = !department.value ? 'Choose a department first.' : matching.length ? '' : 'No active requesters in this department. Please contact the administrator.';
        employee.setCustomValidity('');
    }
    department.addEventListener('change', filter); filter();
    form.addEventListener('submit', event => {
        if (employee.disabled || !employee.value) { event.preventDefault(); notice.textContent = 'Choose a department with an active requester.'; department.focus(); return; }
        if (!form.checkValidity()) return;
        const button = form.querySelector('[type=submit]'); button.disabled = true; button.setAttribute('aria-busy', 'true'); button.textContent = 'Creating ticket...';
    });
    if (dialog.hasAttribute('data-reopen')) open();
})();
