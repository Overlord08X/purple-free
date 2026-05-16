window.initAntrianRealtime = function initAntrianRealtime(options) {
    const config = options || {};
    const state = {
        lastCallKey: config.initialCallKey || '',
        lastAnnouncementKey: config.initialAnnouncementKey || '',
        soundUnlocked: false,
    };

    const csrfToken = config.csrfToken || document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';
    const eventSource = new EventSource(config.eventSourceUrl);
    const selectors = config.selectors || {};
    const audioElement = config.audioUrl ? new Audio(config.audioUrl) : null;
    if (audioElement) {
        audioElement.preload = 'auto';
        audioElement.playsInline = true;
    }

    const el = (name) => selectors[name] ? document.querySelector(selectors[name]) : null;

    function formatNumber(value) {
        return String(value ?? '-').padStart(3, '0');
    }

    function statusClass(status) {
        switch (status) {
            case 'dipanggil':
                return 'bg-primary';
            case 'selesai':
                return 'bg-success';
            case 'terlambat':
                return 'bg-danger';
            default:
                return 'bg-warning text-dark';
        }
    }

    function renderBadge(status) {
        const badge = document.createElement('span');
        badge.className = `badge rounded-pill ${statusClass(status)}`;
        badge.textContent = status ? status.charAt(0).toUpperCase() + status.slice(1) : 'Menunggu';
        return badge.outerHTML;
    }

    function renderRows(items, emptyText) {
        if (!items || !items.length) {
            return `<tr><td colspan="5" class="text-center text-muted py-4">${emptyText}</td></tr>`;
        }

        return items.map((item, index) => `
            <tr>
                <td>${index + 1}</td>
                <td><strong>${formatNumber(item.nomor_antrian)}</strong></td>
                <td>${item.nama}</td>
                <td>${item.created_at || '-'}</td>
                <td>${renderBadge(item.status)}</td>
            </tr>
        `).join('');
    }

    function updateAdmin(snapshot) {
        const stats = snapshot.stats || {};
        const current = snapshot.current;
        const next = snapshot.next;

        const setText = (name, value) => {
            const node = el(name);
            if (node) {
                node.textContent = value;
            }
        };

        setText('total', stats.total ?? 0);
        setText('menunggu', stats.menunggu ?? 0);
        setText('dipanggil', stats.dipanggil ?? 0);
        setText('selesai', stats.selesai ?? 0);
        setText('terlambat', stats.terlambat ?? 0);
        setText('currentNumber', current ? formatNumber(current.nomor_antrian) : '--');
        setText('currentName', current ? current.nama : 'Belum ada panggilan');
        setText('nextNumber', next ? formatNumber(next.nomor_antrian) : '--');
        setText('nextName', next ? next.nama : 'Belum ada antrian');
        setText('currentNumberMetric', current ? formatNumber(current.nomor_antrian) : '--');

        const currentStatus = el('currentStatus');
        if (currentStatus) {
            currentStatus.className = `badge rounded-pill ${statusClass(current ? current.status : 'menunggu')}`;
            currentStatus.textContent = current ? current.status_label : 'Menunggu';
        }

        const actionButtons = document.querySelectorAll('[data-antrian-action]');
        actionButtons.forEach((button) => {
            button.dataset.currentId = current ? current.id : '';
            button.disabled = button.dataset.action !== 'next' && !current;
        });

        const waitingTable = el('waitingTable');
        const calledTable = el('calledTable');
        const doneTable = el('doneTable');
        const lateTable = el('lateTable');

        if (waitingTable) waitingTable.innerHTML = renderRows(snapshot.queues?.menunggu || [], 'Belum ada antrian menunggu');
        if (calledTable) calledTable.innerHTML = renderRows(snapshot.queues?.dipanggil || [], 'Belum ada antrian dipanggil');
        if (doneTable) doneTable.innerHTML = renderRows(snapshot.queues?.selesai || [], 'Belum ada antrian selesai');
        if (lateTable) lateTable.innerHTML = renderRows(snapshot.queues?.terlambat || [], 'Belum ada antrian terlambat');
    }

    function announce(snapshot) {
        if (!snapshot.current) {
            return;
        }

        if (!state.soundUnlocked) {
            console.log('[Antrian] Sound not unlocked yet');
            const statusNode = el('papanStatus');
            if (statusNode) {
                statusNode.textContent = 'Klik Aktifkan Suara untuk mendengar panggilan';
            }
            return;
        }

        const announcementKey = snapshot.announcement
            ? `${snapshot.announcement.type}-${snapshot.announcement.queue_id}-${snapshot.announcement.sent_at}`
            : `${snapshot.current.id}-${snapshot.current.status}`;

        const shouldSpeak = snapshot.announcement
            ? announcementKey !== state.lastAnnouncementKey
            : `${snapshot.current.id}-${snapshot.current.status}` !== state.lastCallKey;

        if (!shouldSpeak) {
            return;
        }

        state.lastCallKey = `${snapshot.current.id}-${snapshot.current.status}`;
        state.lastAnnouncementKey = announcementKey;

        console.log('[Antrian] Announcing queue #' + snapshot.current.nomor_antrian);

        // Play voice announcement directly (no beep)
        playVoiceAnnouncement(snapshot.current);
    }

    function playVoiceAnnouncement(current) {
        const params = new URLSearchParams({
            nomor: current.nomor_antrian,
            nama: current.nama,
        });

        const audioUrl = `${config.audioGenerationUrl}?${params}`;

        console.log('[Antrian] Requesting voice announcement:', audioUrl);

        // Create temporary audio element for voice announcement
        const voiceAudio = new Audio();
        voiceAudio.src = audioUrl;
        voiceAudio.volume = 1;

        voiceAudio.onerror = (e) => {
            console.error('[Antrian] Voice audio error:', e);
        };

        voiceAudio.onplay = () => {
            console.log('[Antrian] Voice announcement playing');
        };

        voiceAudio.onended = () => {
            console.log('[Antrian] Voice announcement finished');
        };

        voiceAudio.play().catch((e) => {
            console.error('[Antrian] Voice announcement play failed:', e.message);
        });
    }

    function updatePapan(snapshot) {
        const current = snapshot.current;
        const next = snapshot.next;

        const setText = (name, value) => {
            const node = el(name);
            if (node) {
                node.textContent = value;
            }
        };

        setText('papanNumber', current ? formatNumber(current.nomor_antrian) : '--');
        setText('papanName', current ? current.nama : 'Menunggu panggilan berikutnya');
        setText('papanNext', next ? formatNumber(next.nomor_antrian) : '--');
        setText('papanStatus', current ? current.status_label : 'Menunggu');
        setText('papanClock', new Date().toLocaleTimeString('id-ID'));
        setText('papanStatusCount', snapshot.stats?.dipanggil ?? 0);
        setText('papanWaitingCount', snapshot.stats?.menunggu ?? 0);
        setText('papanDoneCount', snapshot.stats?.selesai ?? 0);

        const statusNode = el('papanStatusBadge');
        if (statusNode) {
            statusNode.className = `badge fs-5 px-4 py-2 rounded-pill ${statusClass(current ? current.status : 'menunggu')}`;
            statusNode.textContent = current ? current.status_label : 'Menunggu';
        }

        announce(snapshot);
    }

    function postAction(url, body) {
        return fetch(url, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': csrfToken,
                'Accept': 'application/json',
            },
            body: JSON.stringify(body || {}),
        }).then(async (response) => {
            const payload = await response.json().catch(() => ({}));
            if (!response.ok) {
                throw new Error(payload.message || 'Permintaan gagal.');
            }
            return payload;
        });
    }

    document.querySelectorAll('[data-antrian-action]').forEach((button) => {
        button.addEventListener('click', () => {
            const url = button.dataset.url;
            const currentId = button.dataset.currentId;
            const action = button.dataset.action;
            const body = action === 'next' ? {} : (currentId ? { id: currentId } : {});

            button.disabled = true;

            postAction(url, body)
                .catch((error) => {
                    alert(error.message);
                })
                .finally(() => {
                    button.disabled = false;
                });
        });
    });

    eventSource.addEventListener('queue-update', (event) => {
        const snapshot = JSON.parse(event.data || '{}');

        if (config.mode === 'admin') {
            updateAdmin(snapshot);
        }

        if (config.mode === 'papan') {
            updatePapan(snapshot);
        }
    });

    eventSource.onerror = (e) => {
        console.warn('[Antrian] SSE Error, readyState:', eventSource.readyState);
        
        if (eventSource.readyState === EventSource.CLOSED) {
            console.log('[Antrian] SSE connection closed. Browser will attempt automatic reconnection.');
        }
        
        // Browser akan otomatis reconnect dengan exponential backoff
    };

    async function unlockSound() {
        state.soundUnlocked = true;

        if (audioElement) {
            try {
                audioElement.muted = true;
                await audioElement.play();
                audioElement.pause();
                audioElement.currentTime = 0;
                audioElement.muted = false;
            } catch (error) {
                audioElement.muted = false;
            }
        }

        if ('speechSynthesis' in window) {
            try {
                window.speechSynthesis.cancel();
            } catch (error) {
                // ignore
            }
        }

        return true;
    }

    if (config.mode === 'papan') {
        setInterval(() => {
            const clock = el('papanClock');
            if (clock) {
                clock.textContent = new Date().toLocaleTimeString('id-ID');
            }
        }, 1000);
    }

    return {
        eventSource,
        unlockSound,
        refresh(snapshot) {
            if (config.mode === 'admin') {
                updateAdmin(snapshot);
            }
            if (config.mode === 'papan') {
                updatePapan(snapshot);
            }
        },
        destroy() {
            eventSource.close();
        },
    };
};