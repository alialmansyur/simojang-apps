let dtDetail = null;
let currentKey = null;
const detailProcessingState = (window.ServiceTableUI && ServiceTableUI.createProcessingState)
    ? ServiceTableUI.createProcessingState('Memuat detail E-Kinerja...')
    : '<div class="text-center text-muted py-4">Memuat data...</div>';

$('#dataTable tbody').on('click', '.btn-detail', function (e) {
    e.preventDefault();
    currentKey = Number($(this).data('id') || 0);
    if (!currentKey) {
        swlErrorHandler('Detail tidak dapat dimuat karena key data tidak valid.');
        return;
    }
    bootstrap.Modal.getOrCreateInstance('#fileDetailModal').show();
    if (!$.fn.DataTable.isDataTable('#dataTableDetail')) {
        initDetailTable();
        return;
    }
    dtDetail = $('#dataTableDetail').DataTable();
    dtDetail.ajax.reload(null, false);
});

function initDetailTable() {
    dtDetail = $('#dataTableDetail').DataTable({
        autoWidth: false,
        processing: true,
        serverSide: true,
        order: [[0, 'asc']],
        buttons: ['copy', 'excel', 'pdf', 'print'],
        ajax: {
            url: AppConfig.initGlobal + 'fetch/detail-ekin',
            type: 'POST',
            data: function (d) {
                d.key = currentKey;
            },
            error: function () {
                swlErrorHandler('Gagal memuat detail data E-Kinerja.');
            }
        },
        columns: [
            { data: 'nip', className: 'text-center' },
            { data: 'nama' },
            { data: 'waktu', className: 'text-center' },
            { data: 'kegiatan' },
            { data: 'realisasi', className: 'text-center' },
            { data: 'created_by', className: 'text-center' },
            { data: 'created_at', className: 'text-center' }
        ],
        language: {
            processing: detailProcessingState
        },
        drawCallback: function () {
            dtDetail.columns.adjust();
        }
    });
}

$('#fileDetailModal').on('hidden.bs.modal', function () {
    if (dtDetail) {
        dtDetail.columns.adjust();
    }
});
