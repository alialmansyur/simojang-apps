// =========================================================================
// Fasilitasi CAT - Detail Tilok & Rekap Per Instansi (tablesRekap.js)
// =========================================================================

window.CatDetailState = {
    paramKey: (typeof TILOK_UID !== 'undefined' && TILOK_UID) ? TILOK_UID : window.location.pathname.replace(/\/$/, '').split('/').pop(),
    tilokMeta: {},
    currentLevel: 'instansi', // 'instansi' | 'rekap'
    activeInstansi: {
        id: '',
        nama: '',
        stats: {}
    },
    allInstansi: [],
    filter: {
        keyword: '',
        status: 'all', // 'all', 'updated', 'pending'
        sort: 'updated_desc',
        currentPage: 1,
        itemsPerPage: 10
    },
    selectedBulan: [],
    rekapModalMode: 'new_instansi' // 'new_instansi' | 'active_instansi'
};

const bulanList = [
    { val: '01', text: 'Januari' },
    { val: '02', text: 'Februari' },
    { val: '03', text: 'Maret' },
    { val: '04', text: 'April' },
    { val: '05', text: 'Mei' },
    { val: '06', text: 'Juni' },
    { val: '07', text: 'Juli' },
    { val: '08', text: 'Agustus' },
    { val: '09', text: 'September' },
    { val: '10', text: 'Oktober' },
    { val: '11', text: 'November' },
    { val: '12', text: 'Desember' }
];

function escapeHtml(value) {
    return String(value || '')
        .replace(/&/g, '&amp;')
        .replace(/</g, '&lt;')
        .replace(/>/g, '&gt;')
        .replace(/"/g, '&quot;')
        .replace(/'/g, '&#039;');
}

function formatDateOnly(value) {
    if (!value) return '-';
    const d = new Date(`${value}T00:00:00`);
    if (Number.isNaN(d.getTime())) return value;
    return d.toLocaleDateString('id-ID', {
        year: 'numeric',
        month: 'short',
        day: '2-digit',
    });
}

function formatDateTime(value) {
    if (!value) return '-';
    const d = new Date(value.includes('T') ? value : value.replace(' ', 'T'));
    if (Number.isNaN(d.getTime())) return value;
    return d.toLocaleString('id-ID', {
        year: 'numeric',
        month: 'short',
        day: '2-digit',
        hour: '2-digit',
        minute: '2-digit',
    });
}

function formatNumber(num) {
    return Number(num || 0).toLocaleString('id-ID');
}

function parseDateToTimestamp(dateStr) {
    if (!dateStr) return 0;
    const cleanStr = String(dateStr).trim();
    if (!cleanStr || cleanStr === '-' || cleanStr === '0000-00-00' || cleanStr === '0000-00-00 00:00:00') return 0;
    
    // Replace space with T for ISO format compatibility across all JS engines
    const iso = cleanStr.includes('T') ? cleanStr : cleanStr.replace(' ', 'T');
    const parsed = Date.parse(iso);
    if (!Number.isNaN(parsed)) return parsed;
    
    const fallback = Date.parse(cleanStr.replace(/-/g, '/'));
    return !Number.isNaN(fallback) ? fallback : 0;
}

// -------------------------------------------------------------------------
// Inisialisasi Dropdown Bulan
// -------------------------------------------------------------------------
const bulanContainer = document.getElementById('bulanList');
if (bulanContainer) {
    bulanList.forEach(bulan => {
        bulanContainer.insertAdjacentHTML('beforeend', `
            <li>
                <div class="form-check py-1">
                    <input class="form-check-input bulan-check"
                           type="checkbox"
                           value="${bulan.val}"
                           id="bulan${bulan.val}">
                    <label class="form-check-label fw-semibold"
                           for="bulan${bulan.val}">
                        ${bulan.text}
                    </label>
                </div>
            </li>
        `);
    });
}

// -------------------------------------------------------------------------
// Load Meta & Summary
// -------------------------------------------------------------------------
function loadMetaDetailTilok() {
    return $.ajax({
        url: AppConfig.initGlobal + 'fetch/meta-tilok-detail',
        type: 'POST',
        dataType: 'json',
        data: { key: CatDetailState.paramKey },
        success: function (response) {
            const meta = response?.meta || {};
            CatDetailState.tilokMeta = meta;
            $('#catDetailTilok').text(meta.nama_tilok || '-');
            $('#catDetailEvent').text(meta.nama_seleksi || '-');

            // Sync Modal Info Header (Entri Rekap - Instansi Baru)
            $('#modalInfoEvent').text(meta.nama_seleksi || '-').attr('title', meta.nama_seleksi || '-');
            $('#modalInfoTilok').text(meta.nama_tilok || '-').attr('title', meta.nama_tilok || '-');

            if (meta.seleksi_uid) {
                $('#btnHeaderBack').attr('href', AppConfig.initGlobal + 'apps-cat-tilok/' + meta.seleksi_uid);
            }

            if (meta.period_start_date || meta.period) {
                const periode = meta.period || `${meta.period_start_date} s/d ${meta.period_end_date}`;
                $('#catDetailPeriodeText').text(periode);
                $('#catDetailPeriodeWrap').removeClass('d-none');
            } else {
                $('#catDetailPeriodeWrap').addClass('d-none');
            }

            if (meta.kapasitas) {
                $('#catDetailKapasitasText').text(`${meta.kapasitas} PC`);
                $('#catDetailKapasitasWrap').removeClass('d-none');
            } else {
                $('#catDetailKapasitasWrap').addClass('d-none');
            }

            $('#detailKeyFormCreate').val(meta.id || '');
        },
        error: function () {
            $('#catDetailTilok').text('-');
            $('#catDetailEvent').text('-');
            $('#catDetailPeriodeWrap').addClass('d-none');
            $('#catDetailKapasitasWrap').addClass('d-none');
            $('#detailKeyFormCreate').val('');
        }
    });
}

function loadSummaryDetailTilok() {
    return $.ajax({
        url: AppConfig.initGlobal + 'fetch/summary-tilok-detail',
        type: 'POST',
        dataType: 'json',
        data: {
            key: CatDetailState.paramKey,
            bulan: CatDetailState.selectedBulan
        },
        success: function (response) {
            // Can be used to sync global stats if needed
        }
    });
}

// -------------------------------------------------------------------------
// LEVEL 1: LIST / CATALOG INSTANSI LOGIC
// -------------------------------------------------------------------------
function loadInstansiList(onDone) {
    $('#loaded').html(`
        <div class="col-12 text-center text-muted py-5">
            <span class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span> Memuat daftar instansi...
        </div>
    `);

    $.ajax({
        url: AppConfig.initGlobal + 'fetch/instansi-tilok-cat',
        type: 'POST',
        dataType: 'json',
        data: {
            key: CatDetailState.paramKey,
            bulan: CatDetailState.selectedBulan
        },
        success: function (response) {
            CatDetailState.allInstansi = Array.isArray(response?.data) ? response.data : [];
            processAndRenderInstansi();
            if (typeof onDone === 'function') onDone();
        },
        error: function () {
            $('#loaded').html(`
                <div class="col-12 text-center text-danger py-5">
                    <i class="bi bi-exclamation-octagon fs-3 d-block mb-2"></i>
                    Gagal memuat daftar instansi. Silakan klik muat ulang.
                </div>
            `);
        }
    });
}

function processAndRenderInstansi() {
    let rendered = [...CatDetailState.allInstansi];

    // Filter Keyword
    if (CatDetailState.filter.keyword) {
        const kw = CatDetailState.filter.keyword.toLowerCase();
        rendered = rendered.filter(row => {
            const nama = String(row.instansi_nama || '').toLowerCase();
            const id = String(row.instansi_id || '').toLowerCase();
            return nama.includes(kw) || id.includes(kw);
        });
    }

    // Filter Status (if any)
    if (CatDetailState.filter.status === 'updated') {
        rendered = rendered.filter(row => Boolean(row.last_update));
    } else if (CatDetailState.filter.status === 'pending') {
        rendered = rendered.filter(row => !row.last_update);
    }

    // Sort
    rendered.sort((a, b) => {
        if (CatDetailState.filter.sort === 'updated_desc') {
            const tA = parseDateToTimestamp(a.last_update) || parseDateToTimestamp(a.max_date);
            const tB = parseDateToTimestamp(b.last_update) || parseDateToTimestamp(b.max_date);
            if (tB !== tA) return tB - tA;
            const nameA = String(a.instansi_nama || '').toLowerCase();
            const nameB = String(b.instansi_nama || '').toLowerCase();
            return nameA.localeCompare(nameB, 'id');
        }
        if (CatDetailState.filter.sort === 'sessions_desc') {
            const diff = Number(b.total_sesi || 0) - Number(a.total_sesi || 0);
            if (diff !== 0) return diff;
            const nameA = String(a.instansi_nama || '').toLowerCase();
            const nameB = String(b.instansi_nama || '').toLowerCase();
            return nameA.localeCompare(nameB, 'id');
        }
        if (CatDetailState.filter.sort === 'peserta_desc') {
            const diff = Number(b.total_peserta || 0) - Number(a.total_peserta || 0);
            if (diff !== 0) return diff;
            const nameA = String(a.instansi_nama || '').toLowerCase();
            const nameB = String(b.instansi_nama || '').toLowerCase();
            return nameA.localeCompare(nameB, 'id');
        }
        // name_asc
        const nameA = String(a.instansi_nama || '').toLowerCase();
        const nameB = String(b.instansi_nama || '').toLowerCase();
        return nameA.localeCompare(nameB, 'id');
    });

    const totalItems = rendered.length;
    const startIndex = (CatDetailState.filter.currentPage - 1) * CatDetailState.filter.itemsPerPage;
    const paginated = rendered.slice(startIndex, startIndex + CatDetailState.filter.itemsPerPage);

    renderInstansiCards(paginated, totalItems);
    renderInstansiPagination(totalItems);
}

function renderInstansiCards(data, totalFiltered) {
    const container = $('#loaded');
    container.empty();

    if (!data || data.length === 0) {
        const isSearching = Boolean(CatDetailState.filter.keyword);
        container.html(`
            <div class="col-12" id="noSearchInfo">
                <div class="d-flex flex-column align-items-center justify-content-center text-center mt-5 mb-5 pb-4 tw-animate-entry">
                    <img src="${AppConfig.initGlobal}apps/assets/images/empty-content-profile.png" alt="Tidak Ditemukan" class="cat-empty-logo-img">
                    <h5 class="cat-empty-title fw-bold">
                        ${isSearching ? 'Pencarian Instansi Tidak Ditemukan' : 'Belum Ada Instansi Terdaftar'}
                    </h5>
                    <p class="cat-empty-desc text-muted mb-3">
                        ${isSearching ? 'Tidak ada data instansi yang cocok dengan pencarian Anda.' : 'Titik lokasi ini belum memiliki instansi dengan data rekap. Silakan tambahkan instansi baru.'}
                    </p>
                    <button type="button" class="btn btn-primary px-4 py-2 fw-semibold cat-btn-empty-tambah" id="btnEmptyTambahInstansi">
                        <i class="bi bi-plus-lg me-1"></i> Tambah Instansi Baru
                    </button>
                </div>
            </div>
        `);
        return;
    }

    let html = '';
    data.forEach((row, index) => {
        const instansiId = escapeHtml(row.instansi_id || '');
        const instansiNama = escapeHtml(row.instansi_nama || row.instansi_id || 'Instansi');
        const totalSesi = formatNumber(row.total_sesi || 0);
        const totalHadir = formatNumber(row.total_hadir || 0);
        const totalTidakHadir = formatNumber(row.total_tidak_hadir || 0);
        const totalPeserta = formatNumber(row.total_peserta || 0);
        const minNilai = row.min_nilai !== null && row.min_nilai !== undefined ? row.min_nilai : '-';
        const maxNilai = row.max_nilai !== null && row.max_nilai !== undefined ? row.max_nilai : '-';
        const rentangNilai = (minNilai !== '-' || maxNilai !== '-') ? `${minNilai} - ${maxNilai}` : '-';
        const lastUpdateText = row.last_update ? formatDateOnly(row.last_update) : 'Belum ada data';
        const isUpdated = Boolean(row.last_update);
        const toneIndex = (index % 4) + 1;
        html += `
        <div class="col-12 tws-col-list tw-animate-entry mb-2">
            <div class="card h-100 p-2 rounded-3 border tws-service-card tws-card-soft tws-anim-card tws-card-instansi shct overflow-hidden position-relative tws-tone-${toneIndex}"
                 data-instansi-id="${instansiId}"
                 data-instansi-nama="${instansiNama}"
                 data-logo="${escapeHtml(row.logo || '')}"
                 data-sesi="${totalSesi}"
                 data-hadir="${totalHadir}"
                 data-tidak-hadir="${totalTidakHadir}"
                 data-peserta="${totalPeserta}"
                 data-min="${minNilai}"
                 data-max="${maxNilai}">
                
                <div class="position-absolute tws-bg-icon-wrapper cat-bg-opacity-5">
                    <div class="tws-bg-icon-svg">
                        <svg viewBox="0 0 24 24" aria-hidden="true" stroke="currentColor" fill="none" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <rect x="4" y="2" width="16" height="20" rx="2" ry="2"></rect>
                            <line x1="9" y1="22" x2="9" y2="22.01"></line>
                            <line x1="15" y1="22" x2="15" y2="22.01"></line>
                            <line x1="9" y1="18" x2="9" y2="18.01"></line>
                            <line x1="15" y1="18" x2="15" y2="18.01"></line>
                            <line x1="9" y1="14" x2="9" y2="14.01"></line>
                            <line x1="15" y1="14" x2="15" y2="14.01"></line>
                            <line x1="9" y1="10" x2="9" y2="10.01"></line>
                            <line x1="15" y1="10" x2="15" y2="10.01"></line>
                            <line x1="9" y1="6" x2="9" y2="6.01"></line>
                            <line x1="15" y1="6" x2="15" y2="6.01"></line>
                        </svg>
                    </div>
                </div>

                <div class="card-body p-0 d-flex flex-column flex-md-row align-items-md-center justify-content-between cat-card-body-relative">
                    <div class="d-flex align-items-center gap-3">
                        <div class="flex-shrink-0 d-flex align-items-center justify-content-center cat-instansi-logo-box">
                            ${row.logo ? `
                                <img src="${AppConfig.initGlobal}apps/assets/images/instansi/${escapeHtml(row.logo)}" 
                                     alt="Logo ${instansiNama}" 
                                     class="img-fluid cat-instansi-logo-img" 
                                     onerror="this.onerror=null; this.parentElement.innerHTML='<div class=\\'d-flex align-items-center justify-content-center text-center bg-light border rounded-3 text-secondary fw-bold cat-no-logo-box\\'>No<br>Logo</div>';">
                            ` : `
                                <div class="d-flex align-items-center justify-content-center text-center bg-light border rounded-3 text-secondary fw-bold cat-no-logo-box">
                                    No<br>Logo
                                </div>
                            `}
                        </div>
                        <div class="text-start">
                            <div class="d-flex align-items-center gap-2 mb-1">
                                <h6 class="tws-card-instansi-title mb-0" title="${instansiNama}">${instansiNama}</h6>
                            </div>
                            <div class="d-flex flex-wrap gap-1 align-items-center mt-1 cat-card-meta-row">
                                <span class="cat-card-meta-badge cat-meta-tilok" title="Total Sesi">
                                    <i class="bi bi-calendar-check"></i> ${totalSesi} Sesi
                                </span>
                                <span class="cat-card-meta-badge cat-meta-peserta" title="Peserta Hadir">
                                    <i class="bi bi-person-check-fill"></i> ${totalHadir} Hadir
                                </span>
                                ${totalTidakHadir !== '0' ? `
                                    <span class="cat-card-meta-badge cat-meta-tidak-hadir" title="Tidak Hadir">
                                        <i class="bi bi-person-x-fill"></i> ${totalTidakHadir} Absen
                                    </span>
                                ` : ''}
                                ${(minNilai !== '-' || maxNilai !== '-') ? `
                                    <span class="cat-card-meta-badge cat-meta-skor" title="Rentang Skor">
                                        <i class="bi bi-award-fill"></i> Skor ${minNilai} - ${maxNilai}
                                    </span>
                                ` : ''}
                                ${row.last_update ? `
                                    <span class="cat-card-meta-badge cat-meta-rekap" title="Tanggal Rekap Terakhir">
                                        <i class="bi bi-clock-history"></i> ${lastUpdateText}
                                    </span>
                                ` : ''}
                            </div>
                        </div>
                    </div>
                    <div class="d-flex align-items-center gap-2 mt-3 mt-md-0 px-2 px-md-0 h-100">
                        <button type="button" class="btn btn-outline-danger p-0 d-flex align-items-center justify-content-center cat-instansi-delete-btn js-delete-instansi" data-instansi-id="${instansiId}" data-instansi-nama="${instansiNama}" title="Hapus Instansi dari Titik Lokasi">
                            <i class="bi bi-trash"></i>
                        </button>
                        <button type="button" class="btn btn-primary p-0 d-flex align-items-center justify-content-center text-white shadow-sm tws-access-btn cat-instansi-arrow-btn" title="Lihat Rekap Data">
                            <i class="bi bi-arrow-right d-flex align-items-center justify-content-center cat-instansi-arrow-icon"></i>
                        </button>
                    </div>
                </div>
            </div>
        </div>
        `;
    });

    container.html(html);
}

function renderInstansiPagination(totalItems) {
    const wrap = $('#instansiPaginationWrap');
    wrap.empty();

    if (totalItems <= CatDetailState.filter.itemsPerPage) {
        return;
    }

    const totalPages = Math.ceil(totalItems / CatDetailState.filter.itemsPerPage);
    let html = '<ul class="pagination pagination-rounded tws-pagination mb-0">';

    const prevDisabled = CatDetailState.filter.currentPage === 1 ? 'disabled' : '';
    html += `
        <li class="page-item ${prevDisabled}">
            <a class="page-link tws-page-link" href="javascript:void(0)" data-page="${CatDetailState.filter.currentPage - 1}" aria-label="Previous">
                <i class="bi bi-chevron-left"></i>
            </a>
        </li>
    `;

    for (let i = 1; i <= totalPages; i++) {
        if (i === 1 || i === totalPages || (i >= CatDetailState.filter.currentPage - 1 && i <= CatDetailState.filter.currentPage + 1)) {
            const activeClass = i === CatDetailState.filter.currentPage ? 'active' : '';
            html += `<li class="page-item ${activeClass}"><a class="page-link tws-page-link" href="javascript:void(0)" data-page="${i}">${i}</a></li>`;
        } else if (i === CatDetailState.filter.currentPage - 2 || i === CatDetailState.filter.currentPage + 2) {
            html += `<li class="page-item disabled"><span class="page-link">...</span></li>`;
        }
    }

    const nextDisabled = CatDetailState.filter.currentPage === totalPages ? 'disabled' : '';
    html += `
        <li class="page-item ${nextDisabled}">
            <a class="page-link tws-page-link" href="javascript:void(0)" data-page="${CatDetailState.filter.currentPage + 1}" aria-label="Next">
                <i class="bi bi-chevron-right"></i>
            </a>
        </li>
    `;

    html += '</ul>';
    wrap.html(html);
}

// -------------------------------------------------------------------------
// LEVEL 2: DRILLDOWN EVENT LOGIC
// -------------------------------------------------------------------------
function openInstansiEventView(instansiId, instansiNama, logo = '') {
    CatDetailState.currentLevel = 'event';
    CatDetailState.activeInstansi = {
        id: instansiId,
        nama: instansiNama,
        logo: logo,
        stats: {}
    };

    $('#eventActiveInstansiTitle').text(instansiNama || 'Daftar Event');
    $('#eventActiveInstansiNama').text(instansiNama || '-');

    // Update Active Logo di Level 2
    const logoWrap = $('#eventActiveLogoWrap');
    if (logo) {
        logoWrap.html(`
            <img src="${AppConfig.initGlobal}apps/assets/images/instansi/${escapeHtml(logo)}" 
                 alt="Logo" 
                 class="img-fluid cat-active-instansi-logo-img" 
                 onerror="this.onerror=null; this.parentElement.innerHTML='<div class=\\'d-flex align-items-center justify-content-center text-center bg-light border rounded-3 text-secondary fw-bold cat-no-logo-box\\'>No<br>Logo</div>';">
        `);
    } else {
        logoWrap.html(`
            <div class="d-flex align-items-center justify-content-center text-center bg-light border rounded-3 text-secondary fw-bold cat-no-logo-box">
                No<br>Logo
            </div>
        `);
    }
    
    // Switch View
    $('#viewLevelInstansi').addClass('d-none');
    $('#viewLevelRekap').addClass('d-none');
    $('#viewLevelEvent').removeClass('d-none');

    loadEventList();
}

function loadEventList() {
    $('#loadedEvents').html(`
        <div class="col-12 text-center text-muted py-5">
            <span class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span> Memuat daftar event...
        </div>
    `);

    $.ajax({
        url: AppConfig.initGlobal + 'FasilitasiCAT/getEventsByInstansi',
        type: 'POST',
        dataType: 'json',
        data: {
            tilok_uid: CatDetailState.paramKey,
            instansi_id: CatDetailState.activeInstansi.id
        },
        success: function (response) {
            CatDetailState.allEvents = Array.isArray(response?.data) ? response.data : [];
            renderEventCards(CatDetailState.allEvents);
        },
        error: function () {
            $('#loadedEvents').html(`
                <div class="col-12 text-center text-danger py-5">
                    <i class="bi bi-exclamation-octagon fs-3 d-block mb-2"></i>
                    Gagal memuat daftar event. Silakan klik muat ulang.
                </div>
            `);
        }
    });
}

function renderEventCards(data) {
    const container = $('#loadedEvents');
    container.empty();

    if (!data || data.length === 0) {
        container.html(`
            <div class="col-12" id="noSearchInfoEvent">
                <div class="d-flex flex-column align-items-center justify-content-center text-center mt-5 mb-5 pb-4 tw-animate-entry">
                    <img src="${AppConfig.initGlobal}apps/assets/images/empty-content-profile.png" alt="Tidak Ditemukan" class="cat-empty-logo-img">
                    <h5 class="cat-empty-title fw-bold">Belum Ada Event Terdaftar</h5>
                    <p class="cat-empty-desc text-muted mb-3">
                        Instansi ini belum memiliki event dengan data rekap pada titik lokasi ini.
                    </p>
                    <button type="button" class="btn btn-primary px-4 py-2 fw-semibold cat-btn-empty-tambah" onclick="$('#btnOpenTambahEvent').click()">
                        <i class="bi bi-plus-lg me-1"></i> Tambah Event Baru
                    </button>
                </div>
            </div>
        `);
        return;
    }

    let html = '';
    data.forEach((row, index) => {
        const seleksiId = row.seleksi_id;
        const namaSeleksi = escapeHtml(row.nama_seleksi || 'Event');
        const jenisTes = escapeHtml(row.jenis_tes || '-');
        const totalSesi = formatNumber(row.total_sesi || 0);
        const lastUpdateText = row.last_update ? formatDateOnly(row.last_update) : 'Belum ada data';
        
        const rowJson = escapeHtml(JSON.stringify(row));
        
        html += `
        <div class="col-12 tws-col-list tw-animate-entry mb-2">
            <div class="card h-100 p-2 rounded-3 border tws-service-card tws-card-soft tws-anim-card tws-card-event shct overflow-hidden position-relative"
                 data-seleksi-id="${seleksiId}"
                 data-event='${rowJson}'>
                 
                <div class="card-body p-0 d-flex flex-column flex-md-row align-items-md-center justify-content-between cat-card-body-relative px-3 py-2">
                    <div class="d-flex align-items-center gap-3">
                        <div class="text-start">
                            <div class="d-flex align-items-center gap-2 mb-1">
                                <h6 class="tws-card-instansi-title mb-0" title="${namaSeleksi}">${namaSeleksi}</h6>
                            </div>
                            <div class="d-flex flex-wrap gap-1 align-items-center mt-1 cat-card-meta-row">
                                <span class="cat-card-meta-badge cat-meta-kapasitas" title="Jenis Tes">
                                    <i class="bi bi-tag-fill"></i> ${jenisTes}
                                </span>
                                <span class="cat-card-meta-badge cat-meta-tilok" title="Total Sesi">
                                    <i class="bi bi-calendar-check"></i> ${totalSesi} Sesi
                                </span>
                                ${row.last_update ? `
                                    <span class="cat-card-meta-badge cat-meta-rekap" title="Tanggal Rekap Terakhir">
                                        <i class="bi bi-clock-history"></i> ${lastUpdateText}
                                    </span>
                                ` : ''}
                            </div>
                        </div>
                    </div>
                    <div class="d-flex align-items-center gap-2 mt-3 mt-md-0 px-2 px-md-0 h-100">
                        <button type="button" class="btn btn-outline-secondary p-0 d-flex align-items-center justify-content-center btn-edit-event cat-event-edit-btn" data-seleksi-id="${seleksiId}" title="Ubah Event Seleksi">
                            <i class="bi bi-pencil-square"></i>
                        </button>
                        <button type="button" class="btn btn-primary p-0 d-flex align-items-center justify-content-center text-white shadow-sm tws-access-btn cat-instansi-arrow-btn" title="Lihat Rekap Data">
                            <i class="bi bi-arrow-right d-flex align-items-center justify-content-center cat-instansi-arrow-icon"></i>
                        </button>
                    </div>
                </div>
            </div>
        </div>
        `;
    });

    container.html(html);
}

// -------------------------------------------------------------------------
// LEVEL 3: DRILLDOWN REKAP DATATABLE LOGIC
// -------------------------------------------------------------------------
function openInstansiRekapView(seleksiId, eventData) {
    CatDetailState.currentLevel = 'rekap';
    CatDetailState.activeEvent = {
        id: seleksiId,
        ...eventData
    };

    // Update Banner Info
    $('#rekapActiveInstansiNama').text(CatDetailState.activeInstansi.nama || '-');
    $('#statTotalSesi').text(formatNumber(eventData.total_sesi || 0));
    $('#statTotalHadir').text(formatNumber(eventData.hadir || 0));
    $('#statTotalTidakHadir').text(formatNumber(eventData.tidak_hadir || 0));

    const minScore = eventData.min_nilai !== null && eventData.min_nilai !== undefined && eventData.min_nilai !== '' ? eventData.min_nilai : null;
    const maxScore = eventData.max_nilai !== null && eventData.max_nilai !== undefined && eventData.max_nilai !== '' ? eventData.max_nilai : null;
    if (minScore !== null && maxScore !== null) {
        $('#statRentangNilai').text(`${formatNumber(minScore)} - ${formatNumber(maxScore)}`);
    } else if (maxScore !== null) {
        $('#statRentangNilai').text(formatNumber(maxScore));
    } else {
        $('#statRentangNilai').text('-');
    }

    // Update Active Logo
    const logoWrap = $('#rekapActiveLogoWrap');
    const logo = CatDetailState.activeInstansi.logo;
    if (logo) {
        logoWrap.html(`
            <img src="${AppConfig.initGlobal}apps/assets/images/instansi/${escapeHtml(logo)}" 
                 alt="Logo" 
                 class="img-fluid cat-active-instansi-logo-img" 
                 onerror="this.onerror=null; this.parentElement.innerHTML='<div class=\\'d-flex align-items-center justify-content-center text-center bg-light border rounded-3 text-secondary fw-bold cat-no-logo-box\\'>No<br>Logo</div>';">
        `);
    } else {
        logoWrap.html(`
            <div class="d-flex align-items-center justify-content-center text-center bg-light border rounded-3 text-secondary fw-bold cat-no-logo-box">
                No<br>Logo
            </div>
        `);
    }

    // Switch View
    $('#viewLevelInstansi').addClass('d-none');
    $('#viewLevelEvent').addClass('d-none');
    $('#viewLevelRekap').removeClass('d-none');

    // Reload DataTable
    table.ajax.reload(function () {
        table.columns.adjust().responsive.recalc();
    });
}

function backToInstansiList() {
    CatDetailState.currentLevel = 'instansi';
    CatDetailState.activeInstansi = { id: '', nama: '', stats: {} };
    CatDetailState.activeEvent = null;

    $('#viewLevelRekap').addClass('d-none');
    $('#viewLevelEvent').addClass('d-none');
    $('#viewLevelInstansi').removeClass('d-none');

    loadInstansiList();
}

function backToEventList() {
    CatDetailState.currentLevel = 'event';
    CatDetailState.activeEvent = null;

    if (CatDetailState.activeInstansi) {
        $('#eventActiveInstansiTitle').text(CatDetailState.activeInstansi.nama || 'Daftar Event');
        $('#eventActiveInstansiNama').text(CatDetailState.activeInstansi.nama || '-');

        const logoWrap = $('#eventActiveLogoWrap');
        const logo = CatDetailState.activeInstansi.logo;
        if (logo) {
            logoWrap.html(`
                <img src="${AppConfig.initGlobal}apps/assets/images/instansi/${escapeHtml(logo)}" 
                     alt="Logo" 
                     class="img-fluid cat-active-instansi-logo-img" 
                     onerror="this.onerror=null; this.parentElement.innerHTML='<div class=\\'d-flex align-items-center justify-content-center text-center bg-light border rounded-3 text-secondary fw-bold cat-no-logo-box\\'>No<br>Logo</div>';">
            `);
        } else {
            logoWrap.html(`
                <div class="d-flex align-items-center justify-content-center text-center bg-light border rounded-3 text-secondary fw-bold cat-no-logo-box">
                    No<br>Logo
                </div>
            `);
        }
    }

    $('#viewLevelRekap').addClass('d-none');
    $('#viewLevelInstansi').addClass('d-none');
    $('#viewLevelEvent').removeClass('d-none');

    loadEventList();
}

window.loadInstansiList = loadInstansiList;
window.loadSummaryDetailTilok = loadSummaryDetailTilok;
window.loadEventList = loadEventList;
window.openInstansiEventView = openInstansiEventView;
window.openInstansiRekapView = openInstansiRekapView;
window.backToInstansiList = backToInstansiList;
window.backToEventList = backToEventList;

// -------------------------------------------------------------------------
// DATATABLE INITIALIZATION (LEVEL 2)
// -------------------------------------------------------------------------
const processingState = (window.ServiceTableUI && ServiceTableUI.createProcessingState)
    ? ServiceTableUI.createProcessingState('Memuat rekap detail sesi...')
    : '<div class="text-center text-muted py-4">Memuat data...</div>';

const table = $('#dataTable').DataTable({
    responsive: {
        details: { type: 'column', target: 'td.dtr-control' }
    },
    processing: true,
    serverSide: true,
    order: [],
    buttons: [
        { extend: 'copy', exportOptions: { columns: ':not(:first-child):not(:last-child)' } },
        { extend: 'excel', exportOptions: { columns: ':not(:first-child):not(:last-child)' } },
        { extend: 'pdf', exportOptions: { columns: ':not(:first-child):not(:last-child)' } },
        { extend: 'print', exportOptions: { columns: ':not(:first-child):not(:last-child)' } }
    ],
    ajax: {
        url: AppConfig.initGlobal + 'fetch/data-tilok-detail',
        type: 'POST',
        data: function (d) {
            d.key = (typeof TILOK_UID !== 'undefined' && TILOK_UID) ? TILOK_UID : (CatDetailState.paramKey || '');
            d.bulan = CatDetailState.selectedBulan;
            d.instansi_id = CatDetailState.activeInstansi.id || '';
            d.seleksi_id = CatDetailState.activeEvent ? CatDetailState.activeEvent.id : '';
            d.tanggal = $('#filterTanggalSesi').val() || '';
            return d;
        }
    },
    columnDefs: [{ className: 'dtr-control text-center', targets: 0, orderable: false }],
    columns: [
        { data: null, defaultContent: '' },
        {
            data: 'period_date',
            className: 'text-start',
            render: function (data, type) {
                if (type === 'sort' || type === 'type') {
                    return data || '';
                }
                return data ? formatDateOnly(data) : '-';
            }
        },
        {
            data: 'sesi',
            className: 'text-center',
            render: function (data) {
                return (data != null && data !== '') ? String(data) : '-';
            }
        },
        {
            data: 'nilai_min',
            className: 'text-center',
            render: function (data) {
                return (data != null && data !== '') ? String(data) : '-';
            }
        },
        {
            data: 'nilai_max',
            className: 'text-center',
            render: function (data) {
                return (data != null && data !== '') ? String(data) : '-';
            }
        },
        {
            data: 'hadir',
            className: 'text-center',
            render: function (data) {
                return (data != null && data !== '') ? String(data) : '0';
            }
        },
        {
            data: 'tidak_hadir',
            className: 'text-center',
            render: function (data) {
                return (data != null && data !== '') ? String(data) : '0';
            }
        },
        {
            data: 'reschedule',
            className: 'text-center',
            render: function (data) {
                return (data != null && data !== '') ? String(data) : '0';
            }
        },
        {
            data: null,
            orderable: false,
            className: 'text-center fw-semibold',
            render: function (data, type, row) {
                const total = (parseInt(row.hadir) || 0) + (parseInt(row.tidak_hadir) || 0);
                return String(total);
            }
        },
        {
            data: 'memenuhi',
            className: 'text-center',
            render: function (data) {
                return (data != null && data !== '') ? String(data) : '-';
            }
        },
        {
            data: 'tidak_memenuhi',
            className: 'text-center',
            render: function (data) {
                return (data != null && data !== '') ? String(data) : '-';
            }
        },
        {
            data: 'created_by',
            className: 'text-start',
            render: function (data) {
                return data ? escapeHtml(String(data)) : '-';
            }
        },
        {
            data: 'created_at',
            className: 'text-center',
            render: function (data) {
                return data ? formatDateOnly(data) : '-';
            }
        },
        {
            data: null,
            orderable: false,
            searchable: false,
            className: 'text-center',
            render: function(data, type, row) {
                return `
                    <div class="d-inline-flex gap-1">
                        <button class="btn btn-sm btn-outline-primary btn-update" data-id="${row.id}" title="Edit Sesi">
                            <i class='bi bi-pencil'></i>
                        </button>  
                        <button class="btn btn-sm btn-outline-danger btn-remove" data-id="${row.id}" title="Hapus Sesi">
                            <i class='bi bi-trash'></i>
                        </button>
                    </div>
                `;
            }
        }
    ],
    language: {
        emptyTable: (window.ServiceTableUI ? ServiceTableUI.createEmptyState() : 'Tidak ada data rekap sesi'),
        zeroRecords: (window.ServiceTableUI ? ServiceTableUI.createEmptyState() : 'Tidak ada data rekap sesi'),
        processing: processingState
    }
});

// Sinkronisasi KPI Banner & Update Terakhir Sesuai Data Rekapan Sesi
table.on('xhr.dt', function (e, settings, json) {
    if (json && json.summary_stat) {
        const stat = json.summary_stat;
        $('#statTotalSesi').text(formatNumber(stat.total_sesi || 0));
        $('#statTotalHadir').text(formatNumber(stat.total_hadir || 0));
        $('#statTotalTidakHadir').text(formatNumber(stat.total_tidak_hadir || 0));

        const minScore = stat.min_nilai !== null && stat.min_nilai !== undefined && stat.min_nilai !== '' ? stat.min_nilai : null;
        const maxScore = stat.max_nilai !== null && stat.max_nilai !== undefined && stat.max_nilai !== '' ? stat.max_nilai : null;
        if (minScore !== null && maxScore !== null) {
            $('#statRentangNilai').text(`${formatNumber(minScore)} - ${formatNumber(maxScore)}`);
        } else if (maxScore !== null) {
            $('#statRentangNilai').text(formatNumber(maxScore));
        } else {
            $('#statRentangNilai').text('-');
        }
    }

    let lastUpdateVal = json?.last_update || null;
    if (!lastUpdateVal && Array.isArray(json?.data) && json.data.length > 0) {
        let maxTs = 0;
        json.data.forEach(function (row) {
            const t = row.updated_at || row.created_at;
            if (t) {
                const ts = parseDateToTimestamp(t);
                if (ts > maxTs) {
                    maxTs = ts;
                    lastUpdateVal = t;
                }
            }
        });
    }

    if (!lastUpdateVal && CatDetailState.activeInstansi && CatDetailState.activeInstansi.stats && CatDetailState.activeInstansi.stats.last_update) {
        lastUpdateVal = CatDetailState.activeInstansi.stats.last_update;
    }

    const formatted = lastUpdateVal ? formatDateTime(lastUpdateVal) : '-';
    $('[id$="-recap-last"]').text(formatted);
    $('#dataTable-recap-last').text(formatted);
});

// -------------------------------------------------------------------------
// EVENT LISTENERS & BINDINGS
// -------------------------------------------------------------------------
$(document).ready(function () {
    loadMetaDetailTilok();
    loadInstansiList();

    // Search Instansi Input (Level 1)
    let searchTimeout = null;
    $('#searchInstansi').on('input', function () {
        const val = $(this).val().trim();
        CatDetailState.filter.keyword = val;
        CatDetailState.filter.currentPage = 1;

        if (val) {
            $('#clearSearchInstansi').removeClass('d-none');
        } else {
            $('#clearSearchInstansi').addClass('d-none');
        }

        clearTimeout(searchTimeout);
        searchTimeout = setTimeout(function () {
            processAndRenderInstansi();
        }, 250);
    });

    $('#clearSearchInstansi').on('click', function () {
        $('#searchInstansi').val('').trigger('input');
    });

    // Sort Dropdown (Level 1)
    $('#sortInstansi').on('change', function () {
        CatDetailState.filter.sort = $(this).val();
        CatDetailState.filter.currentPage = 1;
        processAndRenderInstansi();
    });

    // Filter Tanggal (Level 3 DataTable)
    $('#filterTanggalSesi').on('change', function () {
        const hasFilter = $(this).val();
        if (hasFilter) {
            $('#btnResetFilterSesi').removeClass('d-none');
        } else {
            $('#btnResetFilterSesi').addClass('d-none');
        }
        table.ajax.reload();
    });

    $('#btnResetFilterSesi').on('click', function () {
        $('#filterTanggalSesi').val('');
        $(this).addClass('d-none');
        table.ajax.reload();
    });

    // Pagination Click (Level 1)
    $(document).on('click', '#instansiPaginationWrap .tws-page-link', function (e) {
        e.preventDefault();
        const parent = $(this).parent();
        if (parent.hasClass('disabled') || parent.hasClass('active')) return;

        const page = parseInt($(this).data('page'));
        if (page && page !== CatDetailState.filter.currentPage) {
            CatDetailState.filter.currentPage = page;
            processAndRenderInstansi();
            $('html, body').animate({ scrollTop: $('#viewLevelInstansi').offset().top - 80 }, 200);
        }
    });

    // Card Instansi Click -> Drilldown Level 2 (Event)
    $(document).on('click', '.tws-card-instansi', function (e) {
        if ($(e.target).closest('button, a').length && !$(e.target).closest('.tws-access-btn').length) return;

        const card = $(this);
        const arrowBtn = card.find('.tws-access-btn');
        const originalContent = arrowBtn.html();

        // Ubah icon arrow menjadi loader bold
        arrowBtn.html('<span class="spinner-border spinner-border-sm cat-spinner-bold" role="status" aria-hidden="true"></span>');

        const instansiId = card.data('instansi-id');
        const instansiNama = card.data('instansi-nama');
        const logo = card.data('logo') || '';

        setTimeout(function () {
            openInstansiEventView(instansiId, instansiNama, logo);
            arrowBtn.html(originalContent);
        }, 120);
    });

    // Card Event Click -> Drilldown Level 3 (Rekap)
    $(document).on('click', '.tws-card-event', function (e) {
        if ($(e.target).closest('button, a').length && !$(e.target).closest('.tws-access-btn').length) return;

        const card = $(this);
        const arrowBtn = card.find('.tws-access-btn');
        const originalContent = arrowBtn.html();

        // Ubah icon arrow menjadi loader bold
        arrowBtn.html('<span class="spinner-border spinner-border-sm cat-spinner-bold" role="status" aria-hidden="true"></span>');

        const seleksiId = card.data('seleksi-id');
        const eventData = card.data('event');

        setTimeout(function () {
            openInstansiRekapView(seleksiId, eventData);
            arrowBtn.html(originalContent);
        }, 120);
    });

    // Back to Instansi List Buttons
    $(document).on('click', '.js-back-to-instansi', function () {
        backToInstansiList();
    });

    // Back to Event List Buttons
    $(document).on('click', '.js-back-to-event', function () {
        backToEventList();
    });

    // Header Back Button Multi-Level Navigation
    $('#btnHeaderBack').on('click', function (e) {
        if (CatDetailState.currentLevel === 'rekap') {
            e.preventDefault();
            backToEventList();
        } else if (CatDetailState.currentLevel === 'event') {
            e.preventDefault();
            backToInstansiList();
        } else {
            const seleksiUid = CatDetailState.tilokMeta?.seleksi_uid || CatDetailState.tilokMeta?.jenis_periode_uid || (typeof SELEKSI_UID !== 'undefined' ? SELEKSI_UID : '');
            if (seleksiUid) {
                e.preventDefault();
                window.location.href = AppConfig.initGlobal + 'apps-cat-tilok/' + seleksiUid;
            } else {
                const currentHref = $(this).attr('href');
                if (!currentHref || currentHref === 'javascript:void(0)' || currentHref === '#') {
                    e.preventDefault();
                    window.location.href = AppConfig.initGlobal + 'apps-cat';
                }
            }
        }
    });

    // Reload Data Button
    $('#btnReloadData').on('click', function () {
        if (CatDetailState.currentLevel === 'rekap') {
            table.ajax.reload();
        } else {
            loadInstansiList();
        }
    });

    // Empty State Add Instansi Click
    $(document).on('click', '#btnEmptyTambahInstansi', function () {
        $('#btnOpenTambahInstansi').trigger('click');
    });

    // Delete Instansi dari Titik Lokasi (Level 1)
    $(document).on('click', '.js-delete-instansi', function (e) {
        e.preventDefault();
        e.stopPropagation();

        const instansiId = $(this).attr('data-instansi-id');
        const instansiNama = $(this).attr('data-instansi-nama') || 'Instansi ini';
        const tilokKey = (CatDetailState && (CatDetailState.tilokMeta?.id || CatDetailState.paramKey)) 
            || (typeof TILOK_UID !== 'undefined' ? TILOK_UID : '');

        if (!instansiId || !tilokKey) {
            if (typeof swlErrorHandler === 'function') swlErrorHandler('Data instansi atau titik lokasi tidak valid.');
            return;
        }

        Swal.fire({
            title: "Hapus Instansi?",
            text: `Apakah Anda yakin ingin menghapus "${instansiNama}" dari titik lokasi ini? Seluruh data rekap sesi terkait instansi ini pada tilok ini juga akan dihapus.`,
            icon: "warning",
            showCancelButton: true,
            confirmButtonColor: "#d63031",
            cancelButtonColor: "#6c757d",
            confirmButtonText: "Ya, Hapus Instansi",
            cancelButtonText: "Batal"
        }).then((result) => {
            if (result.isConfirmed) {
                if (typeof swlwaitProsessing === 'function') swlwaitProsessing();
                $.ajax({
                    type: "POST",
                    url: AppConfig.initGlobal + "kill/data-tilok-instansi",
                    data: {
                        tilok_id: tilokKey,
                        instansi_id: instansiId
                    },
                    dataType: "json",
                    success: function (response) {
                        if (response && (response.status === true || response.status === 'success')) {
                            if (typeof swlSuccess === 'function') {
                                swlSuccess('Instansi berhasil dihapus dari titik lokasi.');
                            }
                            loadInstansiList();
                            if (window.loadSummaryDetailTilok) {
                                loadSummaryDetailTilok();
                            }
                        } else {
                            const msg = (response && response.message) ? response.message : 'Gagal menghapus instansi.';
                            if (typeof swlErrorHandler === 'function') swlErrorHandler(msg);
                        }
                    },
                    error: function () {
                        if (typeof swlErrorHandler === 'function') {
                            swlErrorHandler('Terjadi kesalahan pada server saat menghapus instansi.');
                        }
                    }
                });
            }
        });
    });

    // Row Delete in DataTable
    $('#dataTable tbody').on('click', 'tr td .btn-remove', function () {
        const key = $(this).attr('data-id');
        Swal.fire({
            text: "Apa anda yakin akan menghapus data rekap sesi ini?",
            icon: "warning",
            showCancelButton: true,
            confirmButtonColor: "#d63031",
            confirmButtonText: "Ya, Hapus",
            cancelButtonText: "Batal"
        }).then((result) => {
            if (result.isConfirmed) {
                if (typeof swlwaitProsessing === 'function') swlwaitProsessing();
                $.ajax({
                    type: "POST",
                    url: AppConfig.initGlobal + "kill/data-tilok-rekap",
                    data: { key: key },
                    success: function (response) {
                        if (response) {
                            if (typeof swlSuccess === 'function') swlSuccess();
                            table.ajax.reload(null, false);
                            loadSummaryDetailTilok();
                        }
                    },
                    error: function () {
                        if (typeof swlErrorHandler === 'function') swlErrorHandler('Gagal menghapus data.');
                    }
                });
            }
        });
    });

    // Row Update in DataTable
    $('#dataTable tbody').on('click', '.btn-update', function () {
        let tr = $(this).closest('tr');
        if (tr.hasClass('child')) {
            tr = tr.prev('.parent');
        }

        const row = table.row(tr).data();
        if (!row) return;

        const form = $('#form-usulan-edit');
        $('#DataModalLabel').text('Update Data Rekap Sesi');

        form.find('[name="key"]').val(row.id);
        form.find('[name="tanggal"]').val(row.period_date);
        form.find('[name="sesi"]').val(row.sesi);
        form.find('[name="nilai_min"]').val(row.nilai_min);
        form.find('[name="nilai_max"]').val(row.nilai_max);
        form.find('[name="hadir"]').val(row.hadir);
        form.find('[name="tidak_hadir"]').val(row.tidak_hadir);
        form.find('[name="reschedule"]').val(row.reschedule);
        form.find('[name="memenuhi"]').val(row.memenuhi);
        form.find('[name="tidak_memenuhi"]').val(row.tidak_memenuhi);

        $('#DataModalDetail').modal('show');
        $('#DataModalDetail').one('shown.bs.modal', function () {
            const instansi = row.instansi_id || '';
            const instansiText = row.instansi_nama || '';
            const select = form.find('[name="instansi"]');

            if (instansi) {
                const option = new Option(instansiText, instansi, true, true);
                select.empty().append(option).trigger('change');
            }
        });
    });

    // Edit Form Submission
    $(document).on('click', '.sbmt-edit', function () {
        $('#form-usulan-edit').submit();
    });

    $('#form-usulan-edit').on('submit', function (e) {
        e.preventDefault();
        $('#DataModalDetail').modal('hide');
        if (typeof swlwaitProsessing === 'function') swlwaitProsessing();
        $.ajax({
            url: AppConfig.initGlobal + 'store/update-data-hasil-cat',
            type: 'POST',
            data: $(this).serialize(),
            success: function (response) {
                if (response && response.status === 'error') {
                    if (typeof swlErrorHandler === 'function') swlErrorHandler(response.message);
                } else {
                    table.ajax.reload();
                    if (typeof swlSuccess === 'function') swlSuccess();
                    loadSummaryDetailTilok();
                }
            },
            error: function () {
                if (typeof swlErrorHandler === 'function') swlErrorHandler('Gagal menyimpan perubahan.');
            }
        });
    });

    // Filter Bulan Apply
    const MAX_BULAN = 6;
    $(document).on('change', '.bulan-check', function () {
        const checked = $('.bulan-check:checked');
        if (checked.length > MAX_BULAN) {
            this.checked = false;
            if (typeof swlErrorHandler === 'function') swlErrorHandler('Riwayat ditampilkan maksimal 6 bulan.');
        }
    });

    $('#applyBulan').on('click', function () {
        CatDetailState.selectedBulan = $('.bulan-check:checked')
            .map(function () {
                return this.value;
            })
            .get();

        if (CatDetailState.selectedBulan.length > 6) {
            if (typeof swlErrorHandler === 'function') swlErrorHandler('Silakan pilih maksimal 6 bulan saja.');
            return;
        }

        if (CatDetailState.selectedBulan.length) {
            const namaBulan = bulanList
                .filter(b => CatDetailState.selectedBulan.includes(b.val))
                .map(b => b.text.substring(0, 3));
            $('#dropdownBulan').text(namaBulan.join(', '));
        } else {
            $('#dropdownBulan').text('Pilih Bulan');
        }

        if (CatDetailState.currentLevel === 'rekap') {
            table.ajax.reload();
        } else {
            loadInstansiList();
        }
        loadSummaryDetailTilok();
    });
});
