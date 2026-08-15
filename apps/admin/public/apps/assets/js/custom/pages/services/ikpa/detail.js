let dtDetail = null;
let currentKey = null;
const detailProcessingState = (window.ServiceTableUI && ServiceTableUI.createProcessingState)
    ? ServiceTableUI.createProcessingState('Memuat detail IKPA...')
    : '<div class="text-center text-muted py-4">Memuat data...</div>';

$('#dataTable tbody').on('click', '.btn-detail', function (e) {
    e.preventDefault();
    currentKey = $(this).data('id');
    bootstrap.Modal.getOrCreateInstance('#fileDetailModal').show();
    if (!dtDetail) initDetailTable();
    else dtDetail.ajax.reload(null, false);
});

function initDetailTable() {
    dtDetail = $('#dataTableDetail').DataTable({
        autoWidth: false,
        processing: true,
        serverSide: true,
        order: [[0, 'asc']],
        buttons: ['copy', 'excel', 'pdf', 'print'],
        ajax: {
            url: AppConfig.initGlobal + 'fetch/detail-ikpa',
            type: 'POST',
            data: function (d) {
                d.key = currentKey;
            }
        },
        columns: [
            { data: 'kelompok' },
            { data: 'nama_indikator' },
            { data: 'nilai', className: 'text-center' },
            { data: 'bobot', className: 'text-center' },
            { data: 'nilai_akhir', className: 'text-center' }
        ],
        language: {
            processing: detailProcessingState
        },
        drawCallback: function () {
            dtDetail.columns.adjust().responsive.recalc();
        }
    });
}

$('#fileDetailModal').on('hidden.bs.modal', function () {
    if (dtDetail) {
        dtDetail.columns.adjust();
    }
});
