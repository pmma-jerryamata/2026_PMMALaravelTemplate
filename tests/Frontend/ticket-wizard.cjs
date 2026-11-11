const assert = require('node:assert/strict');
const fs = require('node:fs');
const path = require('node:path');
let JSDOM;
try { ({JSDOM} = require('jsdom')); }
catch { ({JSDOM} = require(path.join(process.env.LOCALAPPDATA, 'PMMA-Support-Local/dom-tests/node_modules/jsdom'))); }
const source = fs.readFileSync(path.join(__dirname, '../../public/helpdesk/js/ticket-wizard.js'), 'utf8');

async function run() {
    const dom = new JSDOM(`<div data-issue-editor><textarea id="support_specifics"></textarea></div><button data-issue-next>Next</button>
        <button data-issue="Cannot connect" aria-pressed="false"></button><button data-manual-issue aria-pressed="false"></button>
        <form data-public-email-form data-lookup-url="/tickets/requester"><input name="_token" value="token">
        <input name="email" type="email" required><p id="email-status"></p>
        <fieldset data-registration-fields><legend data-registration-title></legend><p data-registration-description></p><input name="name"><div data-employee-department><select name="registration_department_id"><option value="1">ITSU</option></select></div></fieldset>
        <button type="submit">Submit</button></form>`, {runScripts: 'outside-only', url: 'https://support.example.test'});
    const {window} = dom;
    let response = {valid: true, registration_required: true, registration_type: 'employee'};
    window.fetch = async () => ({ok: true, json: async () => response});
    window.eval(source);
    const document = window.document;
    assert.equal(document.querySelector('[data-issue-editor]').hidden, true);
    assert.equal(document.querySelector('[data-issue-next]').disabled, true);
    document.querySelector('[data-issue]').click();
    assert.equal(document.querySelector('[data-issue-editor]').hidden, false);
    assert.equal(document.querySelector('[data-issue-next]').disabled, false);
    assert.equal(document.getElementById('support_specifics').value, 'Cannot connect');
    document.getElementById('support_specifics').value += ' in my office';
    document.querySelector('[data-manual-issue]').click();
    assert.equal(document.getElementById('support_specifics').value, 'Cannot connect in my office');
    const form = document.querySelector('form');
    const fields = document.querySelector('fieldset');
    assert.equal(fields.hidden, true);
    let resubmitted = 0;
    form.requestSubmit = () => { resubmitted++; };
    form.elements.email.value = 'new@pmma.edu.ph';
    form.dispatchEvent(new window.Event('submit', {cancelable: true}));
    await new Promise(resolve => setTimeout(resolve, 0));
    assert.equal(fields.hidden, false);
    assert.equal(form.elements.name.required, true);
    form.elements.name.value = 'Juan Dela Cruz';
    form.elements.name.dispatchEvent(new window.Event('input'));
    assert.equal(form.elements.name.value, 'JUAN DELA CRUZ');
    assert.equal(form.elements.registration_department_id.required, true);
    assert.equal(document.querySelector('[data-registration-title]').textContent, 'Employee registration');
    assert.equal(resubmitted, 1);
    response = {valid: true, registration_required: true, registration_type: 'cadet'};
    form.elements.email.value = 'cadet@cdt.pmma.edu.ph';
    form.elements.email.dispatchEvent(new window.Event('input'));
    form.dispatchEvent(new window.Event('submit', {cancelable: true}));
    await new Promise(resolve => setTimeout(resolve, 0));
    assert.equal(document.querySelector('[data-registration-title]').textContent, 'Cadet registration');
    assert.equal(document.querySelector('[data-employee-department]').hidden, true);
    assert.equal(form.elements.registration_department_id.required, false);
    assert.equal(form.elements.registration_department_id.disabled, true);
    assert.equal(form.elements.name.required, true);
    assert.equal(resubmitted, 2);
    response = {valid: true};
    form.elements.email.value = 'existing@pmma.edu.ph';
    form.elements.email.dispatchEvent(new window.Event('input'));
    form.dispatchEvent(new window.Event('submit', {cancelable: true}));
    await new Promise(resolve => setTimeout(resolve, 0));
    assert.equal(fields.hidden, true);
    assert.equal(form.elements.name.disabled, true);
    assert.equal(form.elements.name.required, false);
    assert.equal(resubmitted, 3);
    window.close();
    console.log('Ticket wizard interactions passed.');
}
run().catch(error => { console.error(error); process.exitCode = 1; });
