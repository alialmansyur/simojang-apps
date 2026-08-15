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
        { data: 'created_at' },
        {
            data: null,
            orderable: false,
            render: function (data, type, row) {
                return `
                    <button class="btn btn-sm btn-primary btn-edit" data-id="${row.id}"><i class='bi bi-pencil'></i></button>
                    <button class="btn btn-sm btn-danger btn-delete" data-id="${row.id}"><i class='bi bi-trash'></i></button>
                `;
            }
        }
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

                loadSummary: loadSummaryAssets,
                cards: [
                    { id: 'total-data', label: 'Total Item', value: '0' },
                    { id: 'total-qty', label: 'Total Kuantitas', value: '0' },
                    { id: 'last-update', label: 'Update Terakhir', value: '-' }
                ]
            });
        }
        updateShownAssets();
        loadSummaryAssets();
    }
});
table.on('draw.dt', updateShownAssets);

// --- CRUD EVENT HANDLERS ---

$('#btnTambahData').on('click', function () {
    $('#formAssetDetail')[0].reset();
    $('#assetId').val('');
    $('#DataModalLabel').text('Tambah Data Asset');
    $('#formAssetDetail').attr('action', AppConfig.initGlobal + 'apps-manage-assets-detail/store');
});

$('#dataTable tbody').on('click', '.btn-edit', function () {
    const id = $(this).data('id');
    
    $.ajax({
        url: AppConfig.initGlobal + 'apps-manage-assets-detail/get-data',
        type: 'POST',
        dataType: 'json',
        data: { id: id },
        success: function (res) {
            if (res.status === 'success') {
                const data = res.data;
                $('#assetId').val(data.id);
                $('#kode').val(data.kode);
                $('#subcode').val(data.subcode);
                $('#uraian').val(data.uraian);
                $('#satuan').val(data.satuan);
                $('#qty').val(data.qty);
                
                $('#DataModalLabel').text('Edit Data Asset');
                $('#formAssetDetail').attr('action', AppConfig.initGlobal + 'apps-manage-assets-detail/update');
                $('#DataModal').modal('show');
            } else {
                Swal.fire('Error', res.message || 'Gagal memuat data', 'error');
            }
        },
        error: function () {
            Swal.fire('Error', 'Terjadi kesalahan pada server', 'error');
        }
    });
});

$('#formAssetDetail').on('submit', function (e) {
    e.preventDefault();
    const form = $(this);
    const url = form.attr('action');
    const formData = form.serialize();
    const btnSubmit = $('#btnSubmitForm');

    btnSubmit.prop('disabled', true).html('<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Menyimpan...');

    $.ajax({
        url: url,
        type: 'POST',
        dataType: 'json',
        data: formData,
        success: function (res) {
            btnSubmit.prop('disabled', false).text('Simpan Data');
            if (res.status === 'success') {
                $('#DataModal').modal('hide');
                Swal.fire({
                    icon: 'success',
                    title: 'Berhasil',
                    text: res.message,
                    timer: 1500,
                    showConfirmButton: false
                });
                table.ajax.reload(null, false);
                loadSummaryAssets();
            } else {
                Swal.fire('Gagal', res.message || 'Gagal menyimpan data', 'error');
            }
        },
        error: function () {
            btnSubmit.prop('disabled', false).text('Simpan Data');
            Swal.fire('Error', 'Terjadi kesalahan pada server', 'error');
        }
    });
});

$('#dataTable tbody').on('click', '.btn-delete', function () {
    const id = $(this).data('id');
    Swal.fire({
        title: 'Hapus Data?',
        text: "Data yang dihapus tidak dapat dikembalikan!",
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#d33',
        cancelButtonColor: '#3085d6',
        confirmButtonText: 'Ya, hapus!',
        cancelButtonText: 'Batal'
    }).then((result) => {
        if (result.isConfirmed) {
            Swal.fire({
                title: 'Menghapus...',
                allowOutsideClick: false,
                didOpen: () => { Swal.showLoading(); }
            });
            $.ajax({
                url: AppConfig.initGlobal + 'apps-manage-assets-detail/delete',
                type: 'POST',
                dataType: 'json',
                data: { id: id },
                success: function (res) {
                    if (res.status === 'success') {
                        Swal.fire('Terhapus!', res.message, 'success');
                        table.ajax.reload(null, false);
                        loadSummaryAssets();
                    } else {
                        Swal.fire('Gagal', res.message || 'Gagal menghapus data', 'error');
                    }
                },
                error: function () {
                    Swal.fire('Error', 'Terjadi kesalahan pada server', 'error');
                }
            });
        }
    });
});

