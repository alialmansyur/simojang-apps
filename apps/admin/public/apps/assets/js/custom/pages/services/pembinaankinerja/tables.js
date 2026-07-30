(function (window, $) {
    'use strict';

    if (!window || !$ || !$.fn || !$.fn.DataTable) return;

    const PKApp = window.PKApp || (window.PKApp = {});
    const processingState = ServiceTableUI.createProcessingState('Memuat data pembinaan kinerja...');

    function badgeStatus(name, warna) {
        const tone = warna || 'secondary';
        const label = name || '-';
        return `<span class="badge text-bg-${tone}">${label}</span>`;
    }

    function formatPercent(value) {
        const num = Number(value || 0);
        return `${new Intl.NumberFormat('id-ID', {
            minimumFractionDigits: 0,
            maximumFractionDigits: 2
        }).format(num)}%`;
    }

    function getCategoryVisual(code = '') {
        const key = String(code || '').toUpperCase();

        if (key === 'PELAPORAN_SKP') {
            return {
                tone: 'tone-1',
                icon: '<svg viewBox="0 0 24 24" aria-hidden="true"><rect x="4" y="3" width="16" height="18" rx="2"/><path d="M8 8h8"/><path d="M8 12h8"/><path d="M8 16h5"/></svg>'
            };
        }

        if (key === 'PENYUSUNAN_SKP') {
            return {
                tone: 'tone-2',
                icon: '<svg viewBox="0 0 24 24" aria-hidden="true"><path d="M4 6h16"/><path d="M4 12h10"/><path d="M4 18h7"/><path d="M17 12l2 2 3-3"/></svg>'
            };
        }

        if (key === 'PEMANFAATAN_EKINERJA') {
            return {
                tone: 'tone-3',
                icon: '<svg viewBox="0 0 24 24" aria-hidden="true"><rect x="3" y="4" width="18" height="14" rx="2"/><path d="M8 20h8"/><path d="M10 18v2"/><path d="M14 18v2"/></svg>'
            };
        }

        return {
            tone: 'tone-1',
            icon: '<svg viewBox="0 0 24 24" aria-hidden="true"><circle cx="12" cy="12" r="9"/><path d="M12 7v5l3 2"/></svg>'
        };
    }

    function ensureOverviewPlacement() {
        const $overview = $('#pkCategoryOverview');
        if (!$overview.length) return;

        const $cardBody = $overview.closest('.card-body');
        const $topbar = $cardBody.find('.service-ui-static-topbar.pk-topbar').first();
        const $tableWrap = $cardBody.find('.table-responsive').first();

        if (!$topbar.length || !$tableWrap.length) return;

        // Force overview between topbar and table, even if global UI script reorders nodes.
        $overview.insertAfter($topbar);
        $overview.insertBefore($tableWrap);
    }

    function renderCategoryOverview(summary = {}, items = []) {
        const $el = $('#pkCategoryOverview');
        if (!$el.length) return;

        const total = Number(summary.total_data || 0);
        const avg = Number(summary.avg_capaian || 0);
        const lastUpdate = summary.last_update || null;
        const globalCards = `
            <div class="pk-overview-card tone-1">
                <span class="pk-overview-icon">
                    <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M4 7h16"/><path d="M7 3v4"/><path d="M17 3v4"/><rect x="4" y="7" width="16" height="14" rx="2"/></svg>
                </span>
                <div class="pk-overview-body">
                    <p class="pk-overview-label">Total Data</p>
                    <h6 class="pk-overview-value">${new Intl.NumberFormat('id-ID').format(total)}</h6>
                </div>
            </div>
            <div class="pk-overview-card tone-2">
                <span class="pk-overview-icon">
                    <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M3 3v18h18"/><path d="M8 14l3-3 3 2 4-5"/></svg>
                </span>
                <div class="pk-overview-body">
                    <p class="pk-overview-label">Rata-rata Capaian</p>
                    <h6 class="pk-overview-value">${formatPercent(avg)}</h6>
                </div>
            </div>
            <div class="pk-overview-card tone-3">
                <span class="pk-overview-icon">
                    <svg viewBox="0 0 24 24" aria-hidden="true"><circle cx="12" cy="12" r="9"/><path d="M12 7v5l3 2"/></svg>
                </span>
                <div class="pk-overview-body">
                    <p class="pk-overview-label">Update Terakhir</p>
                    <h6 class="pk-overview-value pk-overview-value-sm">${lastUpdate ? ServiceTableUI.formatDateTime(lastUpdate) : '-'}</h6>
                </div>
            </div>
        `;

        const categoryCards = items.map((item) => {
            const visual = getCategoryVisual(item.code);
            return `
            <div class="pk-overview-card ${visual.tone}">
                <span class="pk-overview-icon">${visual.icon}</span>
                <div class="pk-overview-body">
                    <p class="pk-overview-label">${item.nama}</p>
                    <h6 class="pk-overview-value">${item.total || 0} instansi</h6>
                </div>
            </div>
        `;
        }).join('');

        $el.html(globalCards + categoryCards);
        ensureOverviewPlacement();
    }

    function loadSummary() {
        return $.ajax({
            url: AppConfig.initGlobal + 'fetch/summary-pembinaan-kinerja',
            type: 'POST',
            dataType: 'json',
            data: {
                period_year: PKApp.selectedYear,
                kategori_id: PKApp.selectedCategory,
                period_months: PKApp.selectedMonths || []
            }
        }).done((response) => {
            if (!response || response.status !== 'success') return;

            const summary = response.summary || {};
            renderCategoryOverview(summary, response.kategori_breakdown || []);
        });
    }

    function initTable() {
        const emptyLottie = ServiceTableUI.createEmptyLottie();
        const table = $('#pkTable').DataTable({
            responsive: {
                details: { type: 'column', target: 'td.dtr-control' }
            },
            processing: true,
            serverSide: true,
            order: [[8, 'desc']],
            ajax: {
                url: AppConfig.initGlobal + 'fetch/data-pembinaan-kinerja',
                type: 'POST',
                data: function (d) {
                    d.period_year = PKApp.selectedYear;
                    d.kategori_id = PKApp.selectedCategory;
                    d.period_months = PKApp.selectedMonths || [];
                    return d;
                }
            },
            columnDefs: [{ className: 'dtr-control', targets: 0, orderable: false }],
            columns: [
                { data: null, defaultContent: '' },
                {
                    data: 'logo',
                    className: 'text-center',
                    render: function (data) {
                        if (data) {
                            return `<img src="apps/assets/images/instansi/${data}" alt="logo" style="height:20px;">`;
                        }
                        return '<span class="text-muted"><small>No Logo</small></span>';
                    }
                },
                { data: 'instansi_name' },
                { data: 'kategori_nama' },
                { data: 'period_year' },
                {
                    data: 'capaian_percent',
                    className: 'text-center fw-semibold',
                    render: function (data) {
                        return formatPercent(data);
                    }
                },
                {
                    data: null,
                    render: function (_data, _type, row) {
                        return badgeStatus(row.status_nama, row.status_warna);
                    }
                },
                {
                    data: 'pendampingan_date_label',
                    render: function (data) {
                        return data || '-';
                    }
                },
                { data: 'updated_at_label' },
                {
                    data: null,
                    orderable: false,
                    searchable: false,
                    render: function (_data, _type, row) {
                        return `
                            <button class="btn btn-sm btn-primary pk-btn-edit me-1" data-id="${row.id}">
                                <i class="bi bi-pencil"></i>
                            </button>
                            <button class="btn btn-sm btn-danger pk-btn-remove" data-id="${row.id}">
                                <i class="bi bi-trash"></i>
                            </button>
                        `;
                    }
                }
            ],
            language: {
                emptyTable: (window.ServiceTableUI ? ServiceTableUI.createEmptyState() : 'Tidak ada data'),
                zeroRecords: (window.ServiceTableUI ? ServiceTableUI.createEmptyState() : 'Tidak ada data'),
                processing: processingState
            }
        });

        PKApp.table = table;
        PKApp.refreshSummary = loadSummary;

        ServiceTableUI.setup({
            key: 'pk',
            table,
            cards: [
                { id: 'recap-total', label: 'Total Data', value: '0' },
                { id: 'recap-avg', label: 'Rata-rata Capaian', value: '0%' },
                { id: 'recap-update', label: 'Update Terakhir', value: '-' }
            ],
            loadSummary,
            reloadSummaryOnClick: false,
            processingText: 'Memuat data pembinaan kinerja...'
        });

        ensureOverviewPlacement();

        table.on('xhr.dt', function () {
            ensureOverviewPlacement();
            loadSummary();
        });

        loadSummary();
    }

    $(document).on('click', '#pkTable .pk-btn-remove', function () {
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
                url: AppConfig.initGlobal + 'kill/data-pembinaan-kinerja',
                data: { key },
                dataType: 'json'
            }).done((response) => {
                if (response?.status !== 'success') {
                    swlErrorHandler(response?.message || 'Gagal menghapus data.');
                    return;
                }

                swlSuccess();
                if (PKApp.table) PKApp.table.ajax.reload(null, false);
                if (typeof PKApp.refreshSummary === 'function') PKApp.refreshSummary();
            }).fail(() => {
                swlErrorHandler('Terjadi kendala saat menghapus data.');
            });
        });
    });

    $(document).on('click', '#pkTable .pk-btn-edit', function () {
        const key = Number($(this).data('id') || 0);
        if (!key || !PKApp.table) return;

        let tr = $(this).closest('tr');
        if (tr.hasClass('child')) tr = tr.prev('.parent');
        const row = PKApp.table.row(tr).data();
        if (!row) return;

        if (typeof PKApp.openEditModal === 'function') {
            PKApp.openEditModal(row);
        }
    });

    $(document).ready(function () {
        initTable();
    });
})(window, window.jQuery);
