const staffMenu = document.querySelector('.admin-menu-toggle');
const staffNavigation = document.getElementById('staff-sidebar');
const staffBackdrop = document.querySelector('.admin-menu-backdrop');
const staffShell = document.querySelector('.admin-shell');
const staffMobile = matchMedia('(max-width: 768px)');
function closeStaffMenu() {
    staffMenu?.setAttribute('aria-expanded', 'false');
    staffNavigation?.classList.remove('is-open');
    if (staffBackdrop) staffBackdrop.hidden = true;
    if (staffShell) staffShell.inert = false;
    if (staffNavigation) staffNavigation.inert = staffMobile.matches;
    document.body.classList.remove('staff-menu-open');
}
staffMenu?.addEventListener('click', () => {
    staffMenu.setAttribute('aria-expanded', 'true');
    staffNavigation?.classList.add('is-open');
    if (staffNavigation) staffNavigation.inert = false;
    if (staffBackdrop) staffBackdrop.hidden = false;
    if (staffShell) staffShell.inert = true;
    document.body.classList.add('staff-menu-open');
    document.querySelector('.admin-menu-close')?.focus();
});
function dismissStaffMenu() { closeStaffMenu(); staffMenu?.focus(); }
staffBackdrop?.addEventListener('click', dismissStaffMenu);
document.querySelector('.admin-menu-close')?.addEventListener('click', dismissStaffMenu);
document.addEventListener('keydown', event => {
    if (event.key === 'Escape' && staffMenu?.getAttribute('aria-expanded') === 'true') {
        closeStaffMenu();
        staffMenu.focus();
    }
    if (event.key === 'Tab' && staffMenu?.getAttribute('aria-expanded') === 'true') {
        const controls = [...staffNavigation.querySelectorAll('a[href], button')];
        const first = controls[0], last = controls.at(-1);
        if (event.shiftKey && document.activeElement === first) { event.preventDefault(); last.focus(); }
        else if (!event.shiftKey && document.activeElement === last) { event.preventDefault(); first.focus(); }
    }
});
staffMobile.addEventListener('change', closeStaffMenu);
closeStaffMenu();

const settings = JSON.parse(document.getElementById('settings-options')?.textContent || 'null');
const tickets = JSON.parse(document.getElementById('ticket-options')?.textContent || 'null');
const auditTable = document.getElementById('activity-log-table');
if (auditTable) {
    const filterForm = document.getElementById('audit-filters');
    const auditError = document.getElementById('audit-error');
    const audit = new DataTable(auditTable, {
        serverSide:true, processing:true, pageLength:25, lengthMenu:[10,25,50,100], order:[[0,'desc']], searchDelay:350,
        ajax:async (parameters, callback) => {
            auditError.textContent='';
            const query = new URLSearchParams(new FormData(filterForm));
            query.set('draw',parameters.draw);query.set('start',parameters.start);query.set('length',parameters.length);
            query.set('search[value]',parameters.search.value);
            query.set('order[0][column]',parameters.order[0]?.column ?? 0);query.set('order[0][dir]',parameters.order[0]?.dir ?? 'desc');
            try {
                const response = await fetch(auditTable.dataset.source+'?'+query,{headers:{Accept:'application/json'}});
                const data = await response.json();
                if(!response.ok) throw new Error(data.errors ? Object.values(data.errors).flat().join(' ') : 'Unable to load activity logs.');
                callback(data);
            } catch(error) { auditError.textContent=error.message;callback({draw:parameters.draw,recordsTotal:0,recordsFiltered:0,data:[]}); }
        },
        columns:['time','user','ip','operation','outcome','http','details'].map(key=>({data:key,render:DataTable.render.text()})),
        language:{emptyTable:'No activity logs found'},
    });
    filterForm.addEventListener('submit',event=>{event.preventDefault();audit.ajax.reload();});
    filterForm.addEventListener('reset',()=>setTimeout(()=>audit.ajax.reload(),0));
}
document.querySelectorAll('[data-datatable]').forEach(table => new DataTable(table, {
    pageLength: 10, lengthMenu: [10, 25, 50, 100], order: [],
    columnDefs: [{targets: -1, orderable: false}, ...(table.querySelector('th')?.textContent === 'Reorder' ? [{targets: 0, orderable: false}] : [])],
    language: {emptyTable: 'No records available', search: 'Search:', lengthMenu: 'Show _MENU_ entries', paginate: {
        first: '<i class="bi bi-chevron-double-left" aria-hidden="true"></i>', previous: '<i class="bi bi-chevron-left" aria-hidden="true"></i>',
        next: '<i class="bi bi-chevron-right" aria-hidden="true"></i>', last: '<i class="bi bi-chevron-double-right" aria-hidden="true"></i>',
    }},
}));
const settingsTabs = [...document.querySelectorAll('[data-settings-tab]')];
function selectSettingsTab(name, focus = false) {
    if (!settingsTabs.some(tab => tab.dataset.settingsTab === name)) name = 'general';
    settingsTabs.forEach(tab => {
        const selected = tab.dataset.settingsTab === name;
        tab.setAttribute('aria-selected', String(selected));
        tab.tabIndex = selected ? 0 : -1;
        if (selected && focus) tab.focus();
    });
    document.querySelectorAll('[data-settings-panel]').forEach(panel => {
        panel.hidden = panel.dataset.settingsPanel !== name;
        if (!panel.hidden) panel.querySelectorAll('[data-datatable]').forEach(table => new DataTable.Api(table).columns.adjust());
    });
    history.replaceState(null, '', '#'+name);
}
settingsTabs.forEach((tab, index) => {
    tab.addEventListener('click', () => selectSettingsTab(tab.dataset.settingsTab));
    tab.addEventListener('keydown', event => {
        let target;
        if (event.key === 'ArrowRight') target = (index + 1) % settingsTabs.length;
        if (event.key === 'ArrowLeft') target = (index - 1 + settingsTabs.length) % settingsTabs.length;
        if (event.key === 'Home') target = 0;
        if (event.key === 'End') target = settingsTabs.length - 1;
        if (target !== undefined) { event.preventDefault(); selectSettingsTab(settingsTabs[target].dataset.settingsTab, true); }
    });
});
if (settingsTabs.length) selectSettingsTab(location.hash.slice(1));
const dialog = document.getElementById('editor-dialog');
const form = document.getElementById('editor-form');
const fields = document.getElementById('editor-fields');
const errors = document.getElementById('editor-errors');
let saveUrl, saveMethod;
const activeOptions = [{value: '1', label: 'Active'}, {value: '0', label: 'Inactive'}];
const options = (records, key = 'name') => [{value: '', label: 'Choose…'}, ...records.map(r => ({value: r.id, label: r[key]}))];
function updateActionRequirement(targetForm) {
    const action = targetForm?.elements.action_taken;
    const status = targetForm?.elements.status;
    if (!action || !status) return;
    action.required = status.value !== 'Closed';
    action.placeholder = action.required ? 'Describe the action taken or progress update.' : 'Optional when closing a ticket.';
    const label = targetForm.querySelector(`label[for="${action.id}"]`);
    if (label) label.textContent = `Action Taken${action.required ? '' : ' (optional for Closed)'} — visible in public tracking`;
}
document.addEventListener('change', event => {
    if (event.target.matches('select[name="status"]')) updateActionRequirement(event.target.form);
});
document.querySelectorAll('form').forEach(updateActionRequirement);

function openEditor(title, url, schema, record = {}, method = 'POST') {
    dialog.classList.toggle('department-dialog', schema.some(field => field.name === 'code'));
    document.getElementById('editor-title').textContent = title;
    saveUrl = url; saveMethod = method; fields.replaceChildren(); errors.replaceChildren();
    for (const spec of schema) {
        if (spec.section) {
            const heading = document.createElement('h3');
            heading.className = 'editor-section-heading';
            heading.textContent = spec.section;
            fields.append(heading);
        }
        const group = document.createElement('div');
        if (spec.wide) group.className = 'form-field-wide';
        const label = document.createElement('label'); label.htmlFor = 'editor-' + spec.name; label.textContent = spec.label;
        const input = document.createElement(spec.options ? 'select' : spec.type === 'textarea' ? 'textarea' : 'input');
        input.name = spec.name; input.id = label.htmlFor; input.required = !!spec.required;
        if (spec.options) spec.options.forEach(o => { const opt = document.createElement('option'); opt.value = o.value; opt.textContent = o.label; input.append(opt); });
        else if (spec.type === 'textarea') input.rows = 4;
        else input.type = spec.type || 'text';
        if (spec.max) input.maxLength = spec.max;
        if (spec.type === 'password') input.autocomplete = spec.name === 'current_password' ? 'current-password' : 'new-password';
        const val = record[spec.name];
        if (spec.type === 'file') input.accept = 'image/jpeg,image/png,image/webp';
        else input.value = spec.name === 'is_active' ? (val === false || val === 0 ? '0' : '1') : (Array.isArray(val) ? val.join('\n') : val ?? '');
        group.append(label, input); fields.append(group);
        if (spec.type === 'file' && record.photo_path) {
            const img = document.createElement('img'); img.src = '/dashboard/technicians/' + record.id + '/photo'; img.alt = 'Current technician photo'; img.width = 64; img.height = 64; img.className = 'technician-photo'; group.append(img);
        }
    }
    if (saveUrl === tickets.storeUrl) {
        const token = document.createElement('input'); token.type = 'hidden'; token.name = 'submission_token'; token.value = crypto.randomUUID(); fields.append(token);
    }
    updateActionRequirement(form);
    dialog.showModal();
    if (schema.some(field => field.name === 'user_access')) {
        const access = form.elements.user_access;
        const username = form.elements.username;
        const password = form.elements.password;
        const confirmation = form.elements.password_confirmation;
        const updateRequirements = () => {
            const accountNeeded = !!record.user_id || access.value === '1' || username.value.trim() !== '' || password.value !== '';
            username.required = accountNeeded;
            password.required = !record.user_id && accountNeeded;
            confirmation.required = password.value !== '';
        };
        access.addEventListener('change', updateRequirements);
        username.addEventListener('input', updateRequirements);
        password.addEventListener('input', updateRequirements);
        form.querySelector('label[for="editor-password"]').textContent = record.user_id ? 'New Password (leave blank to keep current)' : 'Password (required for a new login account)';
        updateRequirements();
    }
}
document.querySelectorAll('[data-close-dialog]').forEach(b => b.addEventListener('click', () => dialog.close()));
function settingSchema(type) {
    if (type === 'departments') return [{name:'name',label:'Department / Office Name',required:true,max:255,wide:true},{name:'code',label:'Code',max:40},{name:'is_active',label:'Status',options:activeOptions}];
    if (type === 'technicians') return [
        {name:'name',label:'Full Name',required:true,max:120},{name:'email',label:'Email Address',type:'email',required:true,max:254},
        {name:'employee_number',label:'Employee Number',max:100},{name:'department_id',label:'Department / Office',options:options(settings.departments)},
        {name:'phone',label:'Phone',max:40},{name:'position',label:'Position',max:255},{name:'specialization',label:'Specialization',max:255},
        {name:'is_active',label:'Profile Status',options:activeOptions},
        {name:'user_access',label:'User Access',section:'Login Access',options:[{value:'0',label:'Disabled — No Login Access'},{value:'1',label:'Enabled — Staff Dashboard'}]},
        {name:'username',label:'Login Username',max:100},
        {name:'password',label:'Password (leave blank to keep current)',type:'password'},
        {name:'password_confirmation',label:'Confirm Password',type:'password'},
        {name:'photo',label:'Profile Photo (JPG, PNG, WebP; max 2 MB)',section:'Profile Details',type:'file',wide:true},{name:'bio',label:'Profile / Bio',type:'textarea',wide:true,max:5000}];
    return [{name:'title',label:'Service Title',required:true,max:255},{name:'slug',label:'Service Key (letters, numbers, hyphens)',required:true,max:100},{name:'icon',label:'Bootstrap Icon Name (e.g. wifi)',required:true,max:80},{name:'is_active',label:'Status',options:activeOptions},{name:'description',label:'Description',type:'textarea',required:true,wide:true,max:2000},{name:'items',label:'Supported Concerns (one per line)',type:'textarea',required:true,wide:true,max:5000}];
}
document.addEventListener('click', async event => {
    const timelineButton = event.target.closest('[data-timeline-url]');
    if (timelineButton) {
        const timelineDialog = document.getElementById('timeline-dialog');
        const content = document.getElementById('timeline-content');
        content.textContent = 'Loading timeline…'; timelineDialog.showModal();
        try {
            const response = await fetch(timelineButton.dataset.timelineUrl,{headers:{Accept:'application/json'}});
            if(!response.ok) throw new Error('Unable to load timeline.');
            const data = await response.json();
            document.getElementById('timeline-title').textContent = data.reference + ' — Timeline';
            content.innerHTML = data.html;
        } catch(error) { content.textContent = error.message; }
    }
    if (event.target.closest('[data-general-edit]')) openEditor('General System Settings', settings.generalUrl, [
        {name:'app_name',label:'System Name',required:true,max:80},{name:'portal_subtitle',label:'Portal Subtitle',required:true,max:100},
        {name:'support_tagline',label:'Information Bar Text',required:true,max:200,wide:true},{name:'footer_credit',label:'Footer Credit',required:true,max:255,wide:true}], settings.general);
    const add = event.target.closest('[data-setting-new]');
    const edit = event.target.closest('[data-setting-edit]');
    if (add || edit) {
        const type = add?.dataset.settingNew || edit.dataset.settingEdit;
        let record = edit ? settings[type].find(r => String(r.id) === edit.dataset.recordId) : {};
        if (type === 'technicians') record = {...record, user_access:record.user?.is_staff ? '1' : '0', username:record.user?.username || ''};
        openEditor((edit ? 'Edit ' : 'Add ') + ({departments:'Department',technicians:'Technician Profile',services:'Service Category'}[type]), settings.baseUrl + '/' + type + (edit ? '/' + record.id : ''), settingSchema(type), record);
    }
    if (event.target.closest('[data-profile-edit]')) openEditor('My Staff Account', settings.profileUrl, [
        {name:'name',label:'Full Name',required:true},{name:'email',label:'Email Address',type:'email',required:true},{name:'username',label:'Username'},
        {name:'current_password',label:'Current Password',type:'password',required:true},{name:'password',label:'New Password (optional)',type:'password'},
        {name:'password_confirmation',label:'Confirm New Password',type:'password'}], settings.profile);
    if (event.target.closest('[data-new-ticket]')) openEditor('New Support Ticket', tickets.storeUrl, ticketSchema(false), {priority:'normal'});
    const view = event.target.closest('[data-ticket-url], [data-ticket-action-url]');
    if (view) {
        view.disabled = true;
        try {
            const response = await fetch(view.dataset.ticketUrl || view.dataset.ticketActionUrl, {headers:{Accept:'application/json'}});
            if (!response.ok) throw new Error('Unable to load ticket. Please reload and try again.');
            const record = await response.json();
            if (view.hasAttribute('data-ticket-action-url')) {
                openEditor(record.reference + ' — Update / Action Taken', record.action_url, [
                    {name:'status',label:'Status',options:tickets.statuses.map(v=>({value:v,label:v})),required:true},
                    {name:'technician_id',label:'Assigned Technician',options:[{value:'',label:'Unassigned'},...tickets.technicians.filter(t=>t.is_active||t.id===record.technician_id).map(t=>({value:t.id,label:t.name}))]},
                    {name:'action_taken',label:'Action Taken / Progress Update (visible in public tracking)',type:'textarea',required:true,wide:true,max:5000},
                ], record);
            } else openEditor(record.reference + ' — Edit Details', view.dataset.ticketUrl, ticketSchema(true, record), record, 'PATCH');
        } catch (error) { document.getElementById('dashboard-notice').textContent = error.message; }
        finally { view.disabled = false; }
    }
});
function ticketSchema(edit, record = {}) {
    const departments = tickets.departments.filter(d => d.is_active || d.id === record.department_id);
    const technicians = tickets.technicians.filter(t => t.is_active || t.id === record.technician_id);
    const services = Object.entries(tickets.services).filter(([slug, service]) => service.is_active || slug === record.category).map(([slug,s])=>({value:slug,label:s.title}));
    if (record.category && !services.some(s => s.value === record.category)) services.push({value:record.category,label:record.category});
    return [{name:'name',label:'Requester Name',required:true,max:120},{name:'email',label:'Email Address',type:'email',required:true,max:254},
        {name:'department_id',label:'Department / Office',options:options(departments),required:true},
        {name:'category',label:'Service Category',options:[{value:'',label:'Choose a service'},...services],required:true},
        {name:'priority',label:'Priority',options:['normal','high','urgent'].map(v=>({value:v,label:v[0].toUpperCase()+v.slice(1)})),required:true},
        {name:'subject',label:'Subject',required:true,wide:true,max:200},{name:'description',label:'Concern Details',type:'textarea',required:true,wide:true,max:10000}];
}
form.addEventListener('submit', async event => {
    event.preventDefault(); errors.replaceChildren(); form.querySelectorAll('[aria-invalid]').forEach(input=>input.removeAttribute('aria-invalid')); const button = form.querySelector('[type=submit]'); button.disabled = true;
    try {
        const body = new FormData(form); if (saveMethod === 'PATCH') body.append('_method', 'PATCH');
        const response = await fetch(saveUrl, {method:'POST',body,headers:{Accept:'application/json','X-CSRF-TOKEN':document.querySelector('meta[name=csrf-token]').content}});
        const data = await response.json();
        if (!response.ok) {
            const messages = data.errors ? Object.values(data.errors).flat() : [data.message || 'Unable to save.'];
            messages.forEach(m=>{const p=document.createElement('p');p.textContent=m;errors.append(p);});
            if(data.errors) Object.keys(data.errors).forEach(name=>form.elements.namedItem(name)?.setAttribute('aria-invalid','true'));
            errors.scrollIntoView({block:'nearest'});
            return;
        }
        sessionStorage.setItem('dashboard-success', data.message || 'Saved successfully.');
        window.location.reload();
    } catch { errors.textContent = 'Unable to save. Check your connection and try again.'; }
    finally { button.disabled = false; }
});
document.querySelectorAll('[data-close-timeline]').forEach(button=>button.addEventListener('click',()=>document.getElementById('timeline-dialog').close()));
document.addEventListener('click', async event => {
    const button = event.target.closest('[data-ticket-delete-url]');
    if (!button || button.disabled) return;
    if (!window.confirm(`Delete ticket ${button.dataset.ticketReference}?\n\nThis permanently deletes the ticket and its timeline. This cannot be undone.`)) return;
    button.disabled = true;
    try {
        const response = await fetch(button.dataset.ticketDeleteUrl, {
            method: 'DELETE',
            headers: { Accept: 'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content },
        });
        const data = await response.json();
        if (!response.ok) throw new Error(data.message || 'Unable to delete this ticket.');
        sessionStorage.setItem('dashboard-success', data.message);
        window.location.reload();
    } catch (error) {
        document.getElementById('dashboard-notice').textContent = error.message || 'Unable to delete. Check your connection and try again.';
        button.disabled = false;
    }
});

const savedMessage = sessionStorage.getItem('dashboard-success');
if (savedMessage) {
    const notice = document.getElementById('dashboard-notice'); notice.textContent = savedMessage; notice.className = 'portal-alert dashboard-success';
    sessionStorage.removeItem('dashboard-success');
}
