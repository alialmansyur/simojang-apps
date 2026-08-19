document.addEventListener('DOMContentLoaded', function() {
    // Check global loading functions
    const showLoader = typeof showLoading === 'function' ? showLoading : () => console.log('Loading...');
    const hideLoader = typeof hideLoading === 'function' ? hideLoading : () => console.log('Loaded');

    let calendar;
    let allEvents = [];
    
    // Initialize FullCalendar
    const calendarEl = document.getElementById('calendar');
    calendar = new FullCalendar.Calendar(calendarEl, {
        initialView: 'dayGridMonth',
        headerToolbar: {
            left: 'prev,next today',
            center: 'title',
            right: 'dayGridMonth,timeGridWeek,timeGridDay'
        },
        themeSystem: 'standard',
        locale: 'id',
        buttonText: {
            today: 'Hari Ini',
            month: 'Bulan',
            week: 'Minggu',
            day: 'Hari',
            list: 'Agenda'
        },
        height: 'auto',
        contentHeight: 'auto',
        eventClick: function(info) {
            showEventDetail(info.event);
        },
        eventTimeFormat: {
            hour: '2-digit',
            minute: '2-digit',
            meridiem: false,
            hour12: false
        }
    });
    calendar.render();

    // Initial Fetch
    fetchData();

    // Setup Filters
    const searchInput = document.getElementById('searchEvent');
    const categorySelect = document.getElementById('filterCategory');
    const statusSelect = document.getElementById('filterStatus');

    let searchTimer;
    searchInput.addEventListener('keyup', () => {
        clearTimeout(searchTimer);
        searchTimer = setTimeout(() => filterAndRenderEvents(), 300);
    });
    categorySelect.addEventListener('change', filterAndRenderEvents);
    statusSelect.addEventListener('change', filterAndRenderEvents);

    function fetchData() {
        if (typeof showLoading === 'function') showLoading('Memproses data...');

        // Fetch both KPI and Events
        Promise.all([
            fetch('https://kanreg3.id/simanja-api/public/dashboard/kpi').then(res => res.json()),
            fetch('https://kanreg3.id/simanja-api/public/dashboard/events').then(res => res.json())
        ])
        .then(([kpiData, eventsData]) => {
            if (typeof hideLoading === 'function') hideLoading();
            
            // Render KPI
            renderKPI(kpiData);

            // Save events and render
            if (eventsData && eventsData.data) {
                // Map API data to FullCalendar event format
                allEvents = eventsData.data.map(ev => {
                    // Determine color based on status or category
                    let bgColor = '#0ea5e9'; // default blue
                    if (ev.status === 'Selesai') bgColor = '#16a34a'; // green
                    else if (ev.status === 'Berlangsung') bgColor = '#f59e0b'; // amber
                    
                    return {
                        id: ev.id || Math.random().toString(),
                        title: ev.title || 'Tanpa Judul',
                        start: ev.start,
                        end: ev.end,
                        backgroundColor: bgColor,
                        borderColor: bgColor,
                        extendedProps: {
                            description: ev.description || '-',
                            category: ev.category || 'Lainnya',
                            status: ev.status || 'Belum Mulai',
                            location: ev.location || '-',
                            letter: ev.stNumber || '-',
                            team: ev.team || '-',
                            staff: ev.participants || []
                        }
                    };
                });
            } else {
                allEvents = [];
            }
            
            filterAndRenderEvents();
        })
        .catch(err => {
            console.error('Error fetching data:', err);
            if (typeof hideLoading === 'function') hideLoading();
            if (typeof Swal !== 'undefined') {
                Swal.fire('Error', 'Gagal memuat data dari server.', 'error');
            }
        });
    }

    function renderKPI(data) {
        if (data && data.data) {
            document.getElementById('kpiTotal').textContent = data.data.total_kegiatan || 0;
            document.getElementById('kpiCompleted').textContent = data.data.kegiatan_selesai || 0;
            document.getElementById('kpiUpcoming').textContent = data.data.akan_datang || 0;
            document.getElementById('kpiToday').textContent = data.data.hari_ini || 0;
        }
    }

    function filterAndRenderEvents() {
        const searchTerm = searchInput.value.toLowerCase();
        const categoryVal = categorySelect.value;
        const statusVal = statusSelect.value;

        const filteredEvents = allEvents.filter(ev => {
            // Check category
            if (categoryVal && ev.extendedProps.category !== categoryVal) return false;
            // Check status
            if (statusVal && ev.extendedProps.status !== statusVal) return false;
            // Check search term
            if (searchTerm) {
                const titleMatch = ev.title.toLowerCase().includes(searchTerm);
                const descMatch = (ev.extendedProps.description || '').toLowerCase().includes(searchTerm);
                const locMatch = (ev.extendedProps.location || '').toLowerCase().includes(searchTerm);
                if (!titleMatch && !descMatch && !locMatch) return false;
            }
            return true;
        });

        // Update Calendar
        calendar.removeAllEvents();
        calendar.addEventSource(filteredEvents);

        // Update Today's Agenda
        renderTodayAgenda(filteredEvents);
    }

    function renderTodayAgenda(events) {
        const container = document.getElementById('todayAgendaContainer');
        const emptyState = document.getElementById('agendaEmptyState');
        const subtitle = document.getElementById('agendaSubtitle');
        
        container.innerHTML = '';
        
        // Find events active today
        const todayStr = new Date().toISOString().split('T')[0];
        
        const todayEvents = events.filter(ev => {
            if (!ev.start) return false;
            // An event is active today if start is today or before, and end is today or after
            const startStr = ev.start.split(' ')[0].split('T')[0];
            const endStr = ev.end ? ev.end.split(' ')[0].split('T')[0] : startStr;
            return todayStr >= startStr && todayStr <= endStr;
        });

        subtitle.textContent = `Anda memiliki ${todayEvents.length} agenda dijadwalkan`;

        if (todayEvents.length === 0) {
            emptyState.classList.remove('d-none');
            return;
        }

        emptyState.classList.add('d-none');
        
        // Sort by start time if available
        todayEvents.sort((a, b) => a.start.localeCompare(b.start));

        // Separating highlight vs list
        const highlightEvent = todayEvents[0];
        const listEvents = todayEvents; // Do not slice, show all events in the list below

        // --- Render Highlight ---
        let hlStart = '00:00';
        let hlEnd = '23:59';
        if (highlightEvent.start.includes('T') || highlightEvent.start.includes(' ')) {
            hlStart = new Date(highlightEvent.start).toLocaleTimeString('id-ID', { hour: '2-digit', minute: '2-digit' }).replace('.', ':');
        }
        if (highlightEvent.end && (highlightEvent.end.includes('T') || highlightEvent.end.includes(' '))) {
            hlEnd = new Date(highlightEvent.end).toLocaleTimeString('id-ID', { hour: '2-digit', minute: '2-digit' }).replace('.', ':');
        }
        const hlStatus = highlightEvent.extendedProps.status.toUpperCase();
        const pulseClass = (hlStatus === 'BERLANGSUNG' || hlStatus.includes('SEDANG')) ? 'pulse-dot' : 'pulse-dot upcoming';

        const hlCard = document.createElement('div');
        hlCard.className = 'agenda-hl-card twx-anim-card';
        hlCard.innerHTML = `
            <i class="bi bi-clock-history agenda-hl-bg-icon"></i>
            <div class="agenda-hl-status">
                <div class="${pulseClass}"></div>
                ${hlStatus === 'BELUM MULAI' ? 'AKAN DATANG' : hlStatus}
            </div>
            <div class="agenda-hl-time">${hlStart} - ${hlEnd}</div>
            <div class="agenda-hl-title">${highlightEvent.title}</div>
            <div class="agenda-hl-location"><i class="bi bi-geo-alt"></i> ${highlightEvent.extendedProps.location || '-'}</div>
        `;
        hlCard.addEventListener('click', () => {
            showEventDetail({
                title: highlightEvent.title,
                start: new Date(highlightEvent.start),
                end: highlightEvent.end ? new Date(highlightEvent.end) : null,
                extendedProps: highlightEvent.extendedProps
            });
        });
        container.appendChild(hlCard);

        // --- Render Timeline ---
        if (listEvents.length > 0) {
            const tlContainer = document.createElement('div');
            tlContainer.className = 'agenda-timeline';
            
            listEvents.forEach(ev => {
                let evStart = '00:00';
                let evEnd = '23:59';
                if (ev.start.includes('T') || ev.start.includes(' ')) {
                    evStart = new Date(ev.start).toLocaleTimeString('id-ID', { hour: '2-digit', minute: '2-digit' }).replace('.', ':');
                }
                if (ev.end && (ev.end.includes('T') || ev.end.includes(' '))) {
                    evEnd = new Date(ev.end).toLocaleTimeString('id-ID', { hour: '2-digit', minute: '2-digit' }).replace('.', ':');
                }

                const tlItem = document.createElement('div');
                tlItem.className = 'agenda-tl-item';
                tlItem.innerHTML = `
                    <div class="agenda-tl-time">
                        <div class="agenda-tl-time-start">${evStart}</div>
                        <div class="agenda-tl-time-end">${evEnd}</div>
                    </div>
                    <div class="agenda-tl-node">
                        <div class="agenda-tl-circle">
                            <div class="agenda-tl-dot"></div>
                        </div>
                    </div>
                    <div class="agenda-tl-content">
                        <div class="agenda-tl-title">${ev.title}</div>
                        <div class="agenda-tl-location"><i class="bi bi-geo-alt me-1"></i> ${ev.extendedProps.location || '-'}</div>
                    </div>
                `;
                tlItem.addEventListener('click', () => {
                    showEventDetail({
                        title: ev.title,
                        start: new Date(ev.start),
                        end: ev.end ? new Date(ev.end) : null,
                        extendedProps: ev.extendedProps
                    });
                });
                tlContainer.appendChild(tlItem);
            });
            container.appendChild(tlContainer);
        }
    }

    function showEventDetail(eventObj) {
        const props = eventObj.extendedProps;
        
        // Set badges
        const catBadge = document.getElementById('detailCategoryBadge');
        catBadge.textContent = props.category;
        
        const statBadge = document.getElementById('detailStatusBadge');
        statBadge.textContent = props.status;
        if (props.status === 'Selesai') {
            statBadge.style.backgroundColor = '#dcfce7';
            statBadge.style.color = '#16a34a';
        } else if (props.status === 'Berlangsung') {
            statBadge.style.backgroundColor = '#fef3c7';
            statBadge.style.color = '#d97706';
        } else {
            statBadge.style.backgroundColor = '#f1f5f9';
            statBadge.style.color = '#475569';
        }

        // Set Texts
        document.getElementById('detailEventTitle').textContent = eventObj.title;
        document.getElementById('detailDescription').textContent = props.description;
        document.getElementById('detailLocation').textContent = props.location;
        document.getElementById('detailLetter').textContent = props.letter;
        document.getElementById('detailTeam').textContent = props.team;

        // Set Dates
        const dateOpts = { weekday: 'long', year: 'numeric', month: 'short', day: 'numeric' };
        const timeOpts = { hour: '2-digit', minute: '2-digit' };
        let timeString = '-';
        if (eventObj.start) {
            const dateStr = eventObj.start.toLocaleString('id-ID', dateOpts);
            const startTimeStr = eventObj.start.toLocaleTimeString('id-ID', timeOpts).replace('.', ':');
            let endTimeStr = eventObj.end ? eventObj.end.toLocaleTimeString('id-ID', timeOpts).replace('.', ':') : 'Selesai';
            timeString = `${dateStr}, ${startTimeStr} - ${endTimeStr} WIB`;
        }
        const timeEl = document.getElementById('detailTimeText');
        if (timeEl) timeEl.textContent = timeString;

        // Set Staff
        const staffContainer = document.getElementById('detailStaffContainer');
        if (staffContainer) {
            staffContainer.innerHTML = '';
            if (props.staff && Array.isArray(props.staff) && props.staff.length > 0) {
                if (props.staff.length > 3) {
                     staffContainer.innerHTML = `<span class="badge rounded-pill px-3 py-2" style="background-color: #eff6ff; color: #1040c1; font-weight: 600; border: 1px solid #bfdbfe;">Semua Pegawai</span>`;
                } else {
                     staffContainer.innerHTML = props.staff.map(s => s.nama || s).join(', ');
                }
            } else {
                staffContainer.innerHTML = `<span class="badge rounded-pill px-3 py-2" style="background-color: #f1f5f9; color: #475569; font-weight: 600; border: 1px solid #e2e8f0;">Tidak ada</span>`;
            }
        }

        // Show Modal
        if (typeof bootstrap !== 'undefined') {
            const modal = new bootstrap.Modal(document.getElementById('modalEventDetail'));
            modal.show();
        } else {
            $('#modalEventDetail').modal('show'); // Fallback for older bootstrap/jquery
        }
    }
});
