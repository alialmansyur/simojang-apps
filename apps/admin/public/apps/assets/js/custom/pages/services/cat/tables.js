const emptyLottie = (window.ServiceTableUI && ServiceTableUI.createEmptyLottie)
    ? ServiceTableUI.createEmptyLottie()
    : '<div class="text-center text-muted py-5">Tidak ada data.</div>';
const processingState = (window.ServiceTableUI && ServiceTableUI.createProcessingState)
    ? ServiceTableUI.createProcessingState('Memuat data CAT...')
    : '<div class="text-center text-muted py-4">Memuat data...</div>';

function updateShownCAT() {
    const info = table.page.info();
    $('#cat-data-shown').text(ServiceTableUI.formatNumber((info && info.recordsDisplay) || 0));
}

function loadSummaryTilok() {
    $.ajax({
        url: AppConfig.initGlobal + 'fetch/summary-tilok-cat',
        type: 'POST',
        dataType: 'json',
        data: { seleksi_uid: typeof SELEKSI_UID !== 'undefined' ? SELEKSI_UID : '' },
        success: function (response) {
            const s = response?.summary || {};
            $('#cat-total-tilok').text(ServiceTableUI.formatNumber(s.total_tilok || 0));
            $('#cat-total-kapasitas').text(ServiceTableUI.formatNumber(s.total_kapasitas || 0));
            $('#cat-total-jenis').text(ServiceTableUI.formatNumber(s.total_jenis_tes || 0));
            $('#cat-total-periode').text(ServiceTableUI.formatNumber(s.total_periode || 0));
            $('#cat-last-update').text(ServiceTableUI.formatDateTime(s.last_update));
        }
    });
}

const table = $('#dataTable').DataTable({
    responsive: {
        details: { type: 'column', target: 'td.dtr-control' }
    },
    processing: true,
    serverSide: true,
    order: [[1, 'asc']],
    buttons: ['copy', 'excel', 'pdf', 'print'],
    ajax: {
        url: AppConfig.initGlobal + 'fetch/data-tilok-cat',
        type: 'POST',
        data: function (d) {
            d.seleksi_uid = typeof SELEKSI_UID !== 'undefined' ? SELEKSI_UID : '';
            return d;
        },
        error: function (xhr) {
            const message = xhr.responseJSON && xhr.responseJSON.error
                ? xhr.responseJSON.error
                : 'Gagal memuat data CAT.';
            swlErrorHandler(message);
        }
    },
    columnDefs: [{ className: 'dtr-control', targets: 0, orderable: false }],
    columns: [
        { data: null, defaultContent: '' },
        { data: 'jenis_tes' },
        { data: 'period', className: 'text-center' },
        {
            data: 'nama_tilok',
            render: function (data, type, row) {
                if (!data) return '-';
                return `<a href="${AppConfig.initGlobal}apps-cat-detail/${row.uid}"><i class="bi bi-geo-alt"></i> ${data}</a>`;
            }
        },
        { data: 'created_at', className: 'text-center' },
        {
            data: null,
            orderable: false,
            searchable: false,
            className: 'text-center',
            render: function (data, type, row) {
                return `
                    <button class="btn btn-sm btn-primary btn-update" data-id="${row.id}"><i class='bi bi-pencil'></i></button>
                    <button class="btn btn-sm btn-danger btn-remove" data-id="${row.id}"><i class="bi bi-trash"></i></button>
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
                key: 'cat',
                table,
                reloadSummaryOnClick: false,
                loadSummary: loadSummaryTilok,
                cards: [
                    { id: 'total-tilok', label: 'Total Tilok', value: '0' },
                    { id: 'total-kapasitas', label: 'Total Kapasitas', value: '0' },
                    { id: 'total-jenis', label: 'Jenis Tes Aktif', value: '0' },
                    { id: 'total-periode', label: 'Periode Aktif', value: '0' },
                    { id: 'data-shown', label: 'Data Ditampilkan', value: '0' },
                    { id: 'last-update', label: 'Update Terakhir', value: '-' }
                ]
            });
        }
        updateShownCAT();
        loadSummaryTilok();
    }
});
table.on('xhr.dt', function () { loadSummaryTilok(); });
table.on('draw.dt', updateShownCAT);


$('#dataTable tbody').on('click', 'tr td .btn-remove', function () {
    const key = $(this).attr('data-id');
    Swal.fire({
        text: 'Apa anda yakin akan mengahapus data ini ?',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#d63031',
        confirmButtonText: 'Ya',
        cancelButtonText: 'Tidak'
    }).then((result) => {
        if (result.isConfirmed) {
            $.ajax({
                type: 'POST',
                url: AppConfig.initGlobal + 'kill/data-tilok-cat',
                data: { key: key },
                success: function (response) {
                    if (response) {
                        swlSuccess();
                        table.ajax.reload(null, false);
                        loadSummaryTilok();
                    }
                }
            });
        }
    });
});

$('#dataTable tbody').on('click', '.btn-update', function () {
    let tr = $(this).closest('tr');
    if (tr.hasClass('child')) {
        tr = tr.prev('.parent');
    }

    const row = table.row(tr).data();
    if (!row) return;

    const form = $('#form-usulan');
    if (typeof window.setCatTilokEditMode === 'function') {
        window.setCatTilokEditMode(true);
    }
    $('#DataModalLabel').text('Update Data');

    form.find('[name="key"]').val(row.id);
    form.find('[name="action"]').val('update');
    form.find('[name="startdate"]').val(row.period_start_date);
    form.find('[name="enddate"]').val(row.period_end_date);
    form.find('[name="tilok"]').val(row.nama_tilok);
    form.find('[name="capacity"]').val(row.kapasitas);

    $('#DataModal').modal('show');

    $('#DataModal').one('shown.bs.modal', function () {
        const jenis = row.jenis_tes_id || '';
        const jenisText = row.jenis_tes || '';

        if (typeof window.setCatJenisPickerValue === 'function') {
            window.setCatJenisPickerValue(jenis, jenisText);
            return;
        }

        const select = form.find('[name="jenis"]');
        if (jenis) {
            if (!select.find(`option[value="${jenis}"]`).length) {
                const option = new Option(jenisText, jenis, true, true);
                select.append(option);
            }
            select.val(jenis).trigger('change');
        }
    });
});
