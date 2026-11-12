(() => {
    const source = document.getElementById('content_html');
    const editor = document.getElementById('report-content-editor');
    if (!source || !editor) return;
    const toolbar = document.querySelector('.report-editor-toolbar');
    const toggle = document.getElementById('report-html-toggle');
    let sourceMode = false;
    const defaultField = document.getElementById('content_default');
    const markEdited = () => { if (defaultField) defaultField.value = '0'; };
    source.addEventListener('input', markEdited);
    editor.addEventListener('input', markEdited);
    // Never insert raw source/pasted HTML into the live document.
    function clean(html) {
        const parsed = new DOMParser().parseFromString(html, 'text/html');
        const fragment = document.createDocumentFragment();
        function append(node, target) {
            if (node.nodeType === 3) { target.append(document.createTextNode(node.textContent)); return; }
            if (node.nodeType !== 1) return;
            const tag = node.tagName.toLowerCase();
            if (['script','style','iframe','object','svg','math','img','input','textarea'].includes(tag)) return;
            const block = ['p','div','h1','h2','h3','li'].includes(tag);
            const allowed = block || ['br','b','strong','i','em','u'].includes(tag);
            const output = allowed ? document.createElement(block ? 'p' : tag) : target;
            if (block) {
                const align = node.style.textAlign || node.getAttribute('align');
                if (['left','center','right','justify'].includes(align)) output.style.textAlign = align;
            }
            for (const child of node.childNodes) append(child, output);
            if (allowed) target.append(output);
        }
        for (const child of parsed.body.childNodes) append(child, fragment);
        return fragment;
    }
    editor.replaceChildren(clean(source.value));
    editor.hidden = false; toolbar.hidden = false; source.hidden = true;
    document.querySelector('.report-source-label').hidden = true;
    editor.addEventListener('input', () => { source.value = editor.innerHTML; });
    editor.addEventListener('paste', event => {
        event.preventDefault();
        markEdited();
        document.execCommand('insertText', false, event.clipboardData.getData('text/plain'));
        source.value = editor.innerHTML;
    });
    toolbar.querySelectorAll('[data-editor-command]').forEach(button => {
        button.addEventListener('mousedown', event => event.preventDefault());
        button.addEventListener('click', () => {
            markEdited();
            editor.focus(); document.execCommand(button.dataset.editorCommand, false, null);
            source.value = editor.innerHTML;
        });
    });
    toggle.addEventListener('click', () => {
        if (sourceMode) { editor.replaceChildren(clean(source.value)); source.value = editor.innerHTML; }
        else source.value = editor.innerHTML;
        sourceMode = !sourceMode;
        source.hidden = !sourceMode; editor.hidden = sourceMode;
        toggle.setAttribute('aria-pressed', String(sourceMode));
        toolbar.querySelectorAll('[data-editor-command]').forEach(button => { button.disabled = sourceMode; });
        (sourceMode ? source : editor).focus();
    });
    function refreshIntroduction(force = false) {
        if (!defaultField || (!force && defaultField.value !== '1')) return;
        const from = source.form.elements.from?.value, to = source.form.elements.to?.value;
        if (!from || !to) return;
        const format = value => new Intl.DateTimeFormat('en-US', {year:'numeric', month:'long', day:'numeric'}).format(new Date(value + 'T12:00:00'));
        const period = format(from) + ' - ' + format(to);
        const content = document.createElement('div');
        const title = document.createElement('p'); title.setAttribute('align','center');
        const bold = document.createElement('b'); bold.textContent = 'ACCOMPLISHMENT REPORT'; title.append(bold); content.append(title);
        const dates = document.createElement('p'); dates.setAttribute('align','center'); dates.textContent = '(' + period + ')'; content.append(dates);
        const intro = document.createElement('p'); intro.textContent = 'Submitted herewith is the accomplishment report of the undersigned for the period as of ' + period + '.'; content.append(intro);
        const scope = [];
        for (const [name, label] of [['technician_id','Technician'],['department_id','Office'],['task_id','Service type']]) {
            const field = source.form.elements[name];
            if (field?.value) scope.push(label + ': ' + field.selectedOptions[0].textContent.trim());
        }
        if (scope.length) { const line = document.createElement('p'); line.textContent = scope.join(' | ') + '.'; content.append(line); }
        source.value = content.innerHTML; editor.replaceChildren(clean(source.value)); defaultField.value = '1';
    }
    for (const name of ['from','to','technician_id','department_id','task_id']) source.form.elements[name]?.addEventListener('change', () => refreshIntroduction());
    source.form.addEventListener('submit', () => {
        if (!(sourceMode ? source.value.trim() : editor.textContent.trim())) refreshIntroduction(true);
        else refreshIntroduction();
        if (!sourceMode) source.value = editor.innerHTML;
    });
})();
