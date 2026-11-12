(() => {
    const form = document.getElementById('job-report-form');
    if (!form) return;
    const intro = form.elements.introduction, automatic = form.elements.intro_default;
    intro.addEventListener('input', () => { automatic.value = intro.value.trim() ? '0' : '1'; });
    function refresh() {
        if (automatic.value !== '1') return;
        const format = value => new Intl.DateTimeFormat('en-US', {year:'numeric',month:'long',day:'numeric'}).format(new Date(value+'T12:00:00'));
        if (!form.elements.from.value || !form.elements.to.value) return;
        const period = format(form.elements.from.value)+' - '+format(form.elements.to.value);
        const scope = ['technician_id','department_id','task_id','status'].map(name => form.elements[name].selectedOptions[0].textContent.trim());
        intro.value = 'Submitted herewith is the job requests report '+(form.elements.all_dates.checked ? 'covering all recorded dates' : 'for the period as of '+period)+'. This report covers '+scope.slice(0,3).join(', ' )+' ('+scope[3]+').';
    }
    ['from','to','all_dates','technician_id','department_id','task_id','status'].forEach(name => form.elements[name].addEventListener('change', refresh));
    form.addEventListener('submit', refresh);
    const button = form.querySelector('[data-jobs-preview]');
    if (button && form.checkValidity()) form.requestSubmit(button);
})();
