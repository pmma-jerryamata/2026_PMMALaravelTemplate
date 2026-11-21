(() => {
    const clock = document.getElementById('ph-clock');
    if (!clock) return;

    const source = document.querySelector('[data-ph-clock-source]');
    const formatter = new Intl.DateTimeFormat('en-PH', {
        timeZone: 'Asia/Manila',
        month: 'short',
        day: 'numeric',
        year: 'numeric',
        hour: 'numeric',
        minute: '2-digit',
        second: '2-digit',
        hour12: true,
    });

    const initialTime = Date.parse(clock.getAttribute('datetime') ?? '');
    let timeAnchor = Number.isFinite(initialTime) ? initialTime : Date.now();
    let monotonicAnchor = performance.now();
    let syncing = false;

    function renderClock() {
        const date = new Date(timeAnchor + performance.now() - monotonicAnchor);
        clock.textContent = formatter.format(date);
        if (clock.tagName === 'TIME') clock.dateTime = date.toISOString();
    }

    function showSource(label) {
        clock.title = label;
        if (source) source.textContent = label;
    }

    async function syncClock() {
        if (syncing || document.hidden) return;

        syncing = true;
        try {
            const started = performance.now();
            const response = await fetch(clock.dataset.syncUrl, {
                cache: 'no-store',
                signal: AbortSignal.timeout(7000),
            });
            if (!response.ok) throw new Error('Time source unavailable');

            const data = await response.json();
            if (!Number.isFinite(data.timestamp)) throw new Error('Invalid time');

            monotonicAnchor = performance.now();
            timeAnchor = data.timestamp + (monotonicAnchor - started) / 2;
            showSource(data.synced ? 'Source: PAGASA' : 'Server clock · PAGASA unavailable');
            renderClock();
        } catch {
            showSource('Time sync unavailable');
        } finally {
            syncing = false;
        }
    }

    renderClock();
    syncClock();
    setInterval(renderClock, 1000);
    setInterval(syncClock, 60000);
    document.addEventListener('visibilitychange', () => {
        if (document.hidden) return;
        renderClock();
        syncClock();
    });
})();
