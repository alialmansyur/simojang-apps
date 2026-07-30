const emptyLottie = (window.ServiceTableUI && ServiceTableUI.createEmptyLottie)
    ? ServiceTableUI.createEmptyLottie()
    : '<div class="text-center text-muted py-5">Tidak ada data.</div>';
const processingState = (window.ServiceTableUI && ServiceTableUI.createProcessingState)
    ? ServiceTableUI.createProcessingState('Memuat data asset...')
    : '<div class="text-center text-muted py-4">Memuat data...</div>';

const categoryUid = $('#category_uid').val();

function updateShownAssets() {
    const info = table.page.info();
    $('#asset-data-shown').text(ServiceTableUI.formatNumber((info && info.recordsDisplay) || 0));
}

function loadSummaryAssets() {
    $.ajax({
        url: AppConfig.initGlobal + 'fetch/summary-manage-assets',
        type: 'POST',
        dataType: 'json',
        data: { category_uid: categoryUid },
        success: function (response) {
            const s = response?.summary || {};
            $('#asset-total-data').text(ServiceTableUI.formatNumber(s.total_data || 0));
            $('#asset-total-qty').text(ServiceTableUI.formatNumber(s.total_qty || 0));
            $('#asset-last-update').text(ServiceTableUI.formatDateTime(s.last_update));
        }
    });
}

const table = $('#dataTable').DataTable({
    responsive: { details: { type: 'column', target: 'td.dtr-control' } },
    processing: true,
    serverSide: true,
    order: [[6, 'desc']], // order by created_date desc initially
    dom: 'Bfrtip',
    buttons: ['copy', 'excel', 'pdf', 'print'],
    ajax: {
        url: AppConfig.initGlobal + 'fetch/data-manage-assets',
        type: 'POST',
        data: function (d) { d.category_uid = categoryUid; }
    },
    columnDefs: [{ className: 'dtr-control', targets: 0, orderable: false }],
    columns: [
        { data: null, defaultContent: '' },
        { data: 'kode' },
        { data: 'subcode' },
        { data: 'uraian' },
        { data: 'satuan' },
        { data: 'qty', render: $.fn.dataTable.render.number(',', '.', 2) },
        { data: 'created_at' }
    ],
    language: {
        emptyTable: (window.ServiceTableUI ? ServiceTableUI.createEmptyState() : 'Tidak ada data'),
        zeroRecords: (window.ServiceTableUI ? ServiceTableUI.createEmptyState() : 'Tidak ada data'),
        processing: processingState
    },
    initComplete: function () {
        if (window.ServiceTableUI) {
            ServiceTableUI.setup({
                key: 'asset',
                table,
                recapMountSelector: '.page-heading',
                loadSummary: loadSummaryAssets,
                cards: [
                    { id: 'total-data', label: 'Total Item', value: '0' },
                    { id: 'total-qty', label: 'Total Kuantitas', value: '0' },
                    { id: 'data-shown', label: 'Data Ditampilkan', value: '0' },
                    { id: 'last-update', label: 'Update Terakhir', value: '-' }
                ]
            });
        }
        updateShownAssets();
        loadSummaryAssets();
    }
});
table.on('draw.dt', updateShownAssets);
