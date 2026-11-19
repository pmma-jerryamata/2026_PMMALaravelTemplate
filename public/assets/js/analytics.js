(() => {
    const panel = document.querySelector('[data-analytics-url]');
    if (!panel) return;
    const palette = ['#0032a0', '#ff8c12', '#10264b', '#39a8c7', '#7d80ce', '#d69243', '#43826f', '#c05f85'];
    const charts = [];
    const summary = document.getElementById('analytics-summary');
    let activeRequest;
    const numberAxis = {beginAtZero:true, ticks:{precision:0, font:{size:10}}, grid:{color:'#e9eef5'}};
    Chart.defaults.font.family = getComputedStyle(document.body).fontFamily;
    Chart.defaults.color = '#65748b';
    function tableSection(title, headings, rows, container) {
        const heading = document.createElement('h4'); heading.textContent = title;
        const table = document.createElement('table'); table.className = 'dashboard-table';
        const thead = document.createElement('thead'); const tr = document.createElement('tr');
        headings.forEach(text => { const th = document.createElement('th'); th.textContent = text; tr.append(th); }); thead.append(tr);
        const tbody = document.createElement('tbody');
        rows.forEach(row => { const tr = document.createElement('tr'); row.forEach(value => { const td = document.createElement('td'); td.textContent = value; tr.append(td); }); tbody.append(tr); });
        table.append(thead,tbody); container.append(heading,table);
    }
    function render(data) {
        charts.forEach(chart => chart.destroy()); charts.length = 0;
        const total = `${data.total} ticket${data.total === 1 ? '' : 's'}`;
        summary.textContent = `${total} · ${data.range} · ${data.period[0].toUpperCase()+data.period.slice(1)} view`;
        document.getElementById('analytics-empty').hidden = data.total > 0;
        panel.querySelectorAll('[data-period]').forEach(button => button.setAttribute('aria-pressed', String(button.dataset.period === data.period)));
        document.getElementById('filter-period').value = data.period;
        const common = {responsive:true, maintainAspectRatio:false, animation:matchMedia('(prefers-reduced-motion: reduce)').matches ? false : {duration:250}};
        charts.push(new Chart(document.getElementById('category-chart'), {
            type:'bar',data:{labels:data.labels,datasets:data.categories.map((category,i)=>({...category,backgroundColor:palette[i%palette.length],borderRadius:2,maxBarThickness:30}))},
            options:{...common,scales:{x:{stacked:true,ticks:{font:{size:9},maxRotation:50},grid:{display:false}},y:{...numberAxis,stacked:true}},plugins:{legend:{position:'bottom',labels:{boxWidth:8,font:{size:9},padding:10}},tooltip:{mode:'index',intersect:false}}},
        }));
        charts.push(new Chart(document.getElementById('department-chart'), {
            type:'bar',data:{labels:data.departments.labels,datasets:[{label:'Tickets',data:data.departments.data,backgroundColor:'#0032a0',borderRadius:4,maxBarThickness:24}]},
            options:{...common,indexAxis:'y',scales:{x:numberAxis,y:{grid:{display:false},ticks:{font:{size:10},callback:function(value){const text=this.getLabelForValue(value);return text.length>28?text.slice(0,26)+'…':text;}}}},plugins:{legend:{display:false}}},
        }));
        charts.push(new Chart(document.getElementById('volume-chart'), {
            type:'line',data:{labels:data.labels,datasets:[{label:'New Tickets',data:data.volume,borderColor:'#ff8c12',backgroundColor:'#ff8c1218',fill:true,tension:.25,pointRadius:3,pointBackgroundColor:'#ff8c12',borderWidth:2}]},
            options:{...common,scales:{x:{ticks:{font:{size:9},maxRotation:50},grid:{display:false}},y:numberAxis},plugins:{legend:{display:false}}},
        }));
        document.querySelectorAll('.chart-area canvas').forEach(canvas => canvas.setAttribute('aria-label', canvas.getAttribute('aria-label').split(' — ')[0]+' — '+total+', '+data.range+'. See View Chart Data for exact values.'));
        const container=document.getElementById('analytics-data-content');container.replaceChildren();
        tableSection('Tickets by Period and Category',['Period','Total',...data.categories.map(c=>c.label)],data.labels.map((label,i)=>[label,data.volume[i],...data.categories.map(c=>c.data[i])]),container);
        tableSection('Tickets by Department',['Department','Tickets'],data.departments.labels.map((label,i)=>[label,data.departments.data[i]]),container);
    }
    render(JSON.parse(document.getElementById('analytics-initial').textContent));
    panel.querySelectorAll('[data-period]').forEach(button => button.addEventListener('click', async () => {
        activeRequest?.abort(); activeRequest = new AbortController();
        const request = activeRequest;
        const params = new URLSearchParams(location.search); params.set('period',button.dataset.period);
        panel.setAttribute('aria-busy','true'); summary.textContent='Loading analytics…';
        try {
            const response = await fetch(panel.dataset.analyticsUrl+'?'+params, {headers:{Accept:'application/json'},signal:request.signal});
            if (!response.ok) throw new Error('Unable to load analytics. Try again.');
            render(await response.json());
            history.replaceState(null,'',location.pathname+'?'+params);
            document.querySelectorAll('[data-ticket-url]').forEach(btn=>btn.dataset.ticketUrl=btn.dataset.ticketUrl.split('?')[0]);
        } catch(error) { if(error.name!=='AbortError') summary.textContent=error.message; }
        finally { if(activeRequest===request) panel.removeAttribute('aria-busy'); }
    }));
})();
