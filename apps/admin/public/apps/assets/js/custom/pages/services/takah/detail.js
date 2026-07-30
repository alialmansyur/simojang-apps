let dtDetail = null;
let currentKey = null;
const detailProcessingState = (window.ServiceTableUI && ServiceTableUI.createProcessingState)
    ? ServiceTableUI.createProcessingState('Memuat detail TAKAH...')
    : '<div class="text-center text-muted py-4">Memuat data...</div>';

$('#dataTable tbody').on('click', '.btn-detail', function (e) {
    e.preventDefault();
    currentKey = $(this).data('id');
    bootstrap.Modal.getOrCreateInstance('#fileDetailModal').show();
    if (!dtDetail) initDetailTable();
    else dtDetail.ajax.reload(null, false);
});

function ynIcon(data) {
    return data === 'Y'
        ? '<i class="bi bi-check-circle-fill text-success"></i>'
        : '<i class="bi bi-exclamation-triangle-fill text-warning"></i>';
}   

function initDetailTable() {
    let selectedColumns = [
        { 
            data: 'logo',
            className: 'text-center',
            render: function(data, type, row) {
                if (data) {
                    return '<img src="apps/assets/images/instansi/' + data + '" alt="logo" style="height:20px;">';
                } else {
                    return '<span class="text-muted">No Logo</span>';
                }
            }
        },
        { data: 'kode_instansi', className: 'text-center' }, 
        { data: 'nama_instansi', render: (dt, t, r) => `<strong><a href="#" class="text-break" data-id="${r.id}">${dt}</a></strong>` },
        { data: 'nip', className: 'text-center' }, 
        { data: 'd2nip', className: 'text-center' }, 
        { data: 'ijazah', className: 'text-center' }, 
        { data: 'drh', className: 'text-center' }, 
        { data: 'cpns', className: 'text-center' }, 
        { data: 'pns', className: 'text-center' }, 
        { data: 'kp', className: 'text-center' }, 
        { data: 'jabatan', className: 'text-center' }, 
        { data: 'perubahan', className: 'text-center' }, 
        { data: 'berhenti', className: 'text-center' }, 
        { data: 'pensiun', className: 'text-center' }, 
        { data: 'upload_by'}, 
        { data: 'tanggal_upload'}, 
    ];

    dtDetail = $('#dataTableDetail').DataTable({
        autoWidth: false,
        processing: true,
        serverSide: true,
        order: [[1, 'asc']],
        dom: 'Bfrtip',
        buttons: ['copy','excel','pdf','print'],
        ajax: {
            url: AppConfig.initGlobal + 'fetch/detail-takah',
            type: 'POST',
            data: d => { d.key = currentKey; }
        },
        columns: selectedColumns,
        language: {
            processing: detailProcessingState
        },
        drawCallback: () => dtDetail.columns.adjust().responsive.recalc(),
    });
}

$('#fileDetailModal').on('hidden.bs.modal', () => dtDetail && dtDetail.columns.adjust());
