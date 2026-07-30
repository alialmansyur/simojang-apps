const emptyStateAnggaran = `
    <div class="anggaran-empty-state">
        <p class="mb-0">Data realisasi anggaran belum tersedia.</p>
    </div>
`;

const processingStateAnggaran = (window.ServiceTableUI && ServiceTableUI.createProcessingState)
    ? ServiceTableUI.createProcessingState('Memuat data anggaran...')
    : '<div class="text-center text-muted py-4">Memuat data...</div>';

window.dtAnggaran = $('#dataTableAnggaran').DataTable({
    responsive: {
        details: { type: 'column', target: 'td.dtr-control' }
    },
    processing: true,
    serverSide: true,
    order: [[2, 'desc']],
    ajax: {
        url: AppConfig.initGlobal + 'fetch/data-anggaran',
        type: 'POST',
        data: function (d) {
            const filters = typeof getAnggaranFilterPayload === 'function'
                ? getAnggaranFilterPayload()
                : {};
            Object.assign(d, filters);
            return d;
        }
    },
    columnDefs: [{ className: 'dtr-control', targets: 0, orderable: false }],
    columns: [
        { data: null, defaultContent: '' },
        { data: 'tahun', className: 'text-center' },
        { data: 'period_date', className: 'text-center', render: (dt) => formatYearMonth(dt) },
        { data: 'no_spm', defaultContent: '-', className: 'text-nowrap' },
        { data: 'spm_date', className: 'text-center', render: (dt) => formatShortDate(dt) },
        { data: 'no_sp2d', defaultContent: '-', className: 'text-nowrap' },
        { data: 'sp2d_date', className: 'text-center', render: (dt) => formatShortDate(dt) },
        { data: 'item_count', className: 'text-center', render: (dt) => new Intl.NumberFormat('id-ID').format(Number(dt || 0)) },
        { data: 'total_nominal', className: 'text-end', render: (dt) => formatCurrency(dt) },
        {
            data: 'status',
            className: 'text-center',
            render: (dt) => {
                if (dt === 'POSTED') return '<span class="badge text-bg-success">POSTED</span>';
                if (dt === 'PENDING' || dt === 'DRAFT') return '<span class="badge text-bg-warning">PENDING</span>';
                return '<span class="badge text-bg-secondary">CANCEL</span>';
            }
        },
        { data: 'updated_at', className: 'text-center text-nowrap', render: (dt) => dt || '-' },
        {
            data: null,
            orderable: false,
            searchable: false,
            className: 'text-center',
            render: (_, __, row) => `
                <button class="btn btn-sm btn-outline-info btn-view-anggaran me-1" data-id="${row.id}" title="Lihat Detail">
                    <i class="bi bi-eye"></i>
                </button>
                <button class="btn btn-sm btn-primary btn-update-anggaran me-1" data-id="${row.id}">
                    <i class="bi bi-pencil"></i>
                </button>
                <button class="btn btn-sm btn-danger btn-remove-anggaran" data-id="${row.id}">
                    <i class="bi bi-trash"></i>
                </button>
            `
        }
    ],
    language: {
        emptyTable: (window.ServiceTableUI ? ServiceTableUI.createEmptyState() : 'Tidak ada data'),
        zeroRecords: (window.ServiceTableUI ? ServiceTableUI.createEmptyState() : 'Tidak ada data'),
        processing: processingStateAnggaran
    },
    createdRow: function (row, data) {
        if (data?.item_summary) {
            $(row).attr('title', String(data.item_summary).replace(/\|\|/g, '\n'));
        }
    },
    initComplete: function () {
        if (window.ServiceTableUI) {
            const tableApi = this.api();
            ServiceTableUI.setup({
                key: 'anggaran',
                table: tableApi,
                loadSummary: typeof loadSummaryAnggaran === 'function' ? loadSummaryAnggaran : null,
                reloadSummaryOnClick: false,
                processingText: 'Memuat data anggaran...'
            });
        }
    }
});

window.dtAnggaran.on('xhr.dt', function () {
    if (typeof loadSummaryAnggaran === 'function') {
        loadSummaryAnggaran();
    }
});

$('#dataTableAnggaran tbody').on('click', '.btn-update-anggaran', function () {
    let tr = $(this).closest('tr');
    if (tr.hasClass('child')) tr = tr.prev('.parent');

    const row = window.dtAnggaran.row(tr).data();
    if (!row) return;
    if (typeof openEditRealisasi === 'function') openEditRealisasi(row);
});

$('#dataTableAnggaran tbody').on('click', '.btn-view-anggaran', function () {
    let tr = $(this).closest('tr');
    if (tr.hasClass('child')) tr = tr.prev('.parent');

    const row = window.dtAnggaran.row(tr).data();
    if (!row) return;
    if (typeof openViewRealisasi === 'function') openViewRealisasi(row);
});

$('#dataTableAnggaran tbody').on('click', '.btn-remove-anggaran', function () {
    const key = Number($(this).data('id'));
    if (!key) return;

    confirmDelete('Hapus data realisasi ini?', function () {
        $.ajax({
            type: 'POST',
            dataType: 'json',
            url: AppConfig.initGlobal + 'kill/data-anggaran',
            data: { key },
            success: function (response) {
                if (response?.status === 'error') {
                    swlErrorHandler(response.message || 'Gagal menghapus data realisasi.');
                    return;
                }

                window.dtAnggaran.ajax.reload(null, false);
                swlSuccess(response?.message || 'Data realisasi berhasil dihapus.');
            },
            error: function () {
                swlErrorHandler('Terjadi kesalahan saat menghapus data realisasi.');
            }
        });
    });
});
