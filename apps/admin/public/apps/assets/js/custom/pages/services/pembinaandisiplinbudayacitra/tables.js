(function (window, $) {
    'use strict';

    if (!window || !$ || !$.fn || !$.fn.DataTable) return;

    const PDBCApp = window.PDBCApp || (window.PDBCApp = {});
    PDBCApp.detailById = PDBCApp.detailById || {};
    const processingState = ServiceTableUI.createProcessingState('Memuat data pembinaan disiplin...');

    function formatNumber(value) {
        return new Intl.NumberFormat('id-ID').format(Number(value || 0));
    }

    function getJenisVisual(jenis = '') {
        const key = String(jenis).toUpperCase();
        if (key === 'KONSULTASI') {
            return '<svg viewBox="0 0 24 24" aria-hidden="true"><path d="M4 6h16"/><path d="M4 12h10"/><path d="M4 18h7"/><path d="M17 12l2 2 3-3"/></svg>';
        }
        if (key === 'PEMBINAAN') {
            return '<svg viewBox="0 0 24 24" aria-hidden="true"><rect x="3" y="4" width="18" height="14" rx="2"/><path d="M8 20h8"/><path d="M10 18v2"/><path d="M14 18v2"/></svg>';
        }
        return '<svg viewBox="0 0 24 24" aria-hidden="true"><rect x="4" y="3" width="16" height="18" rx="2"/><path d="M8 8h8"/><path d="M8 12h8"/><path d="M8 16h5"/></svg>';
    }

    function renderCategoryOverview(items = []) {
        const $el = $('#pdbcCategoryOverview');
        if (!$el.length) return;

        if (!items.length) {
            $el.html('');
            return;
        }

        const cards = items.map((item) => `
            <div class="pdbc-overview-card">
                <span class="pdbc-overview-icon">${getJenisVisual(item.jenis_layanan)}</span>
                <div class="pdbc-overview-content">
                    <p>${item.jenis_layanan || '-'}</p>
                    <h6>${item.nama || '-'}</h6>
                    <small class="text-muted">${formatNumber(item.total_data || 0)} data</small>
                </div>
            </div>
        `).join('');

        $el.html(cards);
        ensureOverviewPlacement();
    }

    function ensureOverviewPlacement() {
        // Obsolete: #pdbcCategoryOverview is manually placed outside .card-body in main.php
    }

    function loadSummary() {
        return $.ajax({
            url: AppConfig.initGlobal + 'fetch/summary-pembinaan-disiplin-budaya-citra',
            type: 'POST',
            dataType: 'json',
            data: {
                period_year: PDBCApp.selectedYear,
                kategori_id: PDBCApp.selectedCategory,
                jenis_layanan: PDBCApp.selectedJenis,
                period_months: PDBCApp.selectedMonths || []
            }
        }).done((response) => {
            if (!response || response.status !== 'success') return;
            renderCategoryOverview(response.kategori_breakdown || []);
        });
    }

    function escapeHtml(str) {
        return String(str ?? '')
            .replaceAll('&', '&amp;')
            .replaceAll('<', '&lt;')
            .replaceAll('>', '&gt;')
            .replaceAll('"', '&quot;')
            .replaceAll("'", '&#039;');
    }

    function renderSourceLabel(value) {
        const map = {
            SURAT_MASUK: 'Surat Masuk',
            ZOOM: 'Zoom',
            PPT: 'PPT'
        };
        return map[value] || value || '-';
    }

    function buildDetailHtml(rows = []) {
        if (!rows.length) {
            return `<div class="px-2 py-2 text-muted small">Belum ada riwayat.</div>`;
        }

        const body = rows.map((row) => {
            const pegawai = row.pegawai_names ? String(row.pegawai_names).split('||').join(', ') : '-';
            const kegiatan = row.judul_kegiatan || '-';
            const tempat = row.tempat_kegiatan || '-';
            const surat = row.no_surat_kegiatan || '-';
            const source = renderSourceLabel(row.source_konsultasi);
            const catatan = row.catatan || '-';
            const dateLabel = row.period_date_label || '-';

            PDBCApp.detailById[String(row.id)] = row;

            return `
                <tr>
                    <td>${escapeHtml(dateLabel)}</td>
                    <td><span class="badge text-bg-primary">${escapeHtml(row.jenis_layanan || '-')}</span></td>
                    <td>${escapeHtml(row.kategori_nama || '-')}</td>
                    <td>${escapeHtml(source)}</td>
                    <td><div><strong>${escapeHtml(kegiatan)}</strong></div></td>
                    <td>${escapeHtml(pegawai)}</td>
                    <td>${escapeHtml(catatan)}</td>
                    <td class="text-center">
                        <button class="btn btn-sm btn-primary pdbc-btn-edit me-1" data-rowid="${escapeHtml(row.id)}">
                            <i class="bi bi-pencil"></i>
                        </button>
                        <button class="btn btn-sm btn-danger pdbc-btn-remove" data-id="${escapeHtml(row.id)}">
                            <i class="bi bi-trash"></i>
                        </button>
                    </td>
                </tr>
            `;
        }).join('');

        return `
            <div class="table-responsive">
                <table class="table table-sm table-bordered mb-0">
                    <thead>
                        <tr>
                            <th>Tanggal</th>
                            <th>Jenis</th>
                            <th>Kategori</th>
                            <th>Source</th>
                            <th>Kegiatan</th>
                            <th>Pegawai</th>
                            <th>Catatan</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody>${body}</tbody>
                </table>
            </div>
        `;
    }

    function loadDetailByInstansi(instansiId) {
        return $.ajax({
            url: AppConfig.initGlobal + 'fetch/detail-pembinaan-disiplin-budaya-citra',
            type: 'POST',
            dataType: 'json',
            data: {
                instansi_id: instansiId,
                period_year: PDBCApp.selectedYear,
                kategori_id: PDBCApp.selectedCategory,
                jenis_layanan: PDBCApp.selectedJenis,
                period_months: PDBCApp.selectedMonths || []
            }
        });
    }

    function initTable() {
        const emptyLottie = ServiceTableUI.createEmptyLottie();
        const table = $('#pdbcTable').DataTable({
            processing: true,
            serverSide: true,
            ordering: false,
            ajax: {
                url: AppConfig.initGlobal + 'fetch/data-pembinaan-disiplin-budaya-citra',
                type: 'POST',
                data: function (d) {
                    d.period_year = PDBCApp.selectedYear;
                    d.kategori_id = PDBCApp.selectedCategory;
                    d.jenis_layanan = PDBCApp.selectedJenis;
                    d.period_months = PDBCApp.selectedMonths || [];
                    return d;
                }
            },
            columnDefs: [{ className: 'text-center', targets: 0, orderable: false }],
            columns: [
                {
                    data: null,
                    render: function (_data, _type, row) {
                        return `<button class="btn btn-sm btn-light pdbc-expand" data-instansi="${row.instansi_id}"><i class="bi bi-plus"></i></button>`;
                    }
                },
                {
                    data: 'logo',
                    className: 'text-center',
                    render: function (data) {
                        if (data) {
                            return `<img src="apps/assets/images/instansi/${data}" alt="logo" style="height:20px;">`;
                        }
                        return '<span class="text-muted">No Logo</span>';
                    }
                },
                { data: 'instansi_name' },
                { data: 'total_riwayat', className: 'text-center fw-semibold' },
                { data: 'updated_at_label' }
            ],
            language: {
                emptyTable: (window.ServiceTableUI ? ServiceTableUI.createEmptyState() : 'Tidak ada data'),
                zeroRecords: (window.ServiceTableUI ? ServiceTableUI.createEmptyState() : 'Tidak ada data'),
                processing: processingState
            }
        });

        PDBCApp.table = table;
        PDBCApp.refreshSummary = loadSummary;

        ServiceTableUI.setup({
            key: 'pdbc',
            table,
            disableRecap: true,
            loadSummary,
            reloadSummaryOnClick: false,
            processingText: 'Memuat data pembinaan disiplin...'
        });

        table.on('xhr.dt', function () {
            ensureOverviewPlacement();
            PDBCApp.detailById = {};
            loadSummary();
        });

        ensureOverviewPlacement();
        loadSummary();
    }

    $(document).on('click', '#pdbcTable .pdbc-expand', function () {
        const $btn = $(this);
        const tr = $btn.closest('tr');
        const row = PDBCApp.table.row(tr);
        const instansiId = String($btn.data('instansi') || '');
        if (!instansiId) return;

        if (row.child.isShown()) {
            row.child.hide();
            tr.removeClass('shown');
            $btn.html('<i class="bi bi-plus"></i>');
            return;
        }

        $btn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm"></span>');
        loadDetailByInstansi(instansiId).done((response) => {
            const list = response?.status === 'success' && Array.isArray(response.list) ? response.list : [];
            row.child(buildDetailHtml(list)).show();
            tr.addClass('shown');
            $btn.html('<i class="bi bi-dash"></i>');
        }).fail(() => {
            row.child('<div class="px-2 py-2 text-danger small">Gagal memuat riwayat.</div>').show();
            tr.addClass('shown');
            $btn.html('<i class="bi bi-dash"></i>');
        }).always(() => {
            $btn.prop('disabled', false);
        });
    });

    $(document).on('click', '#pdbcTable .pdbc-btn-remove', function () {
        const key = Number($(this).data('id') || 0);
        if (!key) return;

        Swal.fire({
            text: 'Apa anda yakin akan menghapus data ini?',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d63031',
            confirmButtonText: 'Ya',
            cancelButtonText: 'Tidak'
        }).then((result) => {
            if (!result.isConfirmed) return;

            $.ajax({
                type: 'POST',
                url: AppConfig.initGlobal + 'kill/data-pembinaan-disiplin-budaya-citra',
                data: { key },
                dataType: 'json'
            }).done((response) => {
                if (response?.status !== 'success') {
                    swlErrorHandler(response?.message || 'Gagal menghapus data.');
                    return;
                }

                swlSuccess();
                if (PDBCApp.table) PDBCApp.table.ajax.reload(null, false);
                if (typeof PDBCApp.refreshSummary === 'function') PDBCApp.refreshSummary();
            }).fail(() => {
                swlErrorHandler('Terjadi kendala saat menghapus data.');
            });
        });
    });

    $(document).on('click', '.pdbc-btn-edit', function () {
        const key = String($(this).data('rowid') || $(this).data('id') || '');
        if (!key) return;
        const row = PDBCApp.detailById[key];
        if (!row) return;
        if (typeof PDBCApp.openEditModal === 'function') PDBCApp.openEditModal(row);
    });

    $(document).ready(function () {
        initTable();
    });
})(window, window.jQuery);
