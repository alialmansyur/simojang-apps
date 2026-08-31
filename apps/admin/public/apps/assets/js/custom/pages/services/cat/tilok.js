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
    const form = $('#form-usulan');
    if (!form.length || !form[0]) return;

    form[0].reset();
    form.find('[name="key"]').val('');
    form.find('[name="action"]').val('create');
    $('#DataModalLabel').text('Tambah Data');
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
        $('#form-usulan').submit();
    });

    $('#form-usulan').on('submit', function (e) {
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

let allTilokData = [];
let tilokState = {
    keyword: '',
    filter: 'all',
    sort: 'name_asc',
    currentPage: 1,
    itemsPerPage: 10
};

function updateFilterCounts(list = []) {
    const counts = {
        all: list.length,
        updated: list.filter(item => Boolean(item.updated_at)).length,
        pending: list.filter(item => !item.updated_at).length
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
        if (tilokState.filter === 'updated') return Boolean(row.updated_at);
        if (tilokState.filter === 'pending') return !row.updated_at;
        return true;
    });

    // Sorting
    rendered.sort((a, b) => {
        if (tilokState.sort === 'updated_desc') {
            const tA = a.updated_at ? Date.parse(a.updated_at) || 0 : 0;
            const tB = b.updated_at ? Date.parse(b.updated_at) || 0 : 0;
            return tB - tA;
        }
        if (tilokState.sort === 'pending_first') {
            const hasA = Boolean(a.updated_at);
            const hasB = Boolean(b.updated_at);
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
            seleksi_uid: typeof SELEKSI_UID !== 'undefined' ? SELEKSI_UID : '',
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
                    <img src="${AppConfig.initGlobal}apps/assets/images/empty-content-profile.png" alt="Tidak Ditemukan" style="max-width: 320px; margin-bottom: 2rem;">
                    <h5 class="fw-bold" style="color: #1a202c; font-size: 1.35rem;">Pencarian Tidak Ditemukan</h5>
                    <p class="text-muted mb-0" style="font-size: 1.05rem; max-width: 450px; margin: 0 auto; line-height: 1.6;">Tidak ada data seleksi yang cocok dengan pencarian Anda.</p>
                </div>
            </div>
        `);
        return;
    }

    let html = '';
    data.forEach((row, index) => {
        const toneClass = 'twx-tone-' + ((index % 5) + 1);
        const start = row.startdate || '-';
        const end = row.enddate || '-';
        const cap = row.capacity || '0';
        const nama = escapeHtml(row.tilok || row.nama_tilok || row.nama || '-');
        const isUpdated = Boolean(row.updated_at);
        const ringClass = isUpdated ? 'tws-ring-high' : 'tws-ring-low';

        html += `
        <div class="col-12 tws-col-list tw-animate-entry mb-2" style="--animation-order: ${index};">
            <div class="card h-100 p-2 rounded-3 border tws-service-card tws-card-soft tws-anim-card overflow-hidden position-relative tws-tone-${(index % 4) + 1}" style="cursor: pointer;" data-url="${AppConfig.initGlobal}apps-cat-detail/${row.uid}">
                <div class="position-absolute tws-bg-icon-wrapper" style="opacity: 0.05;">
                    <div class="tws-bg-icon-svg">
                        <svg viewBox="0 0 24 24" aria-hidden="true" stroke="currentColor" fill="none" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"></path><circle cx="12" cy="10" r="3"></circle></svg>
                    </div>
                </div>
                <div class="card-body p-0 d-flex flex-column flex-md-row align-items-md-center justify-content-between" style="position: relative; z-index: 1;">
                    <div class="d-flex align-items-center gap-3">
                        <div class="flex-shrink-0 d-flex align-items-center justify-content-center bg-primary-subtle text-primary" style="width: 48px; height: 48px; border-radius: 12px; transform: none !important;">
                            <svg viewBox="0 0 24 24" aria-hidden="true" style="width: 24px; height: 24px; stroke: currentColor; fill: none; stroke-width: 2; stroke-linecap: round; stroke-linejoin: round;"><path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"></path><circle cx="12" cy="10" r="3"></circle></svg>
                        </div>
                        <div class="text-start">
                            <h6 class="fw-bold mb-1" style="font-size: 1.05rem; color: #1e293b;">${nama}</h6>
                            <div class="d-flex flex-wrap gap-2 align-items-center mt-1">
                                <span class="text-primary fw-semibold" style="font-size: 0.8rem;">Total Rekap: ${row.total_rekap || 0}</span>
                                <span class="text-success fw-semibold" style="font-size: 0.8rem;">Kapasitas: ${cap} PC</span>
                                <span class="text-danger fw-semibold" style="font-size: 0.8rem;">${escapeHtml(row.jenis_tes || '-')}</span>
                                <span class="text-secondary fw-semibold" style="font-size: 0.8rem;">${escapeHtml(row.period || (start + ' s/d ' + end))}</span>
                            </div>
                        </div>
                    </div>
                    <div class="d-flex align-items-center gap-3 mt-3 mt-md-0 px-2 px-md-0 h-100">
                        <button type="button" class="btn p-1 border-0 btn-update" data-id="${row.id}" title="Edit Data" style="color: #64748b;">
                            <i class="bi bi-pencil-square fs-5"></i>
                        </button>
                        <button type="button" class="btn p-1 border-0 btn-remove" data-id="${row.id}" title="Hapus Data" style="color: #ef4444;">
                            <i class="bi bi-trash fs-5"></i>
                        </button>
                        <button type="button" class="btn btn-primary p-0 ms-2 d-flex align-items-center justify-content-center text-white shadow-sm tws-access-btn" title="Detail" style="width: 32px; height: 32px; border-radius: 50% !important; min-width: 32px;">
                            <i class="bi bi-arrow-right d-flex align-items-center justify-content-center" style="font-size: 1.15rem; line-height: 0;"></i>
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

        const form = $('#form-usulan');
        isCatTilokEditMode = true;

        form.find('[name="key"]').val(row.id || '');
        form.find('[name="action"]').val('update');
        form.find('[name="tilok"]').val(row.nama_tilok || row.tilok || '');
        form.find('[name="startdate"]').val(row.period_start_date || '');
        form.find('[name="enddate"]').val(row.period_end_date || '');
        form.find('[name="capacity"]').val(row.kapasitas || '');
        
        const catId = row.jenis_tes_id;
        if(typeof window.setCatJenisPickerValue === 'function') {
             window.setCatJenisPickerValue(catId, row.jenis_tes);
        }

        $('#DataModalLabel').text('Edit Data');
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
});
