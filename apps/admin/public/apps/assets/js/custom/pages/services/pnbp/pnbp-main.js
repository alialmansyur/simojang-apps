/**
 * PNBP Main Card List & Filter Controller
 */

function escapeHtml(value) {
    return String(value || '')
        .replace(/&/g, '&amp;')
        .replace(/</g, '&lt;')
        .replace(/>/g, '&gt;')
        .replace(/"/g, '&quot;')
        .replace(/'/g, '&#039;');
}

let allPnbpData = [];
let pnbpState = {
    keyword: '',
    filter: 'all', // all, draft, generated
    doc_type: (typeof CURRENT_DOC_TYPE !== 'undefined' && CURRENT_DOC_TYPE) ? CURRENT_DOC_TYPE : '',
    seleksi_id: '',
    instansi_id: '',
    sort: 'updated_desc',
    currentPage: 1,
    itemsPerPage: 10
};

const DOC_TYPE_NAMES = {
    'sp': 'Surat Perintah (SP)',
    'st': 'Surat Tugas (ST)',
    'nominatif': 'Daftar Nominatif',
    'kwitansi': 'Kwitansi Perjadin',
    'hadir': 'Daftar Hadir Petugas',
    'kwitansi_jamuan': 'Kwitansi Jamuan',
    'surat_jalan': 'Surat Jalan Jamuan',
    'faktur': 'Faktur Jamuan',
    'hadir_jamuan': 'Daftar Hadir Jamuan'
};

const DOC_ICONS = {
    'sp': '<svg viewBox="0 0 24 24" aria-hidden="true" style="width: 24px; height: 24px; stroke: currentColor; fill: none; stroke-width: 2; stroke-linecap: round; stroke-linejoin: round;"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path><polyline points="14 2 14 8 20 8"></polyline><line x1="16" y1="13" x2="8" y2="13"></line><line x1="16" y1="17" x2="8" y2="17"></line><polyline points="10 9 9 9 8 9"></polyline></svg>',
    'st': '<svg viewBox="0 0 24 24" aria-hidden="true" style="width: 24px; height: 24px; stroke: currentColor; fill: none; stroke-width: 2; stroke-linecap: round; stroke-linejoin: round;"><path d="M16 4h2a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2H6a2 2 0 0 1-2-2V6a2 2 0 0 1 2-2h2"></path><rect x="8" y="2" width="8" height="4" rx="1" ry="1"></rect><path d="M9 14l2 2 4-4"></path></svg>',
    'nominatif': '<svg viewBox="0 0 24 24" aria-hidden="true" style="width: 24px; height: 24px; stroke: currentColor; fill: none; stroke-width: 2; stroke-linecap: round; stroke-linejoin: round;"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"></path><circle cx="9" cy="7" r="4"></circle><path d="M23 21v-2a4 4 0 0 0-3-3.87"></path><path d="M16 3.13a4 4 0 0 1 0 7.75"></path></svg>',
    'kwitansi': '<svg viewBox="0 0 24 24" aria-hidden="true" style="width: 24px; height: 24px; stroke: currentColor; fill: none; stroke-width: 2; stroke-linecap: round; stroke-linejoin: round;"><rect x="1" y="4" width="22" height="16" rx="2" ry="2"></rect><line x1="1" y1="10" x2="23" y2="10"></line></svg>',
    'hadir': '<svg viewBox="0 0 24 24" aria-hidden="true" style="width: 24px; height: 24px; stroke: currentColor; fill: none; stroke-width: 2; stroke-linecap: round; stroke-linejoin: round;"><path d="M9 11l3 3L22 4"></path><path d="M21 12v7a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11"></path></svg>',
    'kwitansi_jamuan': '<svg viewBox="0 0 24 24" aria-hidden="true" style="width: 24px; height: 24px; stroke: currentColor; fill: none; stroke-width: 2; stroke-linecap: round; stroke-linejoin: round;"><path d="M18 8h1a4 4 0 0 1 0 8h-1"></path><path d="M2 8h16v9a4 4 0 0 1-4 4H6a4 4 0 0 1-4-4V8z"></path><line x1="6" y1="1" x2="6" y2="4"></line><line x1="10" y1="1" x2="10" y2="4"></line><line x1="14" y1="1" x2="14" y2="4"></line></svg>',
    'surat_jalan': '<svg viewBox="0 0 24 24" aria-hidden="true" style="width: 24px; height: 24px; stroke: currentColor; fill: none; stroke-width: 2; stroke-linecap: round; stroke-linejoin: round;"><rect x="1" y="3" width="15" height="13"></rect><polygon points="16 8 20 8 23 11 23 16 16 16 16 8"></polygon><circle cx="5.5" cy="18.5" r="2.5"></circle><circle cx="18.5" cy="18.5" r="2.5"></circle></svg>',
    'faktur': '<svg viewBox="0 0 24 24" aria-hidden="true" style="width: 24px; height: 24px; stroke: currentColor; fill: none; stroke-width: 2; stroke-linecap: round; stroke-linejoin: round;"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path><polyline points="14 2 14 8 20 8"></polyline><line x1="12" y1="18" x2="12" y2="12"></line><line x1="9" y1="15" x2="15" y2="15"></line></svg>',
    'hadir_jamuan': '<svg viewBox="0 0 24 24" aria-hidden="true" style="width: 24px; height: 24px; stroke: currentColor; fill: none; stroke-width: 2; stroke-linecap: round; stroke-linejoin: round;"><path d="M18 8h1a4 4 0 0 1 0 8h-1"></path><path d="M2 8h16v9a4 4 0 0 1-4 4H6a4 4 0 0 1-4-4V8z"></path><line x1="9" y1="12" x2="9" y2="16"></line></svg>'
};

function updateFilterCounts(list = []) {
    const counts = {
        all: list.length,
        draft: list.filter(item => item.status === 'draft').length,
        generated: list.filter(item => item.status === 'generated' || item.status === 'final').length
    };

    $('.tws-filter-chip').each(function () {
        const filter = String($(this).data('filter') || 'all');
        const count = counts[filter] || 0;
        const originalText = $(this).text().replace(/\s*\(\d+\)$/, '');
        $(this).text(`${originalText} (${count})`);
    });
}

function processAndRenderPnbp() {
    let rendered = [...allPnbpData];

    // Filter by keyword
    if (pnbpState.keyword) {
        rendered = rendered.filter(row => {
            const title = String(row.title || '').toLowerCase();
            const docNum = String(row.doc_number || '').toLowerCase();
            const seleksi = String(row.nama_seleksi || '').toLowerCase();
            const instansi = String(row.instansi_nama || '').toLowerCase();
            const tilok = String(row.nama_tilok || '').toLowerCase();
            const docTypeName = String(DOC_TYPE_NAMES[row.doc_type] || '').toLowerCase();

            return title.includes(pnbpState.keyword) ||
                   docNum.includes(pnbpState.keyword) ||
                   seleksi.includes(pnbpState.keyword) ||
                   instansi.includes(pnbpState.keyword) ||
                   tilok.includes(pnbpState.keyword) ||
                   docTypeName.includes(pnbpState.keyword);
        });
    }

    // Filter by Doc Type
    if (pnbpState.doc_type) {
        rendered = rendered.filter(row => row.doc_type === pnbpState.doc_type);
    }

    // Filter by Seleksi ID
    if (pnbpState.seleksi_id) {
        rendered = rendered.filter(row => String(row.seleksi_id) === String(pnbpState.seleksi_id));
    }

    // Filter by Instansi ID
    if (pnbpState.instansi_id) {
        rendered = rendered.filter(row => String(row.instansi_id) === String(pnbpState.instansi_id));
    }

    // Update Counts based on current search & dropdown
    updateFilterCounts(rendered);

    // Filter by Status Chip
    rendered = rendered.filter(row => {
        if (pnbpState.filter === 'draft') return row.status === 'draft';
        if (pnbpState.filter === 'generated') return row.status === 'generated' || row.status === 'final';
        return true;
    });

    // Sorting
    rendered.sort((a, b) => {
        if (pnbpState.sort === 'updated_desc') {
            const tA = a.updated_at ? Date.parse(a.updated_at) || 0 : (Date.parse(a.created_at) || 0);
            const tB = b.updated_at ? Date.parse(b.updated_at) || 0 : (Date.parse(b.created_at) || 0);
            return tB - tA;
        }
        if (pnbpState.sort === 'date_desc') {
            const dA = a.doc_date ? Date.parse(a.doc_date) || 0 : 0;
            const dB = b.doc_date ? Date.parse(b.doc_date) || 0 : 0;
            return dB - dA;
        }
        if (pnbpState.sort === 'title_asc') {
            const nameA = String(a.title || '').toLowerCase();
            const nameB = String(b.title || '').toLowerCase();
            return nameA.localeCompare(nameB, 'id');
        }
        return 0;
    });

    const totalItems = rendered.length;
    const startIndex = (pnbpState.currentPage - 1) * pnbpState.itemsPerPage;
    const paginated = rendered.slice(startIndex, startIndex + pnbpState.itemsPerPage);

    renderPnbpCards(paginated);
    renderPagination(totalItems);
}

function renderPagination(totalItems) {
    const wrap = $('#pnbpPaginationWrap');
    wrap.empty();

    if (totalItems <= pnbpState.itemsPerPage) {
        return;
    }

    const totalPages = Math.ceil(totalItems / pnbpState.itemsPerPage);
    let html = '<ul class="pagination pagination-sm m-0">';

    const prevDisabled = pnbpState.currentPage === 1 ? 'disabled' : '';
    html += `
        <li class="page-item ${prevDisabled}">
            <a class="page-link tws-page-link" href="javascript:void(0)" data-page="${pnbpState.currentPage - 1}">
                <i class="bi bi-chevron-left"></i>
            </a>
        </li>
    `;

    for (let i = 1; i <= totalPages; i++) {
        if (i === 1 || i === totalPages || (i >= pnbpState.currentPage - 1 && i <= pnbpState.currentPage + 1)) {
            const activeClass = i === pnbpState.currentPage ? 'active' : '';
            html += `<li class="page-item ${activeClass}"><a class="page-link tws-page-link" href="javascript:void(0)" data-page="${i}">${i}</a></li>`;
        } else if (i === pnbpState.currentPage - 2 || i === pnbpState.currentPage + 2) {
            html += `<li class="page-item disabled"><span class="page-link">...</span></li>`;
        }
    }

    const nextDisabled = pnbpState.currentPage === totalPages ? 'disabled' : '';
    html += `
        <li class="page-item ${nextDisabled}">
            <a class="page-link tws-page-link" href="javascript:void(0)" data-page="${pnbpState.currentPage + 1}">
                <i class="bi bi-chevron-right"></i>
            </a>
        </li>
    `;

    html += '</ul>';
    wrap.html(html);
}

function renderPnbpCards(data) {
    const container = $('#loaded');
    container.empty();

    if (!data || data.length === 0) {
        container.html(`
            <div class="col-12 text-center py-5">
                <img src="${AppConfig.initGlobal}apps/assets/images/empty-content-profile.png" alt="Kosong" style="max-width: 260px; margin-bottom: 1.5rem;">
                <h5 class="fw-bold text-dark mb-2">Belum Ada Dokumen</h5>
                <p class="text-muted mb-0" style="font-size: 0.95rem; line-height: 1.6;">
                    Tidak ada dokumen yang cocok dengan filter atau pencarian Anda.<br>
                    Klik tombol <strong>"Tambah +"</strong> untuk membuat berkas baru.
                </p>
            </div>
        `);
        return;
    }

    let html = '';
    data.forEach((row, index) => {
        const docTypeName = DOC_TYPE_NAMES[row.doc_type] || row.doc_type;
        const iconSvg = DOC_ICONS[row.doc_type] || DOC_ICONS['sp'];
        const isGenerated = row.status === 'generated' || row.status === 'final';
        const statusBadge = isGenerated 
            ? '<span class="badge bg-success-subtle text-success border border-success-subtle px-2 py-1"><i class="bi bi-check-circle me-1"></i>Generated</span>'
            : '<span class="badge bg-warning-subtle text-warning border border-warning-subtle px-2 py-1"><i class="bi bi-pencil me-1"></i>Draft</span>';

        const detailUrl = `${AppConfig.initGlobal}apps-pnbp/detail/${row.uid}`;
        const totalItemsCount = row.total_personel > 0 ? `${row.total_personel} Personel` : (row.total_items > 0 ? `${row.total_items} Item` : '0 Item');
        const signStatus = row.total_signatures > 0 ? `${row.total_signed}/${row.total_signatures} TTD` : 'TTD Siap';
        const toneIndex = (index % 4) + 1;

        html += `
        <div class="col-12 tws-col-list tw-animate-entry mb-2" style="--animation-order: ${index};">
            <div class="card h-100 p-2 rounded-3 border tws-service-card tws-card-soft tws-anim-card overflow-hidden position-relative tws-tone-${toneIndex}" style="cursor: pointer;" data-url="${detailUrl}">
                <div class="position-absolute tws-bg-icon-wrapper" style="opacity: 0.05;">
                    <div class="tws-bg-icon-svg">
                        ${iconSvg}
                    </div>
                </div>
                <div class="card-body p-0 d-flex flex-column flex-md-row align-items-md-center justify-content-between" style="position: relative; z-index: 1;">
                    
                    <div class="d-flex align-items-center gap-3">
                        <div class="flex-shrink-0 d-flex align-items-center justify-content-center bg-primary-subtle text-primary" style="width: 48px; height: 48px; border-radius: 12px; transform: none !important;">
                            ${iconSvg}
                        </div>
                        <div class="text-start">
                            <h6 class="fw-bold mb-1" style="font-size: 1.05rem; color: #1e293b;">${escapeHtml(row.title || 'Dokumen Tanpa Judul')}</h6>
                            <div class="d-flex flex-wrap gap-2 align-items-center mt-1">
                                <span class="text-primary fw-semibold" style="font-size: 0.8rem;">No: ${escapeHtml(row.doc_number || '-')}</span>
                                <span class="text-success fw-semibold" style="font-size: 0.8rem;">${escapeHtml(row.nama_seleksi || 'Non-Event')}</span>
                                ${row.instansi_nama ? `<span class="text-danger fw-semibold" style="font-size: 0.8rem;">${escapeHtml(row.instansi_nama)}</span>` : ''}
                                <span class="text-secondary fw-semibold" style="font-size: 0.8rem;">${escapeHtml(row.nama_tilok || '-')} &bull; ${escapeHtml(row.doc_date || '-')}</span>
                                ${statusBadge}
                                <span class="badge bg-light text-primary border" style="font-size: 0.72rem;">${signStatus}</span>
                                <span class="badge bg-light text-dark border" style="font-size: 0.72rem;">${totalItemsCount}</span>
                            </div>
                        </div>
                    </div>

                    <div class="d-flex align-items-center gap-2 mt-3 mt-md-0 px-2 px-md-0 h-100">
                        <button type="button" class="btn btn-sm btn-outline-primary btn-quick-generate px-2 py-1" data-uid="${row.uid}" title="Generate PDF">
                            <i class="bi bi-file-earmark-pdf"></i> PDF
                        </button>
                        <button type="button" class="btn p-1 border-0 btn-remove-doc text-danger" data-uid="${row.uid}" title="Hapus Dokumen" style="color: #ef4444;">
                            <i class="bi bi-trash fs-5"></i>
                        </button>
                        <button type="button" class="btn btn-primary p-0 ms-2 d-flex align-items-center justify-content-center text-white shadow-sm tws-access-btn" title="Detail Dokumen" style="width: 32px; height: 32px; border-radius: 50% !important; min-width: 32px;">
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

function loadPnbpData() {
    $('#loaded').html('<div class="col-12 text-center text-muted py-5"><span class="spinner-border spinner-border-sm me-2" role="status"></span> Memuat data dokumen...</div>');

    const postData = { draw: 1, length: 1000, start: 0 };
    if (typeof CURRENT_DOC_TYPE !== 'undefined' && CURRENT_DOC_TYPE) {
        postData.doc_type = CURRENT_DOC_TYPE;
    }

    $.ajax({
        url: AppConfig.initGlobal + 'fetch/data-pnbp-documents',
        type: 'POST',
        data: postData,
        dataType: 'json',
        success: function(res) {
            if (res && res.data) {
                allPnbpData = res.data;
                processAndRenderPnbp();
            }
        },
        error: function() {
            $('#loaded').html('<div class="col-12 text-center text-danger py-5">Gagal memuat data dokumen.</div>');
        }
    });
}

function resetPnbpModalForm() {
    const form = $('#pnbpDocForm');
    if (!form.length) return;

    form[0].reset();
    $('#doc_key').val('');

    const targetType = (typeof CURRENT_DOC_TYPE !== 'undefined' && CURRENT_DOC_TYPE) ? CURRENT_DOC_TYPE : 'nominatif';
    $('#doc_type').val(targetType).trigger('change');
    $('#doc_instansi_id').val('').trigger('change');
    $('#doc_seleksi_id').val('').trigger('change');
    $('#doc_tilok_id').html('<option value="">-- Pilih Event Dulu / Opsional --</option>').val('').trigger('change');

    $('#doc_mak').val('030.01.WA.6253.EAA.001.051.A.524111');
    $('#pnbpDocModalLabel').html('<i class="bi bi-file-earmark-plus-fill text-primary me-2"></i> Buat Dokumen Baru');
    $('.jamuan-fields').addClass('d-none');
}

function initPnbpDocModalSelect2() {
    const modal = $('#pnbpDocModal');
    if (!modal.length) return;

    ['#doc_type', '#doc_instansi_id', '#doc_seleksi_id', '#doc_tilok_id'].forEach(function(sel) {
        const el = $(sel);
        if (el.length && !el.data('select2')) {
            el.select2({
                theme: 'bootstrap-5',
                dropdownParent: modal,
                width: '100%',
                placeholder: el.find('option:first').text() || '-- Pilih --',
                allowClear: sel !== '#doc_type'
            });
        }
    });
}

function fillQuickSampleDoc() {
    $('#doc_type').val('nominatif').trigger('change');
    
    // Select first available instansi if none selected
    const instansiSelect = $('#doc_instansi_id');
    const firstOption = instansiSelect.find('option').filter(function() { return $(this).val() !== ''; }).first();
    const instansiVal = firstOption.val() || '4018';
    const instansiNama = firstOption.data('nama') || firstOption.text().trim() || 'Arsip Nasional Republik Indonesia';
    
    if (!instansiSelect.find(`option[value="${instansiVal}"]`).length) {
        const newOption = new Option(instansiNama, instansiVal, true, true);
        instansiSelect.append(newOption);
    }
    instansiSelect.val(instansiVal).trigger('change');
    
    const now = new Date();
    const ymd = now.getFullYear().toString() + String(now.getMonth() + 1).padStart(2, '0') + String(now.getDate()).padStart(2, '0');
    const formattedDate = now.toISOString().slice(0, 10);
    
    $('#doc_date').val(formattedDate);
    $('#doc_number').val('NOM/' + ymd + '/001');
    $('#doc_mak').val('030.01.WA.6253.EAA.001.051.A.524111');
    $('#doc_title').val('Fasilitasi Seleksi Pengembangan Karier dengan metode CAT BKN di Lingkungan Instansi ' + instansiNama);
    $('#doc_notes').val('Dokumen Daftar Nominatif Honorarium Fasilitasi CAT.');
    
    if (typeof toastr !== 'undefined' && toastr.info) {
        toastr.info('Data contoh nominatif berhasil dimuat.');
    }
}

function loadTilokBySeleksi(seleksiId, selectedTilokId) {
    const tilokSelect = $('#doc_tilok_id');
    if (!seleksiId) {
        tilokSelect.html('<option value="">-- Tanpa Event / Mandiri --</option>');
        if (tilokSelect.hasClass('select2-hidden-accessible')) {
            tilokSelect.val('').trigger('change.select2');
        }
        return;
    }

    tilokSelect.html('<option value="">Memuat titik lokasi...</option>');
    if (tilokSelect.hasClass('select2-hidden-accessible')) {
        tilokSelect.val('').trigger('change.select2');
    }

    $.ajax({
        url: AppConfig.initGlobal + 'fetch/pnbp-options-tilok',
        type: 'POST',
        data: { seleksi_id: seleksiId },
        dataType: 'json',
        success: function(res) {
            tilokSelect.empty();
            tilokSelect.append('<option value="">-- Pilih Titik Lokasi --</option>');
            if (res && res.data && res.data.length) {
                res.data.forEach(t => {
                    const selected = selectedTilokId && String(selectedTilokId) === String(t.id) ? 'selected' : '';
                    tilokSelect.append(`<option value="${t.id}" ${selected}>${escapeHtml(t.nama_tilok)}</option>`);
                });
            } else {
                tilokSelect.append('<option value="">(Belum ada tilok di event ini)</option>');
            }
            if (tilokSelect.hasClass('select2-hidden-accessible')) {
                tilokSelect.trigger('change.select2');
            }
        }
    });
}

$(document).ready(function() {
    loadPnbpData();

    // Search Input
    $('#searchdata').on('input', function() {
        pnbpState.keyword = $(this).val().toLowerCase();
        pnbpState.currentPage = 1;

        if (pnbpState.keyword.length > 0) {
            $('#twsClearSearch').removeClass('d-none');
        } else {
            $('#twsClearSearch').addClass('d-none');
        }
        processAndRenderPnbp();
    });

    $('#twsClearSearch').on('click', function() {
        $('#searchdata').val('').trigger('input').focus();
    });

    // Filter Chips
    $('.tws-filter-chip').on('click', function() {
        pnbpState.filter = String($(this).data('filter') || 'all');
        pnbpState.currentPage = 1;
        $('.tws-filter-chip').removeClass('is-active');
        $(this).addClass('is-active');
        processAndRenderPnbp();
    });

    // Dropdown Filters
    $('#filterDocType').on('change', function() {
        pnbpState.doc_type = $(this).val();
        pnbpState.currentPage = 1;
        processAndRenderPnbp();
    });

    $('#filterSeleksi').on('change', function() {
        pnbpState.seleksi_id = $(this).val();
        pnbpState.currentPage = 1;
        processAndRenderPnbp();
    });

    $('#filterInstansi').on('change', function() {
        pnbpState.instansi_id = $(this).val();
        pnbpState.currentPage = 1;
        processAndRenderPnbp();
    });

    $('#twsSort').on('change', function() {
        pnbpState.sort = $(this).val();
        pnbpState.currentPage = 1;
        processAndRenderPnbp();
    });

    // Pagination Click
    $('#pnbpPaginationWrap').on('click', '.tws-page-link', function(e) {
        e.preventDefault();
        const p = parseInt($(this).data('page'));
        if (!isNaN(p)) {
            pnbpState.currentPage = p;
            processAndRenderPnbp();
            $('html, body').animate({ scrollTop: $('.tws-search-wrap').offset().top - 80 }, 300);
        }
    });

    // Card Click -> Go to detail with spinner on arrow
    $(document).on('click', '.tws-service-card', function(e) {
        if ($(e.target).closest('button:not(.tws-access-btn), a:not(.tws-access-btn), .btn-remove-doc, .btn-quick-generate').length) {
            return;
        }
        if ($(this).hasClass('is-disabled')) {
            e.preventDefault();
            return false;
        }
        const url = $(this).data('url');
        if (url) {
            const btn = $(this).find('.tws-access-btn');
            if (btn.length) {
                btn.prop('disabled', true);
                btn.addClass('is-loading');
                btn.html('<span class="spinner-border spinner-border-sm text-white" style="width: 1.15rem; height: 1.15rem; border-width: 3px;" role="status" aria-hidden="true"></span>');
            }
            window.location.href = url;
        }
    });

    // Arrow Button Explicit Click
    $(document).on('click', '.tws-access-btn', function(e) {
        e.preventDefault();
        e.stopPropagation();
        const card = $(this).closest('.tws-service-card');
        if (card.hasClass('is-disabled') || $(this).hasClass('disabled')) {
            return false;
        }
        const url = card.data('url') || $(this).attr('href');
        if (url && url !== '#' && !url.startsWith('javascript')) {
            $(this).prop('disabled', true);
            $(this).addClass('is-loading');
            $(this).html('<span class="spinner-border spinner-border-sm text-white" style="width: 1.15rem; height: 1.15rem; border-width: 3px;" role="status" aria-hidden="true"></span>');
            window.location.href = url;
        }
    });

    // Modal Create / Edit Lifecycle
    $('#btnOpenCreateModal').on('click', function() {
        resetPnbpModalForm();
    });

    $('#doc_type').on('change', function() {
        const val = $(this).val();
        const isJamuan = ['kwitansi_jamuan', 'surat_jalan', 'faktur', 'hadir_jamuan'].includes(val);
        if (isJamuan) {
            $('.jamuan-fields').removeClass('d-none');
        } else {
            $('.jamuan-fields').addClass('d-none');
        }
    });

    $('#doc_seleksi_id').on('change', function() {
        loadTilokBySeleksi($(this).val());
    });

    $('#btnQuickSampleDoc').on('click', function() {
        fillQuickSampleDoc();
    });

    $('#doc_instansi_id').on('change', function() {
        const selectedOpt = $(this).find('option:selected');
        const instansiNama = selectedOpt.data('nama') || selectedOpt.text().trim();
        const curTitle = $('#doc_title').val();
        if (instansiNama && (!curTitle || curTitle.startsWith('Fasilitasi Seleksi Pengembangan Karier') || curTitle.includes('di Lingkungan Instansi'))) {
            $('#doc_title').val('Fasilitasi Seleksi Pengembangan Karier dengan metode CAT BKN di Lingkungan Instansi ' + instansiNama);
        }
    });

    $('#pnbpDocModal').on('show.bs.modal', function() {
        initPnbpDocModalSelect2();
        if (!$('#doc_key').val()) {
            const targetType = (typeof CURRENT_DOC_TYPE !== 'undefined' && CURRENT_DOC_TYPE) ? CURRENT_DOC_TYPE : 'nominatif';
            $('#doc_type').val(targetType).trigger('change');
        }
    });

    $('#pnbpDocModal').on('hidden.bs.modal', function() {
        resetPnbpModalForm();
    });

    // Save Document Form Submit
    $('#btnSaveDocument').on('click', function() {
        $('#pnbpDocForm').submit();
    });

    $('#pnbpDocForm').on('submit', function(e) {
        e.preventDefault();
        $('#pnbpDocModal').modal('hide');
        
        if (typeof swlwaitProsessing === 'function') swlwaitProsessing('Menyimpan data dokumen...');

        $.ajax({
            url: AppConfig.initGlobal + 'store/save-pnbp-document',
            type: 'POST',
            data: $(this).serialize(),
            dataType: 'json',
            success: function(res) {
                if (res && res.status === 'success') {
                    if (typeof swlSuccess === 'function') swlSuccess(res.message || 'Dokumen berhasil disimpan.');
                    loadPnbpData();
                    if (res.uid && !$('#doc_key').val()) {
                        window.location.href = AppConfig.initGlobal + 'apps-pnbp/detail/' + res.uid;
                    }
                } else {
                    if (typeof swlErrorHandler === 'function') swlErrorHandler(res && res.message ? res.message : 'Gagal menyimpan.');
                }
            },
            error: function(xhr) {
                const msg = xhr.responseJSON && xhr.responseJSON.message ? xhr.responseJSON.message : 'Terjadi kesalahan saat menyimpan dokumen.';
                if (typeof swlErrorHandler === 'function') swlErrorHandler(msg);
            }
        });
    });

    // Quick Generate PDF from Card
    $(document).on('click', '.btn-quick-generate', function(e) {
        e.stopPropagation();
        const uid = $(this).data('uid');
        if (typeof generateAndPreviewPdf === 'function') {
            generateAndPreviewPdf(uid, function() {
                loadPnbpData();
            });
        }
    });

    // Delete Document
    $(document).on('click', '.btn-remove-doc', function(e) {
        e.stopPropagation();
        const uid = $(this).data('uid');

        Swal.fire({
            text: 'Apakah Anda yakin ingin menghapus dokumen ini beserta seluruh rincian personel/jamuan?',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#ef4444',
            confirmButtonText: 'Ya, Hapus',
            cancelButtonText: 'Batal'
        }).then((result) => {
            if (!result.isConfirmed) return;

            if (typeof swlwaitProsessing === 'function') swlwaitProsessing('Menghapus dokumen...');

            $.ajax({
                url: AppConfig.initGlobal + 'kill/data-pnbp-document',
                type: 'POST',
                data: { key: uid },
                dataType: 'json',
                success: function(res) {
                    if (res && res.status) {
                        if (typeof swlSuccess === 'function') swlSuccess('Dokumen berhasil dihapus.');
                        loadPnbpData();
                    } else {
                        if (typeof swlErrorHandler === 'function') swlErrorHandler(res && res.message ? res.message : 'Gagal menghapus.');
                    }
                },
                error: function(xhr) {
                    const msg = xhr.responseJSON && xhr.responseJSON.message ? xhr.responseJSON.message : 'Gagal menghapus dokumen.';
                    if (typeof swlErrorHandler === 'function') swlErrorHandler(msg);
                }
            });
        });
    });
});
