const CAT_EVENT_API = AppConfig.initGlobal + 'api/apps-cat/events';
let catEventRows = [];
let isCatTilokEditMode = false;

function escapeHtml(value) {
    return String(value || '')
        .replace(/&/g, '&amp;')
        .replace(/</g, '&lt;')
        .replace(/>/g, '&gt;')
        .replace(/"/g, '&quot;')
        .replace(/'/g, '&#039;');
}

function buildCatJenisLabel(row) {
    const kode = String(row && row.kode || '').trim();
    const nama = String(row && row.nama || '').trim();
    if (!kode) return nama;
    if (!nama) return kode;
    return `${kode} - ${nama}`;
}

function getDomCatJenisRows() {
    return $('#catJenisPicker option').map(function () {
        const id = String($(this).attr('value') || '').trim();
        const label = String($(this).text() || '').trim();
        if (!id || !label || id === '') return null;

        const parts = label.split(' - ');
        const kode = String(parts.shift() || '').trim();
        const nama = String(parts.join(' - ') || kode).trim();

        return {
            id: id,
            kode: nama === kode ? '' : kode,
            nama: nama
        };
    }).get().filter(Boolean);
}

function getCatJenisRows() {
    return Array.isArray(catEventRows) && catEventRows.length
        ? catEventRows
        : getDomCatJenisRows();
}

function ensureCatJenisSelect2() {
    const picker = $('#catJenisPicker');
    if (!picker.length) return;

    if (picker.hasClass('select2-hidden-accessible')) {
        picker.select2('destroy');
    }
}

function renderCatJenisOptions(selectedValue) {
    const picker = $('#catJenisPicker');
    if (!picker.length) return;

    const keepValue = String(selectedValue || picker.val() || '').trim();
    const sourceRows = getCatJenisRows();

    picker.empty();
    picker.append('<option value="">Pilih jenis kegiatan...</option>');

    sourceRows.forEach((row) => {
        const id = String(row.id || '').trim();
        if (!id) return;
        picker.append(`<option value="${escapeHtml(id)}">${escapeHtml(buildCatJenisLabel(row))}</option>`);
    });

    if (keepValue && picker.find(`option[value="${keepValue}"]`).length) {
        picker.val(keepValue);
    } else {
        picker.val('');
    }

    if (picker.hasClass('select2-hidden-accessible')) {
        picker.trigger('change.select2');
    }
}

function resetCatEventForm() {
    $('#cat_event_id').val('');
    $('#cat_event_kode').val('');
    $('#cat_event_nama').val('');
    $('#catEventSubmitBtn').text('Simpan Event');
}

function resetCatTilokForm() {
    const form = $('#form-usulan, #formAction');
    if (!form.length || !form[0]) return;

    form[0].reset();
    form.find('[name="key"]').val('');
    form.find('[name="action"]').val('create');
    $('#staticBackdropLabel, #DataModalLabel').text('Tambah Titik Lokasi');
    if (typeof PERIODE_TAHUN !== 'undefined' && PERIODE_TAHUN) {
        form.find('[name="period"]').val(PERIODE_TAHUN);
    }
    if (typeof JENIS_TES_ID !== 'undefined' && JENIS_TES_ID) {
        form.find('[name="jenis_tes_id"]').val(JENIS_TES_ID);
    }
    $('#catJenisPicker').val('').trigger('change');
    isCatTilokEditMode = false;
}

function renderCatEventTable() {
    const body = $('#catEventTableBody');
    if (!body.length) return;

    if (!catEventRows.length) {
        body.html('<tr><td colspan="3" class="text-center text-muted py-3">Belum ada event.</td></tr>');
        return;
    }

    const html = catEventRows.map((row) => {
        const id = Number(row.id || 0);
        const kode = escapeHtml(String(row.kode || '').trim());
        const nama = escapeHtml(String(row.nama || '').trim());
        return `
            <tr>
                <td>${kode}</td>
                <td>${nama}</td>
                <td class="text-center">
                    <div class="d-inline-flex gap-1">
                        <button type="button" class="btn btn-sm btn-outline-primary js-cat-event-edit" data-id="${id}">Edit</button>
                        <button type="button" class="btn btn-sm btn-outline-danger js-cat-event-delete" data-id="${id}" data-nama="${nama}">Hapus</button>
                    </div>
                </td>
            </tr>
        `;
    }).join('');

    body.html(html);
}

function loadCatEventRows(onDone) {
    const selectedValue = String($('#catJenisPicker').val() || '').trim();

    $.ajax({
        url: CAT_EVENT_API,
        type: 'GET',
        dataType: 'json',
        success: function (response) {
            catEventRows = Array.isArray(response && response.data) ? response.data : [];
            window.CAT_EVENT_CACHE = catEventRows.slice();
            renderCatJenisOptions(selectedValue);
            ensureCatJenisSelect2();
            renderCatEventTable();
            if (typeof onDone === 'function') onDone();
        },
        error: function () {
            renderCatJenisOptions(selectedValue);
            ensureCatJenisSelect2();
            swlErrorHandler('Gagal memuat event jenis kegiatan.');
        }
    });
}

function findCatEventById(id) {
    const num = Number(id || 0);
    return catEventRows.find((row) => Number(row.id || 0) === num);
}

function saveCatEvent(payload) {
    const isUpdate = Number(payload.id || 0) > 0;
    const endpoint = isUpdate ? `${CAT_EVENT_API}/update` : CAT_EVENT_API;

    $.ajax({
        url: endpoint,
        type: 'POST',
        dataType: 'json',
        data: payload,
        success: function (response) {
            if (!response || response.status !== 'success') {
                swlErrorHandler(response && response.message ? response.message : 'Gagal menyimpan event.');
                return;
            }
            resetCatEventForm();
            loadCatEventRows();
            swlSuccess(response.message || 'Event berhasil disimpan.');
        },
        error: function (xhr) {
            const message = xhr.responseJSON && xhr.responseJSON.message
                ? xhr.responseJSON.message
                : 'Gagal menyimpan event.';
            swlErrorHandler(message);
        }
    });
}

function deleteCatEvent(id, name) {
    Swal.fire({
        text: `Apa anda yakin akan menghapus event "${name}"?`,
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#d63031',
        confirmButtonText: 'Ya',
        cancelButtonText: 'Tidak'
    }).then((result) => {
        if (!result.isConfirmed) return;

        $.ajax({
            url: `${CAT_EVENT_API}/delete`,
            type: 'POST',
            dataType: 'json',
            data: { id: id },
            success: function (response) {
                if (!response || response.status !== 'success') {
                    swlErrorHandler(response && response.message ? response.message : 'Gagal menghapus event.');
                    return;
                }
                resetCatEventForm();
                loadCatEventRows();
                swlSuccess(response.message || 'Event berhasil dihapus.');
            },
            error: function (xhr) {
                const message = xhr.responseJSON && xhr.responseJSON.message
                    ? xhr.responseJSON.message
                    : 'Gagal menghapus event.';
                swlErrorHandler(message);
            }
        });
    });
}

window.setCatJenisPickerValue = function (id, fallbackText) {
    const picker = $('#catJenisPicker');
    if (!picker.length) return;

    const value = String(id || '').trim();
    if (!value) {
        picker.val('').trigger('change');
        return;
    }

    if (!picker.find(`option[value="${value}"]`).length) {
        const text = String(fallbackText || value).trim();
        picker.append(new Option(text, value, true, true));
    }

    picker.val(value).trigger('change');
};

window.setCatTilokEditMode = function (isEdit) {
    isCatTilokEditMode = Boolean(isEdit);
};

$(document).ready(function () {
    catEventRows = getDomCatJenisRows();
    ensureCatJenisSelect2();
    loadCatEventRows();

    $(document).on('click', '.sbmt', function () {
        $('#form-usulan, #formAction').submit();
    });

    $('#form-usulan, #formAction').on('submit', function (e) {
        e.preventDefault();
        $('#DataModal').modal('hide');
        swlwaitProsessing();
        $.ajax({
            url: AppConfig.initGlobal + 'store/save-data-tilok-cat',
            type: 'POST',
            dataType: 'json',
            data: $(this).serialize(),
            success: function (response) {
                if (response.status === 'error') {
                    swlErrorHandler(response.message);
                    return;
                }

                if (typeof loadTilokData === 'function') {
                    loadTilokData();
                }
                swlSuccess();
            },
            error: function (xhr) {
                const message = xhr.responseJSON && xhr.responseJSON.message
                    ? xhr.responseJSON.message
                    : 'Gagal menyimpan data.';
                swlErrorHandler(message);
            }
        });
    });

    $('#DataModal').on('show.bs.modal', function () {
        if (!isCatTilokEditMode) {
            resetCatTilokForm();
        }
    });

    $('#DataModal').on('shown.bs.modal', function () {
        renderCatJenisOptions();
        ensureCatJenisSelect2();
    });

    // Card Click Handler
    $(document).on('click', '.tws-service-card', function(e) {
        if ($(e.target).closest('button:not(.tws-access-btn), a, .btn-update, .btn-remove').length) return;
        const url = $(this).data('url');
        if (url) {
            const btn = $(this).find('.tws-access-btn');
            if (btn.length) {
                btn.prop('disabled', true);
                btn.html('<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span>');
            }
            window.location.href = url;
        }
    });

    // Modal Events
    $('#DataModal').on('hidden.bs.modal', function () {
        resetCatTilokForm();
    });

    $('#catEventModal').on('shown.bs.modal', function () {
        loadCatEventRows();
    });

    $('#catEventModal').on('hidden.bs.modal', function () {
        resetCatEventForm();
    });

    $('#catEventForm').on('submit', function (e) {
        e.preventDefault();

        const id = Number($('#cat_event_id').val() || 0);
        const kode = String($('#cat_event_kode').val() || '').trim().toUpperCase();
        const nama = String($('#cat_event_nama').val() || '').trim();

        if (!kode || !nama) {
            swlErrorHandler('Kode dan nama event wajib diisi.');
            return;
        }

        $('#catEventModal').modal('hide');
        if (typeof swlwaitProsessing === 'function') swlwaitProsessing();
        saveCatEvent({ id: id > 0 ? id : '', kode: kode, nama: nama });
    });

    $('#catEventResetBtn').on('click', function () {
        resetCatEventForm();
    });

    $('#catEventTableBody').on('click', '.js-cat-event-edit', function () {
        const id = Number($(this).data('id') || 0);
        const row = findCatEventById(id);
        if (!row) return;

        $('#cat_event_id').val(row.id || '');
        $('#cat_event_kode').val(String(row.kode || '').trim());
        $('#cat_event_nama').val(String(row.nama || '').trim());
        $('#catEventSubmitBtn').text('Update Event');
        $('#cat_event_kode').trigger('focus');
    });

    $('#catEventTableBody').on('click', '.js-cat-event-delete', function () {
        const id = Number($(this).data('id') || 0);
        const name = String($(this).data('nama') || '').trim();
        if (id <= 0) return;
        deleteCatEvent(id, name);
    });
});

function getCurrentDateTime() {
    const now = new Date();
    const dd = String(now.getDate()).padStart(2, '0');
    const mm = String(now.getMonth() + 1).padStart(2, '0');
    const yyyy = now.getFullYear();
    const hh = String(now.getHours()).padStart(2, '0');
    const ii = String(now.getMinutes()).padStart(2, '0');
    const ss = String(now.getSeconds()).padStart(2, '0');
    return `${dd}-${mm}-${yyyy} ${hh}:${ii}:${ss}`;
}

function parseDateToTimestamp(dateStr) {
    if (!dateStr) return 0;
    const cleanStr = String(dateStr).trim();
    if (!cleanStr || cleanStr === '-' || cleanStr === '0000-00-00' || cleanStr === '0000-00-00 00:00:00') return 0;
    
    const iso = cleanStr.includes('T') ? cleanStr : cleanStr.replace(' ', 'T');
    const parsed = Date.parse(iso);
    if (!Number.isNaN(parsed)) return parsed;
    
    const fallback = Date.parse(cleanStr.replace(/-/g, '/'));
    return !Number.isNaN(fallback) ? fallback : 0;
}

let allTilokData = [];
let tilokState = {
    keyword: '',
    filter: 'all',
    sort: 'updated_desc',
    currentPage: 1,
    itemsPerPage: 10
};

function updateFilterCounts(list = []) {
    const counts = {
        all: list.length,
        updated: list.filter(item => Number(item.total_rekap || 0) > 0 || Boolean(item.last_rekap_updated)).length,
        pending: list.filter(item => !(Number(item.total_rekap || 0) > 0 || Boolean(item.last_rekap_updated))).length
    };

    $('.tws-filter-chip').each(function () {
        const filter = String($(this).data('filter') || 'all');
        const count = counts[filter] || 0;
        const originalText = $(this).text().replace(/\s*\(\d+\)$/, '');
        $(this).text(`${originalText} (${count})`);
    });
}

function processAndRenderTilok() {
    let rendered = [...allTilokData];

    // Filter by keyword
    if (tilokState.keyword) {
        rendered = rendered.filter(row => {
            const nama = String(row.tilok || row.nama_tilok || row.nama || '').toLowerCase();
            const jenis = String(row.jenis_tes || '').toLowerCase();
            return nama.includes(tilokState.keyword) || jenis.includes(tilokState.keyword);
        });
    }
    
    // Update Counts (based on current keyword search results, matching teamworkService logic)
    updateFilterCounts(rendered);

    // Filter by status
    rendered = rendered.filter(row => {
        const hasRekap = Number(row.total_rekap || 0) > 0 || Boolean(row.last_rekap_updated);
        if (tilokState.filter === 'updated') return hasRekap;
        if (tilokState.filter === 'pending') return !hasRekap;
        return true;
    });

    // Sorting
    rendered.sort((a, b) => {
        if (tilokState.sort === 'updated_desc') {
            const tA = parseDateToTimestamp(a.last_rekap_updated) || parseDateToTimestamp(a.last_rekap_date);
            const tB = parseDateToTimestamp(b.last_rekap_updated) || parseDateToTimestamp(b.last_rekap_date);
            if (tB !== tA) return tB - tA;
            const nameA = String(a.nama_tilok || '').toLowerCase();
            const nameB = String(b.nama_tilok || '').toLowerCase();
            return nameA.localeCompare(nameB, 'id');
        }
        if (tilokState.sort === 'pending_first') {
            const hasA = Number(a.total_rekap || 0) > 0 || Boolean(a.last_rekap_updated);
            const hasB = Number(b.total_rekap || 0) > 0 || Boolean(b.last_rekap_updated);
            if (hasA === hasB) {
                const nameA = String(a.nama_tilok || '').toLowerCase();
                const nameB = String(b.nama_tilok || '').toLowerCase();
                return nameA.localeCompare(nameB, 'id');
            }
            return hasA ? 1 : -1;
        }
        // name_asc
        const nameA = String(a.nama_tilok || '').toLowerCase();
        const nameB = String(b.nama_tilok || '').toLowerCase();
        return nameA.localeCompare(nameB, 'id');
    });

    const totalItems = rendered.length;
    
    // Pagination slicing
    const startIndex = (tilokState.currentPage - 1) * tilokState.itemsPerPage;
    const paginated = rendered.slice(startIndex, startIndex + tilokState.itemsPerPage);

    renderTilokCards(paginated);
    renderPagination(totalItems);
}

function renderPagination(totalItems) {
    const wrap = $('#twsPaginationWrap');
    wrap.empty();

    if (totalItems <= tilokState.itemsPerPage) {
        return; // No pagination needed
    }

    const totalPages = Math.ceil(totalItems / tilokState.itemsPerPage);
    let html = '<ul class="pagination pagination-sm m-0">';

    // Prev Button
    const prevDisabled = tilokState.currentPage === 1 ? 'disabled' : '';
    html += `
        <li class="page-item ${prevDisabled}">
            <a class="page-link tws-page-link" href="javascript:void(0)" data-page="${tilokState.currentPage - 1}" aria-label="Previous">
                <i class="bi bi-chevron-left"></i>
            </a>
        </li>
    `;

    // Page numbers
    for (let i = 1; i <= totalPages; i++) {
        // Simple logic: show all if few, otherwise we could implement complex ellipsis but for 10/page it's usually fine
        // Let's implement a small window logic (e.g. current +- 2)
        if (i === 1 || i === totalPages || (i >= tilokState.currentPage - 1 && i <= tilokState.currentPage + 1)) {
            const activeClass = i === tilokState.currentPage ? 'active' : '';
            html += `<li class="page-item ${activeClass}"><a class="page-link tws-page-link" href="javascript:void(0)" data-page="${i}">${i}</a></li>`;
        } else if (i === tilokState.currentPage - 2 || i === tilokState.currentPage + 2) {
            html += `<li class="page-item disabled"><span class="page-link">...</span></li>`;
        }
    }

    // Next Button
    const nextDisabled = tilokState.currentPage === totalPages ? 'disabled' : '';
    html += `
        <li class="page-item ${nextDisabled}">
            <a class="page-link tws-page-link" href="javascript:void(0)" data-page="${tilokState.currentPage + 1}" aria-label="Next">
                <i class="bi bi-chevron-right"></i>
            </a>
        </li>
    `;

    html += '</ul>';
    wrap.html(html);
}

function loadTilokData() {
    $('#loaded').html('<div class="col-12 text-center text-muted py-5"><span class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span> Memuat data titik lokasi...</div>');
    $.ajax({
        url: AppConfig.initGlobal + 'fetch/data-tilok-cat',
        type: 'POST',
        data: {
            jenis_periode_id: typeof JENIS_PERIODE_ID !== 'undefined' ? JENIS_PERIODE_ID : '',
            jenis_tes_id: typeof JENIS_TES_ID !== 'undefined' ? JENIS_TES_ID : '',
            periode: typeof PERIODE_TAHUN !== 'undefined' ? PERIODE_TAHUN : '',
            draw: 1, length: 1000, start: 0
        },
        success: function(res) {
            if (res && res.data) {
                allTilokData = res.data;
                processAndRenderTilok();
            }
        },
        error: function() {
            $('#loaded').html('<div class="col-12 text-center text-danger py-5">Gagal memuat data titik lokasi.</div>');
        }
    });
}

function renderTilokCards(data) {
    const container = $('#loaded');
    container.empty();

    if (!data || data.length === 0) {
        container.html(`
            <div class="col-12" id="noSearchInfo">
                <div class="d-flex flex-column align-items-center justify-content-center text-center mt-5 mb-5 pb-4 tw-animate-entry">
                    <img src="${AppConfig.initGlobal}apps/assets/images/empty-content-profile.png" alt="Tidak Ditemukan" class="cat-empty-img">
                    <h5 class="fw-bold cat-empty-title">Pencarian Tidak Ditemukan</h5>
                    <p class="text-muted mb-0 cat-empty-desc">Tidak ada data seleksi yang cocok dengan pencarian Anda.</p>
                </div>
            </div>
        `);
        return;
    }

    let html = '';
    data.forEach((row, index) => {
        const start = row.startdate || row.period_start_date || '-';
        const end = row.enddate || row.period_end_date || '-';
        const capNum = Number(row.kapasitas || row.capacity || 0);
        const instansiNum = Number(row.total_instansi || 0);
        const eventNum = Number(row.total_event || 0);
        const rekapNum = Number(row.total_rekap || 0);
        const pesertaNum = Number(row.total_peserta || 0);
        const lastUpdated = row.last_rekap_updated || null;
        const lastDate = row.last_rekap_date || null;
        const nama = escapeHtml(row.tilok || row.nama_tilok || row.nama || '-');

        let metaBadges = `
            <span class="cat-card-meta-badge cat-meta-instansi" title="Total Instansi Terdaftar">
                <i class="bi bi-building"></i> ${instansiNum > 0 ? instansiNum.toLocaleString('id-ID') + ' Instansi' : '0 Instansi'}
            </span>
        `;

        if (eventNum > 0) {
            metaBadges += `
                <span class="cat-card-meta-badge cat-meta-event" title="Total Event Seleksi">
                    <i class="bi bi-bookmark-check"></i> ${eventNum.toLocaleString('id-ID')} Event
                </span>
            `;
        }

        metaBadges += `
            <span class="cat-card-meta-badge cat-meta-tilok" title="Total Sesi Rekapitulasi">
                <i class="bi bi-card-checklist"></i> ${rekapNum > 0 ? rekapNum.toLocaleString('id-ID') + ' Rekap' : 'Belum Ada Rekap'}
            </span>
        `;

        if (pesertaNum > 0) {
            metaBadges += `
                <span class="cat-card-meta-badge cat-meta-peserta" title="Total Peserta Terealisasi">
                    <i class="bi bi-people-fill"></i> ${pesertaNum.toLocaleString('id-ID')} Peserta
                </span>
            `;
        }

        metaBadges += `
            <span class="cat-card-meta-badge cat-meta-kapasitas" title="Kapasitas PC Per Sesi">
                <i class="bi bi-display"></i> ${capNum > 0 ? capNum.toLocaleString('id-ID') + ' PC' : 'Belum Diatur'}
            </span>
        `;

        if (lastUpdated) {
            metaBadges += `
                <span class="cat-card-meta-badge cat-meta-rekap" title="Waktu Rekap / Update Terakhir">
                    <i class="bi bi-clock-history"></i> Update: ${escapeHtml(lastUpdated)}
                </span>
            `;
        } else if (lastDate) {
            metaBadges += `
                <span class="cat-card-meta-badge cat-meta-rekap" title="Tanggal Rekap Terakhir">
                    <i class="bi bi-clock-history"></i> ${escapeHtml(lastDate)}
                </span>
            `;
        } else if (start !== '-' && end !== '-') {
            metaBadges += `
                <span class="cat-card-meta-badge cat-meta-rekap" title="Rentang Tanggal Pelaksanaan">
                    <i class="bi bi-calendar3"></i> ${escapeHtml(start)} s/d ${escapeHtml(end)}
                </span>
            `;
        }

        html += `
        <div class="col-12 tws-col-list tw-animate-entry mb-2">
            <div class="card h-100 p-2 rounded-3 border tws-service-card tws-card-soft tws-anim-card overflow-hidden position-relative tilok-card-wrapper tws-tone-${(index % 4) + 1}" data-url="${AppConfig.initGlobal}apps-cat-detail/${row.uid}">
                <div class="position-absolute tws-bg-icon-wrapper tilok-card-bg-opacity">
                    <div class="tws-bg-icon-svg">
                        <svg viewBox="0 0 24 24" aria-hidden="true" stroke="currentColor" fill="none" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"></path><circle cx="12" cy="10" r="3"></circle></svg>
                    </div>
                </div>
                <div class="card-body p-0 d-flex flex-column flex-md-row align-items-md-center justify-content-between tilok-card-body-pos">
                    <div class="d-flex align-items-center gap-3">
                        <div class="flex-shrink-0 d-flex align-items-center justify-content-center bg-primary-subtle text-primary tilok-card-icon-container">
                            <svg viewBox="0 0 24 24" aria-hidden="true" class="tilok-card-icon-svg"><path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"></path><circle cx="12" cy="10" r="3"></circle></svg>
                        </div>
                        <div class="text-start">
                            <h6 class="fw-bold mb-1 tilok-card-name">${nama}</h6>
                            <div class="d-flex flex-wrap gap-1 align-items-center mt-1 cat-card-meta-row">
                                ${metaBadges}
                            </div>
                        </div>
                    </div>
                    <div class="d-flex align-items-center gap-2 mt-3 mt-md-0 px-2 px-md-0 h-100">
                        <button type="button" class="btn btn-outline-secondary p-0 d-flex align-items-center justify-content-center btn-update tilok-btn-edit" data-id="${row.id}" title="Edit Data">
                            <i class="bi bi-pencil-square"></i>
                        </button>
                        <button type="button" class="btn btn-outline-danger p-0 d-flex align-items-center justify-content-center btn-remove tilok-btn-delete" data-id="${row.id}" title="Hapus Data">
                            <i class="bi bi-trash"></i>
                        </button>
                        <button type="button" class="btn btn-primary p-0 d-flex align-items-center justify-content-center text-white shadow-sm tws-access-btn tilok-btn-detail" title="Detail">
                            <i class="bi bi-arrow-right d-flex align-items-center justify-content-center tilok-btn-detail-icon"></i>
                        </button>
                    </div>
                </div>
            </div>
        </div>
        `;
    });
    container.html(html);
}

$(document).ready(function() {
    loadTilokData();

    $('#searchdata').on('input', function() {
        tilokState.keyword = $(this).val().toLowerCase();
        tilokState.currentPage = 1; // Reset to page 1 on search
        
        if (tilokState.keyword.length > 0) {
            $('#twsClearSearch').removeClass('d-none');
        } else {
            $('#twsClearSearch').addClass('d-none');
        }

        processAndRenderTilok();
    });

    $('#twsClearSearch').on('click', function() {
        $('#searchdata').val('').trigger('input');
        $('#searchdata').focus();
    });

    $('.tws-filter-chip').on('click', function () {
        tilokState.filter = String($(this).data('filter') || 'all');
        tilokState.currentPage = 1; // Reset to page 1 on filter
        $('.tws-filter-chip').removeClass('is-active');
        $(this).addClass('is-active');
        processAndRenderTilok();
    });

    $('#twsSort').on('change', function () {
        tilokState.sort = String($(this).val() || 'name_asc');
        tilokState.currentPage = 1; // Reset to page 1 on sort
        processAndRenderTilok();
    });

    // Pagination Click Handler
    $('#twsPaginationWrap').on('click', '.tws-page-link', function(e) {
        e.preventDefault();
        const parent = $(this).parent();
        if (parent.hasClass('disabled') || parent.hasClass('active')) return;
        
        const newPage = parseInt($(this).data('page'));
        if (!isNaN(newPage)) {
            tilokState.currentPage = newPage;
            processAndRenderTilok();
            
            // Scroll smoothly to top of list
            const searchWrap = $('.tws-search-wrap').offset().top - 80;
            $('html, body').animate({ scrollTop: searchWrap }, 300);
        }
    });

    $('#loaded').on('click', '.btn-update', function () {
        const id = $(this).data('id');
        const row = allTilokData.find(r => String(r.id) === String(id));
        if (!row) return;

        const form = $('#form-usulan, #formAction');
        isCatTilokEditMode = true;

        form.find('[name="key"]').val(row.id || '');
        form.find('[name="action"]').val('update');
        form.find('[name="nama_tilok"], [name="tilok"]').val(row.nama_tilok || row.tilok || '');
        form.find('[name="period"]').val(row.period || (typeof PERIODE_TAHUN !== 'undefined' ? PERIODE_TAHUN : ''));
        form.find('[name="startdate"], [name="period_start_date"]').val(row.period_start_date || '');
        form.find('[name="enddate"], [name="period_end_date"]').val(row.period_end_date || '');
        form.find('[name="kapasitas"], [name="capacity"]').val(row.kapasitas || '');
        form.find('[name="jenis_periode_id"]').val(row.jenis_periode_id || (typeof JENIS_PERIODE_ID !== 'undefined' ? JENIS_PERIODE_ID : ''));
        form.find('[name="jenis_tes_id"]').val(row.jenis_tes_id || (typeof JENIS_TES_ID !== 'undefined' ? JENIS_TES_ID : ''));

        $('#staticBackdropLabel, #DataModalLabel').text('Edit Titik Lokasi');
        $('#DataModal').modal('show');
    });

    $('#loaded').on('click', '.btn-remove', function () {
        const id = $(this).data('id');
        Swal.fire({
            text: 'Apa anda yakin akan menghapus data ini?',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d63031',
            confirmButtonText: 'Ya',
            cancelButtonText: 'Tidak'
        }).then((result) => {
            if (result.isConfirmed) {
                swlwaitProsessing();
                $.ajax({
                    url: AppConfig.initGlobal + 'kill/data-tilok-cat',
                    type: 'POST',
                    data: { key: id },
                    success: function () {
                        swlSuccess();
                        loadTilokData();
                    },
                    error: function () {
                        swlErrorHandler('Gagal menghapus data.');
                    }
                });
            }
        });
    });

    $('.js-service-reload').on('click', function() {
        loadTilokData();
    });

    // Card Tilok Click -> Navigate to Detail with Bold Spinner
    $('#loaded').on('click', '.tilok-card-wrapper', function (e) {
        if ($(e.target).closest('button, a').length && !$(e.target).closest('.tws-access-btn').length) return;

        const card = $(this);
        const url = card.data('url');
        if (!url) return;

        const arrowBtn = card.find('.tws-access-btn');
        const originalContent = arrowBtn.html();

        arrowBtn.html('<span class="spinner-border spinner-border-sm cat-spinner-bold" role="status" aria-hidden="true"></span>');

        setTimeout(function () {
            window.location.href = url;
        }, 120);
    });
});
