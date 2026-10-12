$(document).ready(function () {
    'use strict';

    // -------------------------------------------------------------------------
    // 1. HELPER & OPTIONS LOADER
    // -------------------------------------------------------------------------
    function initSelect2Modal(modalId, selectId, placeholderText = '-- Pilih Tim Kerja --') {
        if ($.fn.select2) {
            $(selectId).select2({
                dropdownParent: $(modalId),
                width: '100%',
                placeholder: placeholderText
            });
        }
    }

    function loadOptionsTahun(callback = null) {
        $.post(AppConfig.initGlobal + 'fetch/ikk-options-tahun', function (res) {
            let currentYear = new Date().getFullYear();
            let years = Array.isArray(res) && res.length > 0 ? res : [currentYear];
            if (!years.includes(currentYear)) {
                years.unshift(currentYear);
            }
            let htmlGlobal = '<option value="">Semua Tahun</option>';
            let htmlTrx = '<option value="">-- Semua Tahun --</option>';
            let htmlModalInd = '';

            years.forEach(y => {
                let sel = (y == currentYear) ? 'selected' : '';
                htmlGlobal += `<option value="${y}" ${sel}>Tahun ${y}</option>`;
                htmlTrx += `<option value="${y}">Tahun ${y}</option>`;
                htmlModalInd += `<option value="${y}" ${sel}>Tahun ${y}</option>`;
            });

            $('#filter-global-tahun').html(htmlGlobal);
            $('#filter-indikator-tahun').html(htmlTrx);
            $('#filter-transaksi-tahun').html(htmlTrx);
            $('#realisasi_ind_tahun').html(htmlModalInd);

            if (callback) callback();
        });
    }

    function getGlobalPeriodFilter() {
        return {
            tahun: $('#filter-global-tahun').val() || '',
            triwulan: $('#filter-global-triwulan').val() || ''
        };
    }

    function loadSummary() {
        let filterData = getGlobalPeriodFilter();
        $.post(AppConfig.initGlobal + 'fetch/ikk-summary', filterData, function (res) {
            if (res.status === 'success' && res.data) {
                let d = res.data;
                $('.js-kpi-sasaran').text(d.total_sasaran);
                $('.js-kpi-indikator').text(d.total_indikator);
                $('.js-kpi-kegiatan').text(d.total_kegiatan);
                $('.js-kpi-transaksi').text(d.total_transaksi);
                $('.js-kpi-progres').text(d.avg_progres + '%');

                $('.js-badge-count-sasaran').text(d.total_sasaran);
                $('.js-badge-count-indikator').text(d.total_indikator);
                $('.js-badge-count-kegiatan').text(d.total_kegiatan);
                $('.js-badge-count-transaksi').text(d.total_transaksi);

                if (d.tanpa_pengampu > 0) {
                    $('.js-kpi-tanpa-pengampu').text(d.tanpa_pengampu + ' belum ada pengampu').show();
                    $('.js-kpi-kegiatan-sub').hide();
                } else {
                    $('.js-kpi-tanpa-pengampu').hide();
                    $('.js-kpi-kegiatan-sub').show();
                }
            }
        });
    }

    function loadOptionsSasaran(targetSelect, selectedVal = null, callback = null) {
        $.post(AppConfig.initGlobal + 'fetch/ikk-options-sasaran', function (res) {
            let html = '<option value="">-- Semua Sasaran --</option>';
            if (targetSelect === '#indikator_sasaran_id') {
                html = '<option value="">-- Pilih Sasaran Strategis --</option>';
            }
            if (Array.isArray(res)) {
                res.forEach(item => {
                    let sel = (selectedVal && selectedVal == item.id) ? 'selected' : '';
                    html += `<option value="${item.id}" ${sel}>${item.text}</option>`;
                });
            }
            $(targetSelect).html(html);
            if (callback) callback();
        });
    }

    function loadOptionsIndikator(targetSelect, sasaranId = null, selectedVal = null, callback = null) {
        $.post(AppConfig.initGlobal + 'fetch/ikk-options-indikator', { sasaran_id: sasaranId }, function (res) {
            let html = '<option value="">-- Semua Indikator --</option>';
            if (targetSelect === '#kegiatan_indikator_id') {
                html = '<option value="">-- Pilih Indikator Kinerja --</option>';
            }
            if (Array.isArray(res)) {
                res.forEach(item => {
                    let sel = (selectedVal && selectedVal == item.id) ? 'selected' : '';
                    html += `<option value="${item.id}" ${sel}>${item.text}</option>`;
                });
            }
            $(targetSelect).html(html);
            if (callback) callback();
        });
    }

    function loadOptionsKegiatan(targetSelect, indikatorId = null, selectedVal = null, callback = null) {
        $.post(AppConfig.initGlobal + 'fetch/ikk-options-kegiatan', { indikator_id: indikatorId }, function (res) {
            let html = '<option value="">-- Semua Kegiatan --</option>';
            if (targetSelect === '#transaksi_kegiatan_id') {
                html = '<option value="">-- Pilih Kegiatan Operasional --</option>';
            }
            if (Array.isArray(res)) {
                res.forEach(item => {
                    let sel = (selectedVal && selectedVal == item.id) ? 'selected' : '';
                    let warning = (item.has_pengampu == 0) ? ' [BELUM ADA PENGAMPU]' : '';
                    html += `<option value="${item.id}" data-has-pengampu="${item.has_pengampu}" ${sel}>${item.text}${warning}</option>`;
                });
            }
            $(targetSelect).html(html);
            if (callback) callback();
        });
    }

    function loadOptionsTimKerja(targetSelect, selectedVals = [], callback = null) {
        $.post(AppConfig.initGlobal + 'fetch/ikk-options-timkerja', function (res) {
            let isMultiple = $(targetSelect).prop('multiple');
            let html = isMultiple ? '' : '<option value="">-- Semua Tim Kerja --</option>';
            if (Array.isArray(res)) {
                res.forEach(item => {
                    let isSel = Array.isArray(selectedVals) 
                        ? selectedVals.map(v => parseInt(v)).includes(parseInt(item.id)) 
                        : (selectedVals == item.id);
                    let sel = isSel ? 'selected' : '';
                    html += `<option value="${item.id}" ${sel}>${item.text}</option>`;
                });
            }
            $(targetSelect).html(html);
            if (callback) callback();
        });
    }

    // Inisialisasi awal summary & dropdown filter
    loadOptionsTahun(function () {
        loadSummary();
    });
    loadOptionsSasaran('#filter-indikator-sasaran');
    loadOptionsSasaran('#filter-kegiatan-sasaran');
    loadOptionsIndikator('#filter-kegiatan-indikator');
    loadOptionsTimKerja('#filter-kegiatan-timkerja');
    loadOptionsTimKerja('#filter-transaksi-timkerja');
    loadOptionsKegiatan('#filter-transaksi-kegiatan');

    // Filter Periode Global
    $('#filter-global-tahun, #filter-global-triwulan').on('change', function () {
        let th = $('#filter-global-tahun').val();
        let tw = $('#filter-global-triwulan').val();

        // Sync ke tab indikator & transaksi
        $('#filter-indikator-tahun').val(th);
        $('#filter-indikator-triwulan').val(tw);
        $('#filter-transaksi-tahun').val(th);
        $('#filter-transaksi-triwulan').val(tw);

        loadSummary();
        dtIndikator.ajax.reload(null, false);
        dtTransaksi.ajax.reload(null, false);
        if ($('#pane-tree').hasClass('active') || $('#pane-tree').hasClass('show')) {
            loadHierarchyTree();
        }
    });

    $('#btn-reset-global-periode').on('click', function () {
        let curYear = new Date().getFullYear();
        $('#filter-global-tahun').val(curYear);
        $('#filter-global-triwulan').val('');
        $('#filter-indikator-tahun').val('');
        $('#filter-indikator-triwulan').val('');
        $('#filter-transaksi-tahun').val('');
        $('#filter-transaksi-triwulan').val('');
        loadSummary();
        dtIndikator.ajax.reload(null, false);
        dtTransaksi.ajax.reload(null, false);
        if ($('#pane-tree').hasClass('active') || $('#pane-tree').hasClass('show')) {
            loadHierarchyTree();
        }
    });

    // Cascading filter kegiatan
    $('#filter-kegiatan-sasaran').on('change', function () {
        let sasId = $(this).val();
        loadOptionsIndikator('#filter-kegiatan-indikator', sasId, null, function () {
            dtKegiatan.ajax.reload(null, false);
        });
    });

    $('#filter-kegiatan-indikator, #filter-kegiatan-timkerja').on('change', function () {
        dtKegiatan.ajax.reload(null, false);
    });

    $('#filter-indikator-sasaran, #filter-indikator-tahun, #filter-indikator-triwulan').on('change', function () {
        dtIndikator.ajax.reload(null, false);
    });

    $('#filter-transaksi-timkerja, #filter-transaksi-kegiatan, #filter-transaksi-tahun, #filter-transaksi-triwulan').on('change', function () {
        dtTransaksi.ajax.reload(null, false);
    });

    $('#btn-reset-filter-indikator').on('click', function () {
        $('#filter-indikator-sasaran').val('');
        $('#filter-indikator-tahun').val('');
        $('#filter-indikator-triwulan').val('');
        dtIndikator.ajax.reload(null, false);
    });

    $('#btn-reset-filter-transaksi').on('click', function () {
        $('#filter-transaksi-timkerja').val('');
        $('#filter-transaksi-kegiatan').val('');
        $('#filter-transaksi-tahun').val('');
        $('#filter-transaksi-triwulan').val('');
        dtTransaksi.ajax.reload(null, false);
    });

    // -------------------------------------------------------------------------
    // 2. TAB PERSISTENCE & COLUMN AUTO ADJUST
    // -------------------------------------------------------------------------
    $('button[data-bs-toggle="tab"]').on('shown.bs.tab', function (e) {
        let targetPane = $(e.target).attr('data-bs-target');
        if (targetPane) {
            window.location.hash = targetPane;
            sessionStorage.setItem('ikk_active_tab', targetPane);
        }
        $.fn.dataTable.tables({ visible: true, api: true }).columns.adjust();
        
        if (targetPane === '#pane-tree') {
            loadHierarchyTree();
        }
    });

    // Restore last active tab
    let savedHash = window.location.hash || sessionStorage.getItem('ikk_active_tab');
    if (savedHash && $(`button[data-bs-target="${savedHash}"]`).length) {
        let tabTrigger = new bootstrap.Tab($(`button[data-bs-target="${savedHash}"]`)[0]);
        tabTrigger.show();
    }

    // -------------------------------------------------------------------------
    // 3. DATATABLES: 1. SASARAN STRATEGIS (Tanpa Tombol +, Responsif Normal)
    // -------------------------------------------------------------------------
    let dtSasaran = $('#table-sasaran').DataTable({
        processing: true,
        serverSide: true,
        responsive: false,
        autoWidth: false,
        pageLength: 10,
        ajax: {
            url: AppConfig.initGlobal + 'fetch/ikk-sasaran',
            type: 'POST'
        },
        columns: [
            { 
                data: 'no_urut', 
                className: 'text-center fw-bold text-dark ikk-text-dark',
                render: function (data) { return data !== null ? data : '-'; }
            },
            { 
                data: 'nama_sasaran', 
                render: function (data) {
                    return `<div class="fw-semibold text-dark ikk-text-dark text-wrap">${data}</div>`;
                }
            },
            {
                data: 'total_indikator',
                className: 'text-center',
                orderable: false,
                searchable: false,
                render: function (data, type, row) {
                    let count = parseInt(data) || 0;
                    if (count > 0) {
                        return `
                            <button type="button" class="btn btn-sm btn-outline-dark js-jump-indikator ikk-btn-jump px-2 py-1" data-id="${row.id}" data-nama="${encodeURIComponent(row.nama_sasaran)}" title="Buka Indikator">
                                ${count} Indikator <i class="bi bi-chevron-right ms-1"></i>
                            </button>
                        `;
                    } else {
                        return `
                            <span class="badge bg-light text-danger border border-danger-subtle p-2">
                                0 Indikator
                            </span>
                        `;
                    }
                }
            },
            { 
                data: 'created_at',
                render: function (data) {
                    return data ? `<small class="text-dark ikk-text-dark">${data.substring(0, 10)}</small>` : '-';
                }
            },
            {
                data: 'id',
                orderable: false,
                searchable: false,
                className: 'text-center',
                render: function (data) {
                    return `
                        <div class="table-action-gap">
                            <button type="button" class="btn btn-sm btn-warning btn-edit-sasaran" data-id="${data}" title="Edit Sasaran"><i class="bi bi-pencil"></i></button>
                            <button type="button" class="btn btn-sm btn-danger btn-delete-sasaran" data-id="${data}" title="Hapus Sasaran"><i class="bi bi-trash"></i></button>
                        </div>
                    `;
                }
            }
        ]
    });

    // -------------------------------------------------------------------------
    // 4. DATATABLES: 2. INDIKATOR KINERJA (DENGAN APPEND DETAIL ROW +)
    // -------------------------------------------------------------------------
    function formatIndikatorDetail(d) {
        if (!d) return '';
        let sasaranNama = d.nama_sasaran || '-';
        let target = (d.target_volume && d.satuan) ? `${d.target_volume} ${d.satuan}` : (d.target_volume || '-');
        let totalKeg = parseInt(d.total_kegiatan) || 0;
        let selectedYear = $('#filter-indikator-tahun').val() || $('#filter-global-tahun').val() || new Date().getFullYear();

        let teamsHtml = '<span class="badge bg-light text-secondary border">Belum Ada Pengampu</span>';
        if (d.pengampu_list) {
            let teams = d.pengampu_list.split(',').map(t => t.trim());
            teamsHtml = teams.map(t => `<span class="badge bg-light text-dark ikk-text-dark border me-1 mb-1">${t}</span>`).join('');
        }

        setTimeout(function () {
            loadIndikatorQuarterlyMatrix(d.id, selectedYear);
        }, 100);

        return `
            <div class="child-detail-container p-3 my-2">
                <div class="d-flex align-items-center justify-content-between mb-3 pb-2 border-bottom">
                    <div class="fw-bold fs-6 text-primary d-flex align-items-center gap-2">
                        <i class="bi bi-speedometer2"></i> Rincian Indikator Kinerja &amp; Matriks Capaian Triwulan
                    </div>
                    <div>
                        <span class="badge bg-dark">Indikator #${d.no_urut || '-'}</span>
                    </div>
                </div>
                <div class="row g-3">
                    <div class="col-12 col-md-7">
                        <div class="mb-3">
                            <div class="detail-label">Nama Indikator Kinerja:</div>
                            <div class="detail-value text-wrap fw-bold p-2 bg-white rounded border">${d.nama_indikator || '-'}</div>
                        </div>
                        <div class="mb-3">
                            <div class="detail-label">Sasaran Strategis Induk:</div>
                            <div class="detail-value text-wrap p-2 bg-white rounded border">${sasaranNama}</div>
                        </div>
                        <div>
                            <div class="detail-label">Tim Kerja Pengampu Indikator:</div>
                            <div class="p-2 bg-white rounded border mt-1">
                                ${teamsHtml}
                            </div>
                        </div>
                    </div>
                    <div class="col-12 col-md-5">
                        <div class="p-3 bg-white rounded border h-100 d-flex flex-column justify-content-between">
                            <div>
                                <div class="mb-2">
                                    <div class="detail-label">Target Induk Tahunan:</div>
                                    <div class="fs-6 fw-bold text-success">${target}</div>
                                </div>
                                <div class="mb-3">
                                    <div class="detail-label">Rincian Kegiatan Terkait:</div>
                                    <div class="fw-semibold text-dark">${totalKeg} Kegiatan Terdaftar</div>
                                </div>
                            </div>
                            <div class="pt-2 border-top">
                                <button type="button" class="btn btn-sm btn-success btn-input-realisasi-ind px-2 py-1 me-1 mb-1" data-id="${d.id}">
                                    <i class="bi bi-speedometer2 me-1"></i> Input Realisasi
                                </button>
                                <button type="button" class="btn btn-sm btn-outline-primary btn-assign-pengampu-ind px-2 py-1 me-1 mb-1" data-id="${d.id}" data-nama="${encodeURIComponent(d.nama_indikator)}">
                                    <i class="bi bi-people me-1"></i> Atur Pengampu
                                </button>
                                ${totalKeg > 0 ? `
                                    <button type="button" class="btn btn-sm btn-outline-dark js-jump-kegiatan px-2 py-1 me-1 mb-1" data-id="${d.id}" data-sasaran="${d.sasaran_id}">
                                        <i class="bi bi-diagram-3 me-1"></i> Buka Kegiatan (${totalKeg})
                                    </button>
                                ` : ''}
                                <button type="button" class="btn btn-sm btn-warning btn-edit-indikator px-2 py-1 me-1 mb-1" data-id="${d.id}">
                                    <i class="bi bi-pencil me-1"></i> Edit Indikator
                                </button>
                            </div>
                        </div>
                    </div>

                    <!-- Matriks Capaian 4 Triwulan -->
                    <div class="col-12 mt-3">
                        <div class="d-flex align-items-center justify-content-between mb-2">
                            <div class="detail-label text-dark fw-bold mb-0">
                                <i class="bi bi-calendar3 me-1"></i> Riwayat Capaian Kinerja Tahun ${selectedYear} (Matriks 4 Triwulan):
                            </div>
                        </div>
                        <div id="matrix-tw-ind-${d.id}" class="row g-2">
                            <div class="col-12 text-center py-2 text-muted small">
                                <span class="spinner-border spinner-border-sm me-1 text-primary"></span> Memuat capaian triwulan...
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        `;
    }

    function loadIndikatorQuarterlyMatrix(indikatorId, tahun) {
        let container = $(`#matrix-tw-ind-${indikatorId}`);
        if (!container.length) return;

        $.post(AppConfig.initGlobal + 'fetch/ikk-indikator-realisasi', { indikator_id: indikatorId, tahun: tahun }, function (res) {
            if (res.status === 'success' && res.matrix) {
                let m = res.matrix;
                let html = '';
                for (let tw = 1; tw <= 4; tw++) {
                    let twData = m[tw];
                    let roman = tw === 1 ? 'I' : (tw === 2 ? 'II' : (tw === 3 ? 'III' : 'IV'));
                    if (twData && twData.has_data && twData.data) {
                        let d = twData.data;
                        let p = parseFloat(d.progres_capaian || 0);
                        let pColor = p >= 100 ? 'bg-success text-white' : (p >= 50 ? 'bg-primary text-white' : 'bg-warning text-dark');
                        html += `
                            <div class="col-12 col-md-3">
                                <div class="p-2 bg-white rounded border shadow-none h-100 d-flex flex-column justify-content-between">
                                    <div>
                                        <div class="d-flex justify-content-between align-items-center mb-1">
                                            <span class="fw-bold small text-dark">Triwulan ${roman}</span>
                                            <span class="badge ${pColor} fs-8">${p}%</span>
                                        </div>
                                        <div class="small text-muted" style="font-size: 0.75rem;">
                                            <div>Target: <strong class="text-dark">${d.target_periode || '-'}</strong></div>
                                            <div>Realisasi: <strong class="text-dark">${d.jumlah_realisasi !== null ? d.jumlah_realisasi : '-'}</strong></div>
                                        </div>
                                        ${d.bukti_dukung ? `
                                            <div class="mt-1" style="font-size: 0.72rem;">
                                                <a href="${encodeURI(d.bukti_dukung)}" target="_blank" rel="noopener" class="text-primary text-truncate d-block" title="${d.bukti_dukung}">
                                                    <i class="bi bi-link-45deg me-1"></i>Bukti Dukung
                                                </a>
                                            </div>
                                        ` : ''}
                                    </div>
                                    <div class="pt-2 mt-2 border-top text-end">
                                        <button type="button" class="btn btn-xs btn-outline-success btn-fill-tw-ind px-2 py-0" style="font-size: 0.75rem;" data-id="${indikatorId}" data-tw="${tw}" data-tahun="${tahun}" title="Ubah Capaian TW ${roman}">
                                            <i class="bi bi-pencil-square me-1"></i>Edit TW ${roman}
                                        </button>
                                    </div>
                                </div>
                            </div>
                        `;
                    } else {
                        html += `
                            <div class="col-12 col-md-3">
                                <div class="p-2 bg-white rounded border shadow-none h-100 d-flex flex-column justify-content-between" style="border-style: dashed !important;">
                                    <div>
                                        <div class="d-flex justify-content-between align-items-center mb-1">
                                            <span class="fw-bold small text-secondary">Triwulan ${roman}</span>
                                            <span class="badge bg-light text-muted border fs-8">Belum Diisi</span>
                                        </div>
                                        <p class="small text-muted mb-0" style="font-size: 0.75rem;">Belum ada catatan realisasi pada periode ini.</p>
                                    </div>
                                    <div class="pt-2 mt-2 border-top text-end">
                                        <button type="button" class="btn btn-xs btn-outline-primary btn-fill-tw-ind px-2 py-0" style="font-size: 0.75rem;" data-id="${indikatorId}" data-tw="${tw}" data-tahun="${tahun}" title="Isi Capaian TW ${roman}">
                                            <i class="bi bi-plus-circle me-1"></i>Isi TW ${roman}
                                        </button>
                                    </div>
                                </div>
                            </div>
                        `;
                    }
                }
                container.html(html);
            } else {
                container.html('<div class="col-12 text-muted small py-1">Gagal memuat rincian triwulan.</div>');
            }
        }).fail(function () {
            container.html('<div class="col-12 text-muted small py-1">Gagal memuat rincian triwulan.</div>');
        });
    }

    let dtIndikator = $('#table-indikator').DataTable({
        processing: true,
        serverSide: true,
        responsive: false,
        autoWidth: false,
        order: [[1, 'asc']],
        pageLength: 10,
        ajax: {
            url: AppConfig.initGlobal + 'fetch/ikk-indikator',
            type: 'POST',
            data: function (d) {
                d.sasaran_id = $('#filter-indikator-sasaran').val();
                d.tahun = $('#filter-indikator-tahun').val();
                d.triwulan = $('#filter-indikator-triwulan').val();
            }
        },
        columns: [
            {
                data: null,
                className: 'text-center dt-toggle-cell',
                orderable: false,
                searchable: false,
                render: function () {
                    return `
                        <button type="button" class="btn btn-sm btn-outline-primary btn-toggle-child p-0 d-inline-flex align-items-center justify-content-center shadow-none" style="width: 24px; height: 24px; border-radius: 6px;" title="Buka Detail">
                            <i class="bi bi-plus-lg fw-bold" style="font-size: 0.75rem;"></i>
                        </button>
                    `;
                }
            },
            { 
                data: 'no_urut', 
                className: 'text-center fw-bold text-dark ikk-text-dark',
                render: function (data) { return data !== null ? data : '-'; }
            },
            { 
                data: 'nama_indikator',
                render: function (data, type, row) {
                    let sasaranBadge = row.nama_sasaran ? `<div class="mt-1"><span class="badge bg-light text-secondary border text-wrap text-start font-monospace" style="font-size: 0.72rem;">Sasaran: ${row.nama_sasaran}</span></div>` : '';
                    return `<div><span class="fw-semibold text-dark ikk-text-dark">${data}</span>${sasaranBadge}</div>`;
                }
            },
            { 
                data: 'target_volume', 
                render: function (d, type, row) { 
                    return d ? `<span class="fw-bold text-dark ikk-text-dark">${d} ${row.satuan || ''}</span>` : '-'; 
                } 
            },
            {
                data: 'pengampu_list',
                orderable: false,
                searchable: false,
                render: function (data) {
                    if (data) {
                        let teams = data.split(',').map(t => t.trim());
                        return teams.map(t => `<span class="badge bg-light text-dark ikk-text-dark border me-1 mb-1">${t}</span>`).join('');
                    }
                    return `<span class="badge bg-light text-secondary border">Belum Ada Pengampu</span>`;
                }
            },
            {
                data: 'periode_progres',
                className: 'text-center',
                orderable: false,
                searchable: false,
                render: function (data, type, row) {
                    if (data !== null && data !== undefined && data !== '') {
                        let p = parseFloat(data);
                        let pColor = p >= 100 ? 'bg-success text-white' : (p >= 50 ? 'bg-primary text-white' : 'bg-warning text-dark');
                        let twLabel = row.realisasi_tw ? `<span class="badge bg-light text-dark ikk-text-dark border me-1">TW ${row.realisasi_tw}</span>` : '';
                        let realSub = (row.periode_realisasi !== null && row.periode_realisasi !== '') 
                            ? `<div class="small text-muted mt-1" style="font-size: 0.75rem;">Real: ${row.periode_realisasi} ${row.satuan || ''}</div>` 
                            : '';
                        return `<div class="text-center">${twLabel}<span class="badge ${pColor}">${p}%</span>${realSub}</div>`;
                    } else {
                        return `<div class="text-center"><span class="badge bg-light text-muted border" style="font-size: 0.75rem;">Belum Diisi</span></div>`;
                    }
                }
            },
            {
                data: 'id',
                orderable: false,
                searchable: false,
                className: 'text-center',
                render: function (data, type, row) {
                    return `
                        <div class="table-action-gap d-flex align-items-center justify-content-center gap-1">
                            <button type="button" class="btn btn-sm btn-outline-success btn-input-realisasi-ind px-2 py-1" data-id="${data}" title="Input/Update Realisasi Periode"><i class="bi bi-speedometer2"></i></button>
                            <button type="button" class="btn btn-sm btn-outline-primary btn-assign-pengampu-ind px-2 py-1" data-id="${data}" data-nama="${encodeURIComponent(row.nama_indikator)}" title="Atur Pengampu"><i class="bi bi-people"></i></button>
                            <button type="button" class="btn btn-sm btn-warning btn-edit-indikator px-2 py-1" data-id="${data}" title="Edit Indikator"><i class="bi bi-pencil"></i></button>
                            <button type="button" class="btn btn-sm btn-danger btn-delete-indikator px-2 py-1" data-id="${data}" title="Hapus Indikator"><i class="bi bi-trash"></i></button>
                        </div>
                    `;
                }
            }
        ]
    });

    $('#table-indikator tbody').on('click', '.btn-toggle-child, td.dt-toggle-cell', function (e) {
        if ($(e.target).closest('.btn-toggle-child').length === 0 && !$(e.target).hasClass('dt-toggle-cell')) {
            return;
        }
        let tr = $(this).closest('tr');
        let row = dtIndikator.row(tr);
        let btn = tr.find('.btn-toggle-child');
        let icon = btn.find('i');

        if (row.child.isShown()) {
            row.child.hide();
            tr.removeClass('shown');
            icon.removeClass('bi-dash-lg').addClass('bi-plus-lg');
            btn.removeClass('btn-secondary').addClass('btn-outline-primary');
        } else {
            row.child(formatIndikatorDetail(row.data())).show();
            tr.addClass('shown');
            icon.removeClass('bi-plus-lg').addClass('bi-dash-lg');
            btn.removeClass('btn-outline-primary').addClass('btn-secondary');
        }
    });

    // -------------------------------------------------------------------------
    // 5. DATATABLES: 3. KEGIATAN & PENGAMPU (DENGAN APPEND DETAIL ROW +)
    // -------------------------------------------------------------------------
    function formatKegiatanDetail(d) {
        if (!d) return '';
        let target = (d.target_volume ? d.target_volume : '-') + (d.satuan ? ' ' + d.satuan : '');
        let hasPengampu = parseInt(d.total_pengampu) > 0;
        let teamsHtml = '';

        if (d.pengampu_list && d.pengampu_list.trim() !== '') {
            let teams = d.pengampu_list.split(',');
            teamsHtml = teams.map(t => `<span class="badge bg-light text-dark ikk-text-dark border p-2 me-2 mb-2 d-inline-flex align-items-center"><i class="bi bi-people-fill text-primary me-2"></i>${t.trim()}</span>`).join('');
        } else {
            teamsHtml = `
                <div class="alert alert-warning py-2 px-3 mb-0 d-flex align-items-center justify-content-between flex-wrap gap-2">
                    <div class="d-flex align-items-center gap-2">
                        <i class="bi bi-exclamation-triangle-fill text-warning fs-5"></i>
                        <div>
                            <strong class="text-dark">Belum ada Tim Kerja Pengampu.</strong> 
                            <div class="small text-secondary">Tetapkan tim pengampu terlebih dahulu agar transaksi kegiatan ini dapat dicatat.</div>
                        </div>
                    </div>
                    <button type="button" class="btn btn-sm btn-primary btn-assign-pengampu" data-id="${d.id}" data-nama="${encodeURIComponent(d.nama_kegiatan)}">
                        <i class="bi bi-person-plus-fill me-1"></i> Tetapkan Pengampu Sekarang
                    </button>
                </div>
            `;
        }

        let statusBadge = hasPengampu 
            ? `<span class="badge bg-success p-2"><i class="bi bi-check-circle me-1"></i> Siap Input Transaksi</span>`
            : `<span class="badge bg-secondary p-2"><i class="bi bi-lock-fill me-1"></i> Terkunci (Wajib Pengampu)</span>`;

        return `
            <div class="child-detail-container p-3 my-2">
                <div class="d-flex align-items-center justify-content-between mb-3 pb-2 border-bottom">
                    <div class="fw-bold fs-6 text-primary d-flex align-items-center gap-2">
                        <i class="bi bi-diagram-3-fill"></i> Rincian Terperinci Kegiatan Operasional & Pengampu
                    </div>
                    <div>
                        <span class="badge bg-dark me-2">Kegiatan #${d.no_urut || '-'}</span>
                        ${statusBadge}
                    </div>
                </div>
                <div class="row g-3">
                    <div class="col-12 col-md-7">
                        <div class="mb-3">
                            <div class="detail-label">Nama Kegiatan Operasional:</div>
                            <div class="detail-value text-wrap fw-bold p-2 bg-white rounded border">${d.nama_kegiatan || '-'}</div>
                        </div>
                        <div>
                            <div class="detail-label">Indikator Kinerja Induk:</div>
                            <div class="detail-value text-wrap p-2 bg-white rounded border">${d.nama_indikator || '-'}</div>
                        </div>
                    </div>
                    <div class="col-12 col-md-5">
                        <div class="p-3 bg-white rounded border h-100 d-flex flex-column justify-content-between">
                            <div>
                                <div class="mb-2">
                                    <div class="detail-label">Target Volume Operasional:</div>
                                    <div class="fs-6 fw-bold text-success">${target}</div>
                                </div>
                                <div class="mb-3">
                                    <div class="detail-label">Status Kesiapan:</div>
                                    <div class="small text-secondary">${hasPengampu ? 'Pengampu sudah terhubung, kegiatan siap mencatat transaksi realisasi.' : 'Wajib menugaskan tim kerja sebelum realisasi dapat diinput.'}</div>
                                </div>
                            </div>
                            <div class="pt-2 border-top">
                                <button type="button" class="btn btn-sm btn-outline-primary btn-assign-pengampu px-2 py-1 me-1 mb-1" data-id="${d.id}" data-nama="${encodeURIComponent(d.nama_kegiatan)}">
                                    <i class="bi bi-people-fill me-1"></i> Atur Pengampu
                                </button>
                                ${hasPengampu ? `
                                    <button type="button" class="btn btn-sm btn-outline-danger btn-add-transaksi-inline px-2 py-1 me-1 mb-1" data-id="${d.id}">
                                        <i class="bi bi-plus-circle me-1"></i> Input Transaksi
                                    </button>
                                ` : ''}
                                <button type="button" class="btn btn-sm btn-warning btn-edit-kegiatan px-2 py-1 me-1 mb-1" data-id="${d.id}">
                                    <i class="bi bi-pencil me-1"></i> Edit Kegiatan
                                </button>
                            </div>
                        </div>
                    </div>
                    <div class="col-12">
                        <div class="detail-label">Daftar Lengkap Tim Kerja Pengampu Terdaftar:</div>
                        <div class="p-2 bg-white rounded border mt-1">
                            ${teamsHtml}
                        </div>
                    </div>
                </div>
            </div>
        `;
    }

    let dtKegiatan = $('#table-kegiatan').DataTable({
        processing: true,
        serverSide: true,
        responsive: false,
        autoWidth: false,
        order: [[1, 'asc']],
        pageLength: 10,
        ajax: {
            url: AppConfig.initGlobal + 'fetch/ikk-kegiatan',
            type: 'POST',
            data: function (d) {
                d.sasaran_id = $('#filter-kegiatan-sasaran').val();
                d.indikator_id = $('#filter-kegiatan-indikator').val();
                d.timkerja_id = $('#filter-kegiatan-timkerja').val();
            }
        },
        columns: [
            {
                data: null,
                className: 'text-center dt-toggle-cell',
                orderable: false,
                searchable: false,
                render: function () {
                    return `
                        <button type="button" class="btn btn-sm btn-outline-primary btn-toggle-child p-0 d-inline-flex align-items-center justify-content-center shadow-none" style="width: 24px; height: 24px; border-radius: 6px;" title="Buka Detail">
                            <i class="bi bi-plus-lg fw-bold" style="font-size: 0.75rem;"></i>
                        </button>
                    `;
                }
            },
            { 
                data: 'no_urut', 
                className: 'text-center fw-bold text-dark ikk-text-dark',
                render: function (data) { return data !== null ? data : '-'; }
            },
            { 
                data: 'nama_kegiatan',
                render: function (data) {
                    return `<div class="fw-semibold text-dark ikk-text-dark text-wrap">${data}</div>`;
                }
            },
            { 
                data: 'nama_indikator',
                render: function (data) {
                    return `<span class="badge bg-light text-dark ikk-text-dark border text-wrap text-start">${data || '-'}</span>`;
                }
            },
            {
                data: 'pengampu_list',
                render: function (data) {
                    if (!data || data.trim() === '') {
                        return `<span class="badge bg-danger-subtle text-danger border border-danger-subtle">Belum Ada</span>`;
                    }
                    let teams = data.split(',');
                    if (teams.length === 1) {
                        return `<span class="badge bg-light text-dark ikk-text-dark border text-wrap text-start">${teams[0].trim()}</span>`;
                    } else {
                        return `
                            <span class="badge bg-light text-dark ikk-text-dark border text-wrap text-start">${teams[0].trim()}</span>
                            <span class="badge bg-primary rounded-pill ms-1" title="${data}">+${teams.length - 1} tim</span>
                        `;
                    }
                }
            },
            {
                data: 'total_pengampu',
                className: 'text-center',
                render: function (data) {
                    let hasPengampu = parseInt(data) > 0;
                    if (hasPengampu) {
                        return `<span class="badge bg-success">Siap Input</span>`;
                    } else {
                        return `<span class="badge bg-secondary">Terkunci</span>`;
                    }
                }
            },
            {
                data: 'id',
                orderable: false,
                searchable: false,
                className: 'text-center',
                render: function (data, type, row) {
                    let hasPengampu = parseInt(row.total_pengampu) > 0;
                    let disabledAttr = hasPengampu ? '' : 'disabled title="Wajib tetapkan Tim Pengampu terlebih dahulu"';
                    return `
                        <div class="table-action-gap">
                            <button type="button" class="btn btn-sm btn-outline-primary btn-assign-pengampu" data-id="${data}" data-nama="${encodeURIComponent(row.nama_kegiatan)}" title="Atur Pengampu"><i class="bi bi-people-fill"></i></button>
                            <button type="button" class="btn btn-sm btn-outline-danger btn-add-transaksi-inline" data-id="${data}" ${disabledAttr} title="Input Transaksi"><i class="bi bi-plus-circle"></i></button>
                            <button type="button" class="btn btn-sm btn-warning btn-edit-kegiatan" data-id="${data}" title="Edit Kegiatan"><i class="bi bi-pencil"></i></button>
                            <button type="button" class="btn btn-sm btn-danger btn-delete-kegiatan" data-id="${data}" title="Hapus Kegiatan"><i class="bi bi-trash"></i></button>
                        </div>
                    `;
                }
            }
        ]
    });

    $('#table-kegiatan tbody').on('click', '.btn-toggle-child, td.dt-toggle-cell', function (e) {
        if ($(e.target).closest('.btn-toggle-child').length === 0 && !$(e.target).hasClass('dt-toggle-cell')) {
            return;
        }
        let tr = $(this).closest('tr');
        let row = dtKegiatan.row(tr);
        let btn = tr.find('.btn-toggle-child');
        let icon = btn.find('i');

        if (row.child.isShown()) {
            row.child.hide();
            tr.removeClass('shown');
            icon.removeClass('bi-dash-lg').addClass('bi-plus-lg');
            btn.removeClass('btn-secondary').addClass('btn-outline-primary');
        } else {
            row.child(formatKegiatanDetail(row.data())).show();
            tr.addClass('shown');
            icon.removeClass('bi-plus-lg').addClass('bi-dash-lg');
            btn.removeClass('btn-outline-primary').addClass('btn-secondary');
        }
    });

    // -------------------------------------------------------------------------
    // 6. DATATABLES: 4. TRANSAKSI REALISASI HARIAN (DENGAN APPEND DETAIL ROW +)
    // -------------------------------------------------------------------------
    function formatTransaksiDetail(d) {
        if (!d) return '';
        let val = parseFloat(d.progres_realisasi) || 0;
        let color = val >= 100 ? 'bg-success' : (val >= 50 ? 'bg-primary' : 'bg-warning');

        let buktiHtml = '';
        if (d.bukti_pekerjaan && d.bukti_pekerjaan.trim() !== '') {
            let isUrl = d.bukti_pekerjaan.startsWith('http://') || d.bukti_pekerjaan.startsWith('https://');
            if (isUrl) {
                buktiHtml = `
                    <div class="d-flex align-items-center gap-2 flex-wrap">
                        <a href="${d.bukti_pekerjaan}" target="_blank" class="btn btn-sm btn-primary">
                            <i class="bi bi-box-arrow-up-right me-1"></i> Buka Tautan Bukti Digital
                        </a>
                        <span class="small text-muted text-break">${d.bukti_pekerjaan}</span>
                    </div>
                `;
            } else {
                buktiHtml = `<div class="p-2 bg-light rounded border text-wrap text-dark fw-semibold">${d.bukti_pekerjaan}</div>`;
            }
        } else {
            buktiHtml = '<span class="badge bg-light text-secondary border">Tidak ada tautan / catatan bukti dukung fisik terlampir</span>';
        }

        return `
            <div class="child-detail-container p-3 my-2">
                <div class="d-flex align-items-center justify-content-between mb-3 pb-2 border-bottom">
                    <div class="fw-bold fs-6 text-primary d-flex align-items-center gap-2">
                        <i class="bi bi-journal-check"></i> Rincian Terperinci Realisasi & Bukti Dukung
                    </div>
                    <div>
                        <span class="badge bg-dark">Log Transaksi</span>
                    </div>
                </div>
                <div class="row g-3">
                    <div class="col-12 col-md-6">
                        <div class="mb-3">
                            <div class="detail-label">Pelaksana Pekerjaan:</div>
                            <div class="detail-value text-wrap fw-bold fs-6 p-2 bg-white rounded border text-primary">${d.pelaksana_kegiatan_harian || '-'}</div>
                        </div>
                        <div class="mb-3">
                            <div class="detail-label">Kegiatan Operasional:</div>
                            <div class="detail-value text-wrap fw-semibold p-2 bg-white rounded border">${d.nama_kegiatan || '-'}</div>
                        </div>
                        <div>
                            <div class="detail-label">Tim Kerja Pengampu:</div>
                            <div class="detail-value text-wrap p-2 bg-white rounded border small"><i class="bi bi-people-fill me-1 text-primary"></i>${d.pengampu_list || 'Belum ada pengampu'}</div>
                        </div>
                    </div>
                    <div class="col-12 col-md-6">
                        <div class="p-3 bg-white rounded border h-100 d-flex flex-column justify-content-between">
                            <div>
                                <div class="mb-3">
                                    <div class="detail-label">Periode Waktu Pelaksanaan:</div>
                                    <div class="fw-bold text-dark fs-6"><i class="bi bi-calendar-check me-2 text-primary"></i>${d.period_start_date || '-'} s.d ${d.period_end_date || '-'}</div>
                                    <div class="mt-1 d-flex gap-1 flex-wrap">
                                        <span class="badge bg-primary text-white">Tahun ${d.tahun || '-'}</span>
                                        <span class="badge bg-info-subtle text-dark border">TW ${d.triwulan || '-'}</span>
                                        <span class="badge bg-light text-dark border">${d.periode_key || '-'}</span>
                                    </div>
                                </div>
                                <div class="row g-2 mb-3">
                                    <div class="col-6">
                                        <div class="detail-label">Target Mingguan:</div>
                                        <div class="fw-bold fs-6 text-dark">${d.target_mingguan || '-'}</div>
                                    </div>
                                    <div class="col-6">
                                        <div class="detail-label">Jumlah Realisasi:</div>
                                        <div class="fw-bold fs-6 text-success">${d.jumlah_realisasi !== null ? d.jumlah_realisasi : '-'} ${d.satuan_kegiatan || ''}</div>
                                    </div>
                                </div>
                                <div class="mb-2">
                                    <div class="d-flex justify-content-between align-items-center mb-1">
                                        <span class="detail-label mb-0">Progress Capaian:</span>
                                        <span class="fw-bold text-dark fs-6">${val}%</span>
                                    </div>
                                    <div class="progress" style="height: 12px; border-radius: 6px;">
                                        <div class="progress-bar ${color}" role="progressbar" style="width: ${val}%;" aria-valuenow="${val}" aria-valuemin="0" aria-valuemax="100"></div>
                                    </div>
                                </div>
                            </div>
                            <div class="pt-2 border-top mt-3 text-end">
                                <button type="button" class="btn btn-sm btn-warning btn-edit-transaksi px-2 py-1 me-1" data-id="${d.id}">
                                    <i class="bi bi-pencil me-1"></i> Edit Transaksi
                                </button>
                                <button type="button" class="btn btn-sm btn-danger btn-delete-transaksi px-2 py-1" data-id="${d.id}">
                                    <i class="bi bi-trash me-1"></i> Hapus
                                </button>
                            </div>
                        </div>
                    </div>
                    <div class="col-12">
                        <div class="detail-label">Bukti Dukung Fisik / Tautan Dokumen Pendukung:</div>
                        <div class="p-3 bg-white rounded border mt-1">
                            ${buktiHtml}
                        </div>
                    </div>
                </div>
            </div>
        `;
    }

    let dtTransaksi = $('#table-transaksi').DataTable({
        processing: true,
        serverSide: true,
        responsive: false,
        autoWidth: false,
        order: [[1, 'asc']],
        pageLength: 10,
        ajax: {
            url: AppConfig.initGlobal + 'fetch/ikk-transaksi',
            type: 'POST',
            data: function (d) {
                d.timkerja_id = $('#filter-transaksi-timkerja').val();
                d.kegiatan_id = $('#filter-transaksi-kegiatan').val();
                d.tahun       = $('#filter-transaksi-tahun').val();
                d.triwulan    = $('#filter-transaksi-triwulan').val();
            }
        },
        columns: [
            {
                data: null,
                className: 'text-center dt-toggle-cell',
                orderable: false,
                searchable: false,
                render: function () {
                    return `
                        <button type="button" class="btn btn-sm btn-outline-primary btn-toggle-child p-0 d-inline-flex align-items-center justify-content-center shadow-none" style="width: 24px; height: 24px; border-radius: 6px;" title="Buka Detail">
                            <i class="bi bi-plus-lg fw-bold" style="font-size: 0.75rem;"></i>
                        </button>
                    `;
                }
            },
            { 
                data: 'pelaksana_kegiatan_harian',
                render: function (data) {
                    return `<div class="fw-bold text-dark ikk-text-dark text-wrap">${data}</div>`;
                }
            },
            { 
                data: 'nama_kegiatan',
                render: function (data, type, row) {
                    let pengampuHtml = row.pengampu_list ? `<div class="small text-muted mt-1 ikk-text-sub"><i class="bi bi-people me-1"></i>${row.pengampu_list}</div>` : '';
                    return `<div class="fw-semibold text-dark ikk-text-dark text-wrap">${data}</div>${pengampuHtml}`;
                }
            },
            { 
                data: 'period_start_date',
                render: function (data, type, row) {
                    if (!data) return '-';
                    let twBadge = row.periode_key ? `<span class="badge bg-primary-subtle text-primary border border-primary-subtle mt-1 font-monospace" style="font-size: 0.73rem;">${row.periode_key}</span>` : '';
                    return `<small class="text-dark ikk-text-dark d-block fw-semibold">${data}<br>s.d ${row.period_end_date || '-'}</small>${twBadge}`;
                }
            },
            {
                data: 'progres_realisasi',
                render: function (data, type, row) {
                    let val = parseFloat(data) || 0;
                    let color = val >= 100 ? 'bg-success' : (val >= 50 ? 'bg-primary' : 'bg-warning');
                    return `
                        <div class="d-flex align-items-center gap-2">
                            <div class="progress flex-grow-1" style="height: 8px;">
                                <div class="progress-bar ${color}" role="progressbar" style="width: ${val}%;" aria-valuenow="${val}" aria-valuemin="0" aria-valuemax="100"></div>
                            </div>
                            <span class="small fw-bold text-dark ikk-text-dark">${val}%</span>
                        </div>
                        <small class="text-muted d-block mt-1">${row.jumlah_realisasi !== null ? row.jumlah_realisasi : 0} ${row.satuan_kegiatan || ''}</small>
                    `;
                }
            },
            {
                data: 'bukti_pekerjaan',
                className: 'text-center',
                render: function (data) {
                    if (!data || data.trim() === '') return '<span class="text-muted">-</span>';
                    let isUrl = data.startsWith('http://') || data.startsWith('https://');
                    if (isUrl) {
                        return `<a href="${data}" target="_blank" class="btn btn-sm btn-outline-primary" title="Buka Tautan"><i class="bi bi-link-45deg"></i></a>`;
                    } else {
                        return `<button type="button" class="btn btn-sm btn-outline-secondary js-view-bukti" data-bukti="${encodeURIComponent(data)}" title="Lihat Keterangan"><i class="bi bi-file-text"></i></button>`;
                    }
                }
            },
            {
                data: 'id',
                orderable: false,
                searchable: false,
                className: 'text-center',
                render: function (data) {
                    return `
                        <div class="table-action-gap">
                            <button type="button" class="btn btn-sm btn-warning btn-edit-transaksi" data-id="${data}" title="Edit Transaksi"><i class="bi bi-pencil"></i></button>
                            <button type="button" class="btn btn-sm btn-danger btn-delete-transaksi" data-id="${data}" title="Hapus Transaksi"><i class="bi bi-trash"></i></button>
                        </div>
                    `;
                }
            }
        ]
    });

    $('#table-transaksi tbody').on('click', '.btn-toggle-child, td.dt-toggle-cell', function (e) {
        if ($(e.target).closest('.btn-toggle-child').length === 0 && !$(e.target).hasClass('dt-toggle-cell')) {
            return;
        }
        let tr = $(this).closest('tr');
        let row = dtTransaksi.row(tr);
        let btn = tr.find('.btn-toggle-child');
        let icon = btn.find('i');

        if (row.child.isShown()) {
            row.child.hide();
            tr.removeClass('shown');
            icon.removeClass('bi-dash-lg').addClass('bi-plus-lg');
            btn.removeClass('btn-secondary').addClass('btn-outline-primary');
        } else {
            row.child(formatTransaksiDetail(row.data())).show();
            tr.addClass('shown');
            icon.removeClass('bi-plus-lg').addClass('bi-dash-lg');
            btn.removeClass('btn-outline-primary').addClass('btn-secondary');
        }
    });

    // -------------------------------------------------------------------------
    // 7. CONTEXTUAL JUMP LOGIC
    // -------------------------------------------------------------------------
    $('#table-sasaran').on('click', '.js-jump-indikator', function () {
        let sasId = $(this).data('id');
        $('#filter-indikator-sasaran').val(sasId);
        let tab = new bootstrap.Tab($('#tab-indikator-btn')[0]);
        tab.show();
        dtIndikator.ajax.reload(null, false);
    });

    $('#table-indikator').on('click', '.js-jump-kegiatan', function () {
        let indId = $(this).data('id');
        let sasId = $(this).data('sasaran');
        $('#filter-kegiatan-sasaran').val(sasId);
        loadOptionsIndikator('#filter-kegiatan-indikator', sasId, indId, function () {
            let tab = new bootstrap.Tab($('#tab-kegiatan-btn')[0]);
            tab.show();
            dtKegiatan.ajax.reload(null, false);
        });
    });

    // Global Reload Button
    $('#btn-reload-all').on('click', function () {
        loadSummary();
        dtSasaran.ajax.reload(null, false);
        dtIndikator.ajax.reload(null, false);
        dtKegiatan.ajax.reload(null, false);
        dtTransaksi.ajax.reload(null, false);
        Swal.fire({ icon: 'success', text: 'Data seluruh tahapan berhasil dimuat ulang.', timer: 1200, showConfirmButton: false });
    });

    // -------------------------------------------------------------------------
    // 8. CRUD SASARAN STRATEGIS
    // -------------------------------------------------------------------------
    $('.btn-add-sasaran').on('click', function () {
        $('#form-sasaran')[0].reset();
        $('#sasaran_key').val('');
        $('#modal-sasaran-title').text('Tambah Sasaran Strategis');
        $('#modal-sasaran').modal('show');
    });

    $('#table-sasaran').on('click', '.btn-edit-sasaran', function () {
        let row = dtSasaran.row($(this).closest('tr')).data();
        $('#sasaran_key').val(row.id);
        $('#sasaran_no_urut').val(row.no_urut);
        $('#sasaran_nama_sasaran').val(row.nama_sasaran);
        $('#modal-sasaran-title').text('Edit Sasaran Strategis');
        $('#modal-sasaran').modal('show');
    });

    $('#form-sasaran').on('submit', function (e) {
        e.preventDefault();
        $.post(AppConfig.initGlobal + 'store/ikk-sasaran', $(this).serialize(), function (res) {
            if (res.status === 'success') {
                Swal.fire({ icon: 'success', text: res.message, timer: 1500, showConfirmButton: false });
                $('#modal-sasaran').modal('hide');
                dtSasaran.ajax.reload(null, false);
                loadSummary();
                loadOptionsSasaran('#filter-indikator-sasaran');
                loadOptionsSasaran('#filter-kegiatan-sasaran');
            } else {
                Swal.fire({ icon: 'error', title: 'Gagal', text: res.message });
            }
        });
    });

    $('#table-sasaran').on('click', '.btn-delete-sasaran', function () {
        let id = $(this).data('id');
        Swal.fire({
            title: 'Hapus Sasaran Strategis?',
            text: 'Pastikan sasaran ini tidak memiliki indikator aktif. Penghapusan akan ditolak jika masih terdapat data di bawahnya.',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonText: 'Ya, Hapus',
            cancelButtonText: 'Batal'
        }).then((result) => {
            if (result.isConfirmed) {
                $.post(AppConfig.initGlobal + 'kill/ikk-sasaran', { key: id }, function (res) {
                    if (res.status === 'success') {
                        Swal.fire({ icon: 'success', text: res.message, timer: 1500, showConfirmButton: false });
                        dtSasaran.ajax.reload(null, false);
                        loadSummary();
                        loadOptionsSasaran('#filter-indikator-sasaran');
                        loadOptionsSasaran('#filter-kegiatan-sasaran');
                    } else {
                        Swal.fire({ icon: 'error', title: 'Penghapusan Ditolak', text: res.message });
                    }
                });
            }
        });
    });

    // -------------------------------------------------------------------------
    // 9. CRUD INDIKATOR KINERJA
    // -------------------------------------------------------------------------
    $('.btn-add-indikator').on('click', function () {
        $('#form-indikator')[0].reset();
        $('#indikator_key').val('');
        let currentFilterSas = $('#filter-indikator-sasaran').val();
        loadOptionsSasaran('#indikator_sasaran_id', currentFilterSas, function () {
            $('#modal-indikator-title').text('Tambah Indikator Kinerja');
            $('#modal-indikator').modal('show');
        });
    });

    $('#table-indikator').on('click', '.btn-edit-indikator', function () {
        let tr = $(this).closest('tr');
        let row = dtIndikator.row(tr.hasClass('child') ? tr.prev() : tr).data();
        if (!row) return;
        $('#indikator_key').val(row.id);
        $('#indikator_no_urut').val(row.no_urut);
        $('#indikator_nama_indikator').val(row.nama_indikator);
        $('#indikator_satuan').val(row.satuan);
        $('#indikator_target_volume').val(row.target_volume);

        loadOptionsSasaran('#indikator_sasaran_id', row.sasaran_id, function () {
            $('#modal-indikator-title').text('Edit Indikator Kinerja');
            $('#modal-indikator').modal('show');
        });
    });

    $('#form-indikator').on('submit', function (e) {
        e.preventDefault();
        $.post(AppConfig.initGlobal + 'store/ikk-indikator', $(this).serialize(), function (res) {
            if (res.status === 'success') {
                Swal.fire({ icon: 'success', text: res.message, timer: 1500, showConfirmButton: false });
                $('#modal-indikator').modal('hide');
                dtIndikator.ajax.reload(null, false);
                dtSasaran.ajax.reload(null, false);
                loadSummary();
                loadOptionsIndikator('#filter-kegiatan-indikator');
            } else {
                Swal.fire({ icon: 'error', title: 'Gagal', text: res.message });
            }
        });
    });

    $('#table-indikator').on('click', '.btn-delete-indikator', function () {
        let id = $(this).data('id');
        Swal.fire({
            title: 'Hapus Indikator Kinerja?',
            text: 'Pastikan indikator ini tidak memiliki kegiatan operasional aktif.',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonText: 'Ya, Hapus',
            cancelButtonText: 'Batal'
        }).then((result) => {
            if (result.isConfirmed) {
                $.post(AppConfig.initGlobal + 'kill/ikk-indikator', { key: id }, function (res) {
                    if (res.status === 'success') {
                        Swal.fire({ icon: 'success', text: res.message, timer: 1500, showConfirmButton: false });
                        dtIndikator.ajax.reload(null, false);
                        dtSasaran.ajax.reload(null, false);
                        loadSummary();
                        loadOptionsIndikator('#filter-kegiatan-indikator');
                    } else {
                        Swal.fire({ icon: 'error', title: 'Penghapusan Ditolak', text: res.message });
                    }
                });
            }
        });
    });

    // -------------------------------------------------------------------------
    // 10. CRUD KEGIATAN OPERASIONAL & TIM KERJA PENGAMPU (MULTIPLE SELECT)
    // -------------------------------------------------------------------------
    $('.btn-add-kegiatan').on('click', function () {
        $('#form-kegiatan')[0].reset();
        $('#kegiatan_key').val('');
        let currentFilterInd = $('#filter-kegiatan-indikator').val();
        loadOptionsIndikator('#kegiatan_indikator_id', null, currentFilterInd, function () {
            // Load tim kerja aktif (is_show = 1) untuk multiple select
            loadOptionsTimKerja('#kegiatan_timkerja_id', [], function () {
                $('#modal-kegiatan-title').text('Tambah Kegiatan Operasional');
                $('#modal-kegiatan').modal('show');
                setTimeout(function () {
                    initSelect2Modal('#modal-kegiatan', '#kegiatan_timkerja_id', 'Pilih Tim Kerja Pengampu...');
                }, 200);
            });
        });
    });

    $('#table-kegiatan').on('click', '.btn-edit-kegiatan', function () {
        let tr = $(this).closest('tr');
        let row = dtKegiatan.row(tr.hasClass('child') ? tr.prev() : tr).data();
        if (!row) return;
        $('#kegiatan_key').val(row.id);
        $('#kegiatan_no_urut').val(row.no_urut);
        $('#kegiatan_nama_kegiatan').val(row.nama_kegiatan);
        $('#kegiatan_satuan').val(row.satuan);
        $('#kegiatan_target_volume').val(row.target_volume);

        loadOptionsIndikator('#kegiatan_indikator_id', null, row.indikator_id, function () {
            // Ambil ID tim kerja yang sudah terdaftar untuk kegiatan ini
            $.post(AppConfig.initGlobal + 'fetch/ikk-assigned-pengampu', { relasi_id: row.id, tipe_relasi: 'kegiatan' }, function (res) {
                let assignedIds = (res.status === 'success' && Array.isArray(res.data)) ? res.data : [];
                loadOptionsTimKerja('#kegiatan_timkerja_id', assignedIds, function () {
                    $('#modal-kegiatan-title').text('Edit Kegiatan Operasional');
                    $('#modal-kegiatan').modal('show');
                    setTimeout(function () {
                        initSelect2Modal('#modal-kegiatan', '#kegiatan_timkerja_id', 'Pilih Tim Kerja Pengampu...');
                    }, 200);
                });
            });
        });
    });

    $('#form-kegiatan').on('submit', function (e) {
        e.preventDefault();
        $.post(AppConfig.initGlobal + 'store/ikk-kegiatan', $(this).serialize(), function (res) {
            if (res.status === 'success') {
                Swal.fire({ icon: 'success', text: res.message, timer: 1500, showConfirmButton: false });
                $('#modal-kegiatan').modal('hide');
                dtKegiatan.ajax.reload(null, false);
                dtIndikator.ajax.reload(null, false);
                loadSummary();
                loadOptionsKegiatan('#filter-transaksi-kegiatan');
            } else {
                Swal.fire({ icon: 'error', title: 'Gagal', text: res.message });
            }
        });
    });

    $('#table-kegiatan').on('click', '.btn-delete-kegiatan', function () {
        let id = $(this).data('id');
        Swal.fire({
            title: 'Hapus Kegiatan Operasional?',
            text: 'Pastikan kegiatan ini belum memiliki riwayat transaksi/realisasi harian yang tercatat.',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonText: 'Ya, Hapus',
            cancelButtonText: 'Batal'
        }).then((result) => {
            if (result.isConfirmed) {
                $.post(AppConfig.initGlobal + 'kill/ikk-kegiatan', { key: id }, function (res) {
                    if (res.status === 'success') {
                        Swal.fire({ icon: 'success', text: res.message, timer: 1500, showConfirmButton: false });
                        dtKegiatan.ajax.reload(null, false);
                        dtIndikator.ajax.reload(null, false);
                        loadSummary();
                        loadOptionsKegiatan('#filter-transaksi-kegiatan');
                    } else {
                        Swal.fire({ icon: 'error', title: 'Penghapusan Ditolak', text: res.message });
                    }
                });
            }
        });
    });

    // -------------------------------------------------------------------------
    // 11. MANAJEMEN PENUGASAN PENGAMPU CEPAT (MODAL PENGAMPU)
    // -------------------------------------------------------------------------
    $('#table-indikator').on('click', '.btn-assign-pengampu-ind', function () {
        let indId = $(this).data('id');
        let indNama = decodeURIComponent($(this).data('nama') || '');

        $('#pengampu_relasi_id').val(indId);
        $('#pengampu_tipe_relasi').val('indikator');
        $('#pengampu_label_info').text('Indikator Kinerja:');
        $('#pengampu_kegiatan_info').text(indNama);

        $.post(AppConfig.initGlobal + 'fetch/ikk-assigned-pengampu', { relasi_id: indId, tipe_relasi: 'indikator' }, function (res) {
            let assignedIds = (res.status === 'success' && Array.isArray(res.data)) ? res.data : [];
            loadOptionsTimKerja('#pengampu_timkerja_id', assignedIds, function () {
                $('#modal-pengampu').modal('show');
                setTimeout(function () {
                    initSelect2Modal('#modal-pengampu', '#pengampu_timkerja_id', 'Pilih Tim Kerja Pengampu...');
                }, 200);
            });
        });
    });

    $('#table-kegiatan').on('click', '.btn-assign-pengampu', function () {
        let kegiatanId = $(this).data('id');
        let kegiatanNama = decodeURIComponent($(this).data('nama') || '');

        $('#pengampu_relasi_id').val(kegiatanId);
        $('#pengampu_tipe_relasi').val('kegiatan');
        $('#pengampu_label_info').text('Kegiatan Operasional:');
        $('#pengampu_kegiatan_info').text(kegiatanNama);

        // Ambil ID tim kerja yang sudah terdaftar
        $.post(AppConfig.initGlobal + 'fetch/ikk-assigned-pengampu', { relasi_id: kegiatanId, tipe_relasi: 'kegiatan' }, function (res) {
            let assignedIds = (res.status === 'success' && Array.isArray(res.data)) ? res.data : [];
            loadOptionsTimKerja('#pengampu_timkerja_id', assignedIds, function () {
                $('#modal-pengampu').modal('show');
                setTimeout(function () {
                    initSelect2Modal('#modal-pengampu', '#pengampu_timkerja_id', 'Pilih Tim Kerja Pengampu...');
                }, 200);
            });
        });
    });

    $('#form-pengampu').on('submit', function (e) {
        e.preventDefault();
        $.post(AppConfig.initGlobal + 'store/ikk-pengampu', $(this).serialize(), function (res) {
            if (res.status === 'success') {
                Swal.fire({ icon: 'success', text: res.message, timer: 1500, showConfirmButton: false });
                $('#modal-pengampu').modal('hide');
                dtKegiatan.ajax.reload(null, false);
                dtIndikator.ajax.reload(null, false);
                loadSummary();
                loadOptionsKegiatan('#filter-transaksi-kegiatan');
            } else {
                Swal.fire({ icon: 'error', title: 'Gagal', text: res.message });
            }
        });
    });

    // -------------------------------------------------------------------------
    // 11.B PENCATATAN REALISASI INDIKATOR PERIODIK & SMART ASSIST
    // -------------------------------------------------------------------------
    function openRealisasiIndikatorModal(indikatorId, tahun = null, triwulan = null) {
        let curYear = tahun || $('#filter-indikator-tahun').val() || $('#filter-global-tahun').val() || new Date().getFullYear();
        let curTw = triwulan || $('#filter-indikator-triwulan').val() || $('#filter-global-triwulan').val() || 1;

        $('#realisasi_ind_id').val(indikatorId);
        $('#realisasi_ind_tahun').val(curYear);
        $('#realisasi_ind_triwulan').val(curTw);

        // Fetch indikator data, matriks, assist, dan seluruh log riwayat pengisian
        $.post(AppConfig.initGlobal + 'fetch/ikk-indikator-realisasi', { indikator_id: indikatorId, tahun: curYear }, function (res) {
            if (res.status === 'success') {
                // Header Indikator Info
                if (res.indikator) {
                    $('#realisasi_ind_nama').text(res.indikator.nama_indikator || '-');
                    $('#realisasi_ind_sasaran').text(res.indikator.nama_sasaran || '-');
                    let teamsHtml = res.indikator.pengampu_list ? res.indikator.pengampu_list.split(',').map(t => `<span class="badge bg-light text-dark border me-1">${t.trim()}</span>`).join('') : '<span class="badge bg-light text-muted border">Belum Ada Pengampu</span>';
                    $('#realisasi_ind_pengampu').html(teamsHtml);
                    if (res.indikator.target_volume) {
                        $('#realisasi_ind_target').attr('placeholder', `Target Induk: ${res.indikator.target_volume} ${res.indikator.satuan || ''}`);
                    }
                }

                let matrix = res.matrix;
                let activeTwData = (matrix && matrix[curTw] && matrix[curTw].has_data) ? matrix[curTw].data : null;

                if (activeTwData) {
                    $('#realisasi_ind_target').val(activeTwData.target_periode || '');
                    $('#realisasi_ind_jumlah').val(activeTwData.jumlah_realisasi !== null ? activeTwData.jumlah_realisasi : '');
                    $('#realisasi_ind_progres').val(activeTwData.progres_capaian || '');
                    $('#realisasi_ind_penjelasan').val(activeTwData.penjelasan_capaian || '');
                    $('#realisasi_ind_bukti').val(activeTwData.bukti_dukung || '');
                } else {
                    $('#realisasi_ind_target').val('');
                    $('#realisasi_ind_jumlah').val('');
                    $('#realisasi_ind_progres').val('');
                    $('#realisasi_ind_penjelasan').val('');
                    $('#realisasi_ind_bukti').val('');
                }

                // Smart assist box
                let assist = res.assist;
                if (assist && assist.has_kegiatan && assist.total_kegiatan > 0) {
                    $('#smart-assist-box').show();
                    $('#smart-assist-keg-count').text(`Indikator memiliki ${assist.total_kegiatan} kegiatan operasional.`);
                    $('#smart-assist-sub').text(`Estimasi capaian log kegiatan saat ini: ${assist.suggested_progres}%.`);
                } else {
                    $('#smart-assist-box').hide();
                }

                // Render Riwayat / Log Pengisian
                let logs = Array.isArray(res.logs) ? res.logs : [];
                $('#realisasi_ind_log_count').text(`${logs.length} Catatan`);
                let tbody = $('#tbody-log-realisasi-ind');

                if (logs.length === 0) {
                    tbody.html('<tr><td colspan="6" class="text-center text-muted py-3">Belum ada catatan realisasi untuk indikator ini.</td></tr>');
                } else {
                    let rowsHtml = '';
                    logs.forEach(log => {
                        let p = parseFloat(log.progres_capaian || 0);
                        let pColor = p >= 100 ? 'bg-success text-white' : (p >= 50 ? 'bg-primary text-white' : 'bg-warning text-dark');
                        let buktiLink = log.bukti_dukung ? `<div class="mt-1"><a href="${log.bukti_dukung}" target="_blank" class="small text-primary text-decoration-underline text-truncate d-inline-block" style="max-width: 200px;">Buka Bukti Dukung</a></div>` : '';
                        let ketText = log.penjelasan_capaian ? `<div class="text-truncate small" style="max-width: 240px;" title="${log.penjelasan_capaian}">${log.penjelasan_capaian}</div>` : '<span class="text-muted small">-</span>';

                        rowsHtml += `
                            <tr>
                                <td class="text-center fw-semibold text-nowrap">
                                    <span class="badge bg-dark">TW ${log.triwulan} (${log.tahun})</span>
                                </td>
                                <td class="text-wrap small">${log.target_periode || '-'}</td>
                                <td class="text-center text-nowrap">${log.jumlah_realisasi !== null ? log.jumlah_realisasi : '-'}</td>
                                <td class="text-center">
                                    <span class="badge ${pColor}">${p}%</span>
                                </td>
                                <td>
                                    ${ketText}
                                    ${buktiLink}
                                </td>
                                <td class="text-center text-nowrap">
                                    <div class="d-flex gap-1 justify-content-center">
                                        <button type="button" class="btn btn-sm btn-outline-primary py-0 px-2 btn-edit-realisasi-log"
                                            data-id="${log.id}"
                                            data-tw="${log.triwulan}"
                                            data-tahun="${log.tahun}"
                                            data-target="${encodeURIComponent(log.target_periode || '')}"
                                            data-jumlah="${log.jumlah_realisasi !== null ? log.jumlah_realisasi : ''}"
                                            data-progres="${log.progres_capaian || ''}"
                                            data-penjelasan="${encodeURIComponent(log.penjelasan_capaian || '')}"
                                            data-bukti="${encodeURIComponent(log.bukti_dukung || '')}">
                                            Edit
                                        </button>
                                        <button type="button" class="btn btn-sm btn-outline-danger py-0 px-2 btn-delete-realisasi-log"
                                            data-id="${log.id}"
                                            data-periode="TW ${log.triwulan} Tahun ${log.tahun}">
                                            Hapus
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        `;
                    });
                    tbody.html(rowsHtml);
                }

                $('#modal-realisasi-indikator').modal('show');
            }
        });
    }

    // Klik tombol Input Realisasi di tabel atau detail indikator
    $('#table-indikator').on('click', '.btn-input-realisasi-ind', function () {
        let indId = $(this).data('id');
        let tr = $(this).closest('tr');
        let row = dtIndikator.row(tr.hasClass('child') ? tr.prev() : tr).data();
        if (row) {
            $('#realisasi_ind_nama').text(row.nama_indikator || '-');
            $('#realisasi_ind_sasaran').text(row.nama_sasaran || '-');
            let teamsHtml = row.pengampu_list ? row.pengampu_list.split(',').map(t => `<span class="badge bg-light text-dark border me-1">${t.trim()}</span>`).join('') : '<span class="badge bg-light text-muted border">Belum Ada Pengampu</span>';
            $('#realisasi_ind_pengampu').html(teamsHtml);
            if (row.target_volume) {
                $('#realisasi_ind_target').attr('placeholder', `Target Induk: ${row.target_volume} ${row.satuan || ''}`);
            }
        }
        openRealisasiIndikatorModal(indId);
    });

    // Klik tombol isi triwulan tertentu di matriks detail
    $('#table-indikator').on('click', '.btn-fill-tw-ind', function () {
        let indId = $(this).data('id');
        let tw = $(this).data('tw');
        let thn = $(this).data('tahun');
        let tr = $(this).closest('tr');
        let row = dtIndikator.row(tr.hasClass('child') ? tr.prev() : tr).data();
        if (row) {
            $('#realisasi_ind_nama').text(row.nama_indikator || '-');
            $('#realisasi_ind_sasaran').text(row.nama_sasaran || '-');
            let teamsHtml = row.pengampu_list ? row.pengampu_list.split(',').map(t => `<span class="badge bg-light text-dark border me-1">${t.trim()}</span>`).join('') : '<span class="badge bg-light text-muted border">Belum Ada Pengampu</span>';
            $('#realisasi_ind_pengampu').html(teamsHtml);
        }
        openRealisasiIndikatorModal(indId, thn, tw);
    });

    // Perubahan tahun atau triwulan di modal realisasi indikator -> auto reload isian TW tersebut
    $('#realisasi_ind_tahun, #realisasi_ind_triwulan').on('change', function () {
        let indId = $('#realisasi_ind_id').val();
        let thn = $('#realisasi_ind_tahun').val();
        let tw = $('#realisasi_ind_triwulan').val();
        if (indId && thn && tw) {
            $.post(AppConfig.initGlobal + 'fetch/ikk-indikator-realisasi', { indikator_id: indId, tahun: thn }, function (res) {
                if (res.status === 'success' && res.matrix && res.matrix[tw]) {
                    let d = res.matrix[tw].data;
                    if (d) {
                        $('#realisasi_ind_target').val(d.target_periode || '');
                        $('#realisasi_ind_jumlah').val(d.jumlah_realisasi !== null ? d.jumlah_realisasi : '');
                        $('#realisasi_ind_progres').val(d.progres_capaian || '');
                        $('#realisasi_ind_penjelasan').val(d.penjelasan_capaian || '');
                        $('#realisasi_ind_bukti').val(d.bukti_dukung || '');
                    } else {
                        $('#realisasi_ind_target').val('');
                        $('#realisasi_ind_jumlah').val('');
                        $('#realisasi_ind_progres').val('');
                        $('#realisasi_ind_penjelasan').val('');
                        $('#realisasi_ind_bukti').val('');
                    }
                }
            });
        }
    });

    // Tombol Edit pada baris Riwayat / Log Pengisian Modal
    $('#modal-realisasi-indikator').on('click', '.btn-edit-realisasi-log', function () {
        let thn = $(this).data('tahun');
        let tw = $(this).data('tw');
        let target = decodeURIComponent($(this).data('target') || '');
        let jumlah = $(this).data('jumlah');
        let progres = $(this).data('progres');
        let penjelasan = decodeURIComponent($(this).data('penjelasan') || '');
        let bukti = decodeURIComponent($(this).data('bukti') || '');

        $('#realisasi_ind_tahun').val(thn);
        $('#realisasi_ind_triwulan').val(tw);
        $('#realisasi_ind_target').val(target);
        $('#realisasi_ind_jumlah').val(jumlah);
        $('#realisasi_ind_progres').val(progres);
        $('#realisasi_ind_penjelasan').val(penjelasan);
        $('#realisasi_ind_bukti').val(bukti);

        // Gulir ke atas modal dan fokuskan ke progres
        $('#modal-realisasi-indikator .modal-body').animate({ scrollTop: 0 }, 250);
        $('#realisasi_ind_progres').focus();
    });

    // Tombol Hapus pada baris Riwayat / Log Pengisian Modal
    $('#modal-realisasi-indikator').on('click', '.btn-delete-realisasi-log', function () {
        let logId = $(this).data('id');
        let periode = $(this).data('periode');
        let indId = $('#realisasi_ind_id').val();
        let curThn = $('#realisasi_ind_tahun').val();
        let curTw = $('#realisasi_ind_triwulan').val();

        Swal.fire({
            title: 'Hapus Realisasi?',
            text: `Apakah Anda yakin ingin menghapus catatan realisasi ${periode}?`,
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#dc3545',
            confirmButtonText: 'Ya, Hapus',
            cancelButtonText: 'Batal'
        }).then((result) => {
            if (result.isConfirmed) {
                $.post(AppConfig.initGlobal + 'kill/ikk-indikator-realisasi', { id: logId }, function (res) {
                    if (res.status === 'success') {
                        Swal.fire({ icon: 'success', text: res.message, timer: 1500, showConfirmButton: false });
                        openRealisasiIndikatorModal(indId, curThn, curTw);
                        dtIndikator.ajax.reload(null, false);
                        loadSummary();
                        loadOptionsTahun();
                    } else {
                        Swal.fire({ icon: 'error', text: res.message || 'Gagal menghapus data realisasi.' });
                    }
                }).fail(function () {
                    Swal.fire({ icon: 'error', text: 'Terjadi kesalahan sistem saat menghapus realisasi.' });
                });
            }
        });
    });

    // Tombol Reset Form Input Realisasi
    $('#btnResetFormRealisasiInd').on('click', function () {
        $('#realisasi_ind_target').val('');
        $('#realisasi_ind_jumlah').val('');
        $('#realisasi_ind_progres').val('');
        $('#realisasi_ind_penjelasan').val('');
        $('#realisasi_ind_bukti').val('');
        $('#realisasi_ind_target').focus();
    });

    // Tombol Smart Assist "Tarik dari Log Kegiatan"
    $('#btn-tarik-kegiatan').on('click', function () {
        let indId = $('#realisasi_ind_id').val();
        let thn = $('#realisasi_ind_tahun').val();
        let tw = $('#realisasi_ind_triwulan').val();

        if (!indId) return;

        let btn = $(this);
        let origHtml = btn.html();
        btn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm me-1"></span> Menghitung...');

        $.post(AppConfig.initGlobal + 'fetch/ikk-calc-kegiatan-realisasi', { indikator_id: indId, tahun: thn, triwulan: tw }, function (res) {
            btn.prop('disabled', false).html(origHtml);
            if (res.status === 'success' && res.has_kegiatan) {
                $('#realisasi_ind_progres').val(res.suggested_progres);
                if (res.suggested_realisasi > 0) {
                    $('#realisasi_ind_jumlah').val(res.suggested_realisasi);
                }
                Swal.fire({
                    icon: 'success',
                    title: 'Capaian Ditarik',
                    text: `${res.message} Nilai progres terisi: ${res.suggested_progres}%.`,
                    timer: 2000,
                    showConfirmButton: false
                });
            } else {
                Swal.fire({
                    icon: 'info',
                    title: 'Informasi',
                    text: res.message || 'Tidak ada log kegiatan yang dapat dihitung.'
                });
            }
        }).fail(function () {
            btn.prop('disabled', false).html(origHtml);
            Swal.fire({ icon: 'error', text: 'Gagal menghitung estimasi capaian dari kegiatan.' });
        });
    });

    // Submit Realisasi Indikator
    $('#form-realisasi-indikator').on('submit', function (e) {
        e.preventDefault();
        let btn = $('#btnSubmitRealisasiIndikator');
        btn.prop('disabled', true);
        let indId = $('#realisasi_ind_id').val();
        let curThn = $('#realisasi_ind_tahun').val();
        let curTw = $('#realisasi_ind_triwulan').val();

        $.post(AppConfig.initGlobal + 'store/ikk-indikator-realisasi', $(this).serialize(), function (res) {
            btn.prop('disabled', false);
            if (res.status === 'success') {
                Swal.fire({ icon: 'success', title: 'Berhasil', text: res.message, timer: 1500, showConfirmButton: false });
                dtIndikator.ajax.reload(null, false);
                loadSummary();
                loadOptionsTahun();
                // Perbarui tabel log di dalam modal tanpa perlu menutup modal
                openRealisasiIndikatorModal(indId, curThn, curTw);
            } else {
                Swal.fire({ icon: 'error', title: 'Gagal', text: res.message });
            }
        }).fail(function () {
            btn.prop('disabled', false);
            Swal.fire({ icon: 'error', title: 'Kesalahan Sistem', text: 'Terjadi kesalahan saat menyimpan realisasi indikator.' });
        });
    });

    // -------------------------------------------------------------------------
    // 12. CRUD TRANSAKSI REALISASI HARIAN
    // -------------------------------------------------------------------------
    function updateModalTransaksiPeriodInfo() {
        let endDate = $('#transaksi_end_date').val() || $('#transaksi_start_date').val();
        if (endDate) {
            let parts = endDate.split('-');
            if (parts.length === 3) {
                let yr = parseInt(parts[0], 10);
                let m = parseInt(parts[1], 10);
                let tw = Math.ceil(m / 3);
                let badgeKey = `${yr}-TW${tw}`;
                $('#transaksi_periode_detected').html(`
                    <span class="badge bg-primary text-white border shadow-sm">
                        <i class="bi bi-calendar-check me-1"></i>Tahun ${yr} &bull; Triwulan ${tw} (${badgeKey})
                    </span>
                `);
                return;
            }
        }
        $('#transaksi_periode_detected').html(`
            <span class="badge bg-light text-primary border"><i class="bi bi-calendar-event me-1"></i>Periode terpetakan otomatis</span>
        `);
    }

    $('#transaksi_start_date, #transaksi_end_date').on('change input', updateModalTransaksiPeriodInfo);

    function openTransaksiModal(kegiatanId = null) {
        $('#form-transaksi')[0].reset();
        $('#transaksi_key').val('');
        $('#transaksi_kegiatan_warning').text('');
        updateModalTransaksiPeriodInfo();

        loadOptionsKegiatan('#transaksi_kegiatan_id', null, kegiatanId, function () {
            $('#modal-transaksi-title').text('Input Transaksi Realisasi Harian');
            $('#modal-transaksi').modal('show');
        });
    }

    $('.btn-add-transaksi').on('click', function () {
        let currentFilterKeg = $('#filter-transaksi-kegiatan').val();
        openTransaksiModal(currentFilterKeg);
    });

    $('#table-kegiatan').on('click', '.btn-add-transaksi-inline', function () {
        let kegiatanId = $(this).data('id');
        openTransaksiModal(kegiatanId);
    });

    $('#transaksi_kegiatan_id').on('change', function () {
        let selectedOpt = $(this).find('option:selected');
        let hasPengampu = selectedOpt.data('has-pengampu');
        if (hasPengampu === 0) {
            $('#transaksi_kegiatan_warning').html('<span class="text-danger fw-semibold"><i class="bi bi-exclamation-triangle me-1"></i> Peringatan: Kegiatan ini belum memiliki Tim Kerja Pengampu. Harap tentukan Tim Pengampu terlebih dahulu.</span>');
        } else {
            $('#transaksi_kegiatan_warning').text('');
        }
    });

    $('#table-transaksi').on('click', '.btn-edit-transaksi', function () {
        let tr = $(this).closest('tr');
        let row = dtTransaksi.row(tr.hasClass('child') ? tr.prev() : tr).data();
        if (!row) return;
        $('#transaksi_key').val(row.id);
        $('#transaksi_pelaksana').val(row.pelaksana_kegiatan_harian);
        $('#transaksi_start_date').val(row.period_start_date);
        $('#transaksi_end_date').val(row.period_end_date);
        $('#transaksi_target_mingguan').val(row.target_mingguan);
        $('#transaksi_jumlah_realisasi').val(row.jumlah_realisasi);
        $('#transaksi_progres').val(row.progres_realisasi);
        $('#transaksi_bukti').val(row.bukti_pekerjaan);
        updateModalTransaksiPeriodInfo();

        loadOptionsKegiatan('#transaksi_kegiatan_id', null, row.kegiatan_id, function () {
            $('#modal-transaksi-title').text('Edit Transaksi Realisasi Harian');
            $('#modal-transaksi').modal('show');
        });
    });

    $('#form-transaksi').on('submit', function (e) {
        e.preventDefault();
        $.post(AppConfig.initGlobal + 'store/ikk-transaksi', $(this).serialize(), function (res) {
            if (res.status === 'success') {
                Swal.fire({ icon: 'success', text: res.message, timer: 1500, showConfirmButton: false });
                $('#modal-transaksi').modal('hide');
                dtTransaksi.ajax.reload(null, false);
                dtKegiatan.ajax.reload(null, false);
                loadSummary();
                loadOptionsTahun();
            } else {
                Swal.fire({ icon: 'error', title: 'Gagal', text: res.message });
            }
        });
    });

    $('#table-transaksi').on('click', '.btn-delete-transaksi', function () {
        let id = $(this).data('id');
        Swal.fire({
            title: 'Hapus Transaksi Harian?',
            text: 'Data transaksi realisasi ini akan dihapus secara permanen.',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonText: 'Ya, Hapus',
            cancelButtonText: 'Batal'
        }).then((result) => {
            if (result.isConfirmed) {
                $.post(AppConfig.initGlobal + 'kill/ikk-transaksi', { key: id }, function (res) {
                    if (res.status === 'success') {
                        Swal.fire({ icon: 'success', text: res.message, timer: 1500, showConfirmButton: false });
                        dtTransaksi.ajax.reload(null, false);
                        dtKegiatan.ajax.reload(null, false);
                        loadSummary();
                        loadOptionsTahun();
                    } else {
                        Swal.fire({ icon: 'error', title: 'Gagal', text: res.message });
                    }
                });
            }
        });
    });

    $('#table-transaksi').on('click', '.js-view-bukti', function () {
        let bukti = decodeURIComponent($(this).data('bukti') || '');
        Swal.fire({
            title: 'Bukti Dukung Pekerjaan',
            text: bukti,
            icon: 'info',
            confirmButtonText: 'Tutup'
        });
    });

    // -------------------------------------------------------------------------
    // 12. IMPORT TRANSAKSI VIA EXCEL (DROPZONE)
    // -------------------------------------------------------------------------
    let selectedFileTransaksi = null;
    const dropzoneArea = document.getElementById('dropzoneAreaTransaksi');
    const btnBrowse = document.getElementById('btnBrowseExcelTransaksi');
    const excelUpload = document.getElementById('excelUploadTransaksi');
    const filePreview = document.getElementById('filePreviewTransaksi');
    const fileNameDisplay = document.getElementById('fileNameTransaksi');
    const fileSizeDisplay = document.getElementById('fileSizeTransaksi');
    const btnRemoveFile = document.getElementById('btnRemoveFileTransaksi');
    const formImportTransaksi = document.getElementById('form-import-transaksi');

    function formatBytes(bytes, decimals = 2) {
        if (!bytes || bytes === 0) return '0 Bytes';
        const k = 1024;
        const dm = decimals < 0 ? 0 : decimals;
        const sizes = ['Bytes', 'KB', 'MB', 'GB'];
        const i = Math.floor(Math.log(bytes) / Math.log(k));
        return parseFloat((bytes / Math.pow(k, i)).toFixed(dm)) + ' ' + sizes[i];
    }

    function showImportFilePreview(file) {
        if (!fileNameDisplay || !filePreview) return;
        fileNameDisplay.textContent = file.name;
        fileSizeDisplay.textContent = formatBytes(file.size);
        filePreview.classList.remove('d-none');
        if (btnBrowse) btnBrowse.style.display = 'none';
        if (dropzoneArea) {
            $(dropzoneArea).find('h6, p:not(#fileSizeTransaksi), .bi-cloud-arrow-up').hide();
        }
    }

    function resetImportDropzone() {
        selectedFileTransaksi = null;
        if (excelUpload) excelUpload.value = '';
        if (filePreview) filePreview.classList.add('d-none');
        if (btnBrowse) btnBrowse.style.display = 'inline-block';
        if (dropzoneArea) {
            $(dropzoneArea).find('h6, p, .bi-cloud-arrow-up').show();
            dropzoneArea.style.borderColor = '#0d6efd';
            dropzoneArea.style.backgroundColor = '#f8fafc';
        }
    }

    function handleImportFiles(files) {
        if (!files || files.length === 0) return;
        const file = files[0];
        const validExtensions = ['application/vnd.ms-excel', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet'];
        const validNames = /\.(xls|xlsx)$/i;

        if (!validExtensions.includes(file.type) && !validNames.test(file.name)) {
            Swal.fire({
                icon: 'error',
                title: 'Format File Tidak Didukung',
                text: 'Hanya file lembar kerja Excel (.xls atau .xlsx) yang diperbolehkan.'
            });
            return;
        }

        // Limit 10MB
        if (file.size > 10 * 1024 * 1024) {
            Swal.fire({
                icon: 'error',
                title: 'Ukuran File Terlalu Besar',
                text: 'Ukuran file maksimal yang diizinkan adalah 10 MB.'
            });
            return;
        }

        selectedFileTransaksi = file;
        showImportFilePreview(file);
    }

    if (dropzoneArea) {
        ['dragenter', 'dragover'].forEach(eventName => {
            dropzoneArea.addEventListener(eventName, function (e) {
                e.preventDefault();
                e.stopPropagation();
                dropzoneArea.style.borderColor = '#0b5ed7';
                dropzoneArea.style.backgroundColor = '#e0edff';
            }, false);
        });

        ['dragleave', 'dragend'].forEach(eventName => {
            dropzoneArea.addEventListener(eventName, function (e) {
                e.preventDefault();
                e.stopPropagation();
                dropzoneArea.style.borderColor = '#0d6efd';
                dropzoneArea.style.backgroundColor = '#f8fafc';
            }, false);
        });

        dropzoneArea.addEventListener('drop', function (e) {
            e.preventDefault();
            e.stopPropagation();
            dropzoneArea.style.borderColor = '#0d6efd';
            dropzoneArea.style.backgroundColor = '#f8fafc';
            const dt = e.dataTransfer;
            if (dt && dt.files) {
                handleImportFiles(dt.files);
            }
        }, false);

        // Clicking on dropzone triggers file picker when preview is hidden
        dropzoneArea.addEventListener('click', function (e) {
            if (filePreview && !filePreview.classList.contains('d-none')) {
                return;
            }
            if (excelUpload) excelUpload.click();
        });
    }

    if (btnBrowse) {
        btnBrowse.addEventListener('click', function (e) {
            e.stopPropagation();
            if (excelUpload) excelUpload.click();
        });
    }

    if (excelUpload) {
        excelUpload.addEventListener('change', function () {
            handleImportFiles(this.files);
        });
    }

    if (btnRemoveFile) {
        btnRemoveFile.addEventListener('click', function (e) {
            e.stopPropagation();
            resetImportDropzone();
        });
    }

    $('#modal-import-transaksi').on('hidden.bs.modal', function () {
        resetImportDropzone();
        if (formImportTransaksi) formImportTransaksi.reset();
    });

    $('#form-import-transaksi').on('submit', function (e) {
        e.preventDefault();

        if (!selectedFileTransaksi) {
            Swal.fire({
                icon: 'warning',
                title: 'File Belum Dipilih',
                text: 'Silakan pilih atau seret file Excel ke area upload terlebih dahulu.'
            });
            return;
        }

        const fd = new FormData();
        fd.append('file_excel', selectedFileTransaksi, selectedFileTransaksi.name);

        Swal.fire({
            title: 'Memproses Import Data...',
            text: 'Sistem sedang membaca dan memvalidasi rekaman transaksi. Mohon tunggu sejenak.',
            allowOutsideClick: false,
            didOpen: () => {
                Swal.showLoading();
            }
        });

        $.ajax({
            url: AppConfig.initGlobal + 'store/ikk-transaksi-import',
            type: 'POST',
            data: fd,
            processData: false,
            contentType: false,
            dataType: 'json'
        }).done(function (res) {
            if (res.status === 'success') {
                $('#modal-import-transaksi').modal('hide');
                resetImportDropzone();
                dtTransaksi.ajax.reload(null, false);
                dtKegiatan.ajax.reload(null, false);
                loadSummary();
                loadOptionsTahun();
                Swal.fire({
                    icon: 'success',
                    title: 'Import Selesai',
                    text: res.message,
                    confirmButtonText: 'OK'
                });
            } else if (res.status === 'warning') {
                $('#modal-import-transaksi').modal('hide');
                resetImportDropzone();
                dtTransaksi.ajax.reload(null, false);
                dtKegiatan.ajax.reload(null, false);
                loadSummary();
                loadOptionsTahun();
                let warningItems = Array.isArray(res.warnings) ? res.warnings.map(w => `<li>${w}</li>`).join('') : '';
                Swal.fire({
                    icon: 'warning',
                    title: 'Import Sebagian Berhasil',
                    html: `
                        <p class="mb-2">${res.message}</p>
                        ${warningItems ? `
                            <div class="text-start mt-2 p-2 bg-light border rounded small" style="max-height: 160px; overflow-y: auto;">
                                <strong>Catatan Baris:</strong>
                                <ul class="ps-3 mb-0 mt-1">${warningItems}</ul>
                            </div>
                        ` : ''}
                    `,
                    confirmButtonText: 'Tutup'
                });
            } else {
                let errorDetails = '';
                if (Array.isArray(res.warnings) && res.warnings.length > 0) {
                    errorDetails = `
                        <div class="text-start mt-2 p-2 bg-light border rounded small" style="max-height: 160px; overflow-y: auto;">
                            <strong>Detail Kendala:</strong>
                            <ul class="ps-3 mb-0 mt-1">${res.warnings.map(w => `<li>${w}</li>`).join('')}</ul>
                        </div>
                    `;
                }
                Swal.fire({
                    icon: 'error',
                    title: 'Import Gagal',
                    html: `<p class="mb-1">${res.message || 'Terjadi kesalahan saat memproses file Excel.'}</p>${errorDetails}`,
                    confirmButtonText: 'Periksa Kembali'
                });
            }
        }).fail(function (xhr) {
            let errorMsg = 'Terjadi kesalahan pada server saat mengunggah file Excel.';
            if (xhr.responseJSON && xhr.responseJSON.message) {
                errorMsg = xhr.responseJSON.message;
            }
            Swal.fire({
                icon: 'error',
                title: 'Gagal Terhubung',
                text: errorMsg,
                confirmButtonText: 'Tutup'
            });
        });
    });

    // -------------------------------------------------------------------------
    // 13. MATRIKS MONITORING (HIERARCHY TREE)
    // -------------------------------------------------------------------------
    function loadHierarchyTree() {
        let container = $('#hierarchy-tree-container');
        container.html('<div class="text-center py-4 text-dark ikk-text-dark"><div class="spinner-border spinner-border-sm text-primary me-2"></div>Memuat struktur pohon IKK...</div>');

        let filterData = getGlobalPeriodFilter();
        $.post(AppConfig.initGlobal + 'fetch/ikk-hierarchy', filterData, function (res) {
            if (res.status === 'success' && Array.isArray(res.data) && res.data.length > 0) {
                let html = '';
                res.data.forEach((sasaran, sIdx) => {
                    let totalInd = sasaran.indikators ? sasaran.indikators.length : 0;
                    html += `
                        <div class="tree-sasaran-block">
                            <div class="tree-sasaran-header" data-bs-toggle="collapse" data-bs-target="#tree-sas-${sasaran.id}">
                                <div>
                                    <span class="badge bg-dark me-2">Sasaran #${sasaran.no_urut || (sIdx + 1)}</span>
                                    <span class="fw-bold text-dark ikk-text-dark">${sasaran.nama_sasaran}</span>
                                </div>
                                <div>
                                    <span class="badge bg-light text-dark ikk-text-dark border me-2">${totalInd} Indikator</span>
                                    <i class="bi bi-chevron-down text-dark ikk-text-dark"></i>
                                </div>
                            </div>
                            <div class="collapse show" id="tree-sas-${sasaran.id}">
                                <div class="p-3">
                    `;

                    if (totalInd === 0) {
                        html += `<div class="text-muted small ps-3">Belum ada indikator terdaftar.</div>`;
                    } else {
                        sasaran.indikators.forEach((indikator, iIdx) => {
                            let totalKeg = indikator.kegiatans ? indikator.kegiatans.length : 0;
                            html += `
                                <div class="tree-indikator-block">
                                    <div class="d-flex justify-content-between align-items-center mb-1">
                                        <div class="fw-bold text-dark ikk-text-dark">
                                            Indikator #${indikator.no_urut || (iIdx + 1)}: ${indikator.nama_indikator}
                                        </div>
                                        <span class="badge bg-light text-dark ikk-text-dark border small">${indikator.target_volume || '-'} ${indikator.satuan || ''}</span>
                                    </div>
                            `;

                            if (totalKeg === 0) {
                                html += `<div class="text-muted small ps-2 mb-2">Belum ada kegiatan terdaftar.</div>`;
                            } else {
                                indikator.kegiatans.forEach((kegiatan, kIdx) => {
                                    let pengampuBadges = '';
                                    if (kegiatan.pengampus && kegiatan.pengampus.length > 0) {
                                        pengampuBadges = kegiatan.pengampus.map(p => `<span class="badge bg-light text-dark ikk-text-dark border me-1">${p.nama_timkerja}</span>`).join('');
                                    } else {
                                        pengampuBadges = `<span class="badge bg-danger-subtle text-danger border border-danger-subtle">Belum Ada Pengampu</span>`;
                                    }

                                    let totalTrx = kegiatan.transaksis ? kegiatan.transaksis.length : 0;
                                    let latestTrx = (totalTrx > 0) ? kegiatan.transaksis[0] : null;
                                    let progBadge = '';
                                    if (latestTrx && latestTrx.progres_realisasi !== null) {
                                        let pVal = parseFloat(latestTrx.progres_realisasi);
                                        let pColor = pVal >= 100 ? 'bg-success' : (pVal >= 50 ? 'bg-primary' : 'bg-warning text-dark');
                                        progBadge = `<span class="badge ${pColor} ms-1" title="Capaian Terakhir">${pVal}%</span>`;
                                    }

                                    html += `
                                        <div class="tree-kegiatan-block">
                                            <div class="d-flex justify-content-between align-items-start">
                                                <div>
                                                    <span class="fw-semibold text-dark ikk-text-dark">Kegiatan #${kegiatan.no_urut || (kIdx + 1)}: ${kegiatan.nama_kegiatan}</span>
                                                    <div class="mt-1">${pengampuBadges}</div>
                                                </div>
                                                <div class="text-end text-nowrap">
                                                    <span class="badge bg-light text-dark ikk-text-dark border">${totalTrx} Transaksi</span>
                                                    ${progBadge}
                                                </div>
                                            </div>
                                        </div>
                                    `;
                                });
                            }
                            html += `</div>`;
                        });
                    }
                    html += `
                                </div>
                            </div>
                        </div>
                    `;
                });
                container.html(html);
            } else {
                container.html('<div class="alert alert-light text-center border py-4 text-dark ikk-text-dark">Belum ada data struktur IKK yang dapat ditampilkan.</div>');
            }
        }).fail(function () {
            container.html('<div class="alert alert-danger text-center">Gagal memuat struktur pohon hierarki.</div>');
        });
    }

    $('#btn-reload-tree').on('click', function () {
        loadHierarchyTree();
    });

    $('#btn-expand-all-tree').on('click', function () {
        $('#hierarchy-tree-container .collapse').collapse('show');
    });

    $('#btn-collapse-all-tree').on('click', function () {
        $('#hierarchy-tree-container .collapse').collapse('hide');
    });

});
