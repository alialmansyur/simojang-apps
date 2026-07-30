const emptyLottie = (window.ServiceTableUI && ServiceTableUI.createEmptyLottie)
    ? ServiceTableUI.createEmptyLottie()
    : '<div class="text-center text-muted py-5">Tidak ada data.</div>';
const processingState = (window.ServiceTableUI && ServiceTableUI.createProcessingState)
    ? ServiceTableUI.createProcessingState('Memuat data integrasi...')
    : '<div class="text-center text-muted py-4">Memuat data...</div>';

function updateShownIntegrasi() {
    const info = table.page.info();
    $('#itg-data-shown').text(ServiceTableUI.formatNumber((info && info.recordsDisplay) || 0));
}

function loadSummaryIntegrasi() {
    $.ajax({
        url: AppConfig.initGlobal + 'fetch/summary-integrasi',
        type: 'POST',
        dataType: 'json',
        data: { jenis: $('#jenis').val() || 0 },
        success: function (response) {
            const s = response?.summary || {};
            $('#itg-total-data').text(ServiceTableUI.formatNumber(s.total_data || 0));
            $('#itg-total-instansi').text(ServiceTableUI.formatNumber(s.total_instansi || 0));
            $('#itg-total-riwayat').text(ServiceTableUI.formatNumber(s.total_riwayat || 0));
            $('#itg-total-wilayah').text(ServiceTableUI.formatNumber(s.total_wilayah || 0));
            $('#itg-last-update').text(ServiceTableUI.formatDateTime(s.last_update));
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
    dom: 'Bfrtip',
    buttons: ['copy', 'excel', 'pdf', 'print'],
    ajax: {
        url: AppConfig.initGlobal + 'fetch/data-integrasi',
        type: 'POST',
        data: function (d) {
            d.jenis = $('#jenis').val();
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
                if (!data) return '<span class="text-muted">No Logo</span>';
                return '<img src="apps/assets/images/instansi/' + data + '" alt="logo" style="height:20px;">';
            }
        },
        { data: 'instansi_name' },
        {
            data: 'wilayah',
            render: function (data) {
                if (!data) return '<span class="badge bg-secondary">-</span>';
                return `<span class="badge bg-primary">${data}</span>`;
            }
        },
        { data: 'period_date', className: 'text-center' },
        { data: 'jenis', className: 'text-center' },
        { data: 'remarks', className: 'text-center' },
        {
            data: 'bukti_dukung',
            className: 'text-center',
            render: function (data) {
                if (!data) return '-';
                const url = data.match(/^https?:\/\//i) ? data : 'https://' + data;
                return `<a href="${url}" target="_blank" rel="noopener noreferrer"><i class="fas fa-external-link-alt">${data}</i></a>`;
            }
        },
        { data: 'updated_at', className: 'text-center' },
        {
            data: null,
            orderable: false,
            searchable: false,
            render: function (data, type, row) {
                return `
                    <button class="btn btn-sm btn-primary btn-update" data-id="${row.id}"><i class='bi bi-pencil'></i></button>
                    <button class="btn btn-sm btn-danger btn-remove" data-id="${row.id}"><i class='bi bi-trash'></i></button>
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
                key: 'itg',
                table,
                loadSummary: loadSummaryIntegrasi,
                cards: [
                    { id: 'total-data', label: 'Total Data', value: '0' },
                    { id: 'total-instansi', label: 'Total Instansi', value: '0' },
                    { id: 'total-riwayat', label: 'Jenis Riwayat Aktif', value: '0' },
                    { id: 'total-wilayah', label: 'Cakupan Wilayah', value: '0' },
                    { id: 'data-shown', label: 'Data Ditampilkan', value: '0' },
                    { id: 'last-update', label: 'Update Terakhir', value: '-' }
                ]
            });
        }
        updateShownIntegrasi();
        loadSummaryIntegrasi();
    }
});
table.on('xhr.dt', function () { loadSummaryIntegrasi(); });
table.on('draw.dt', updateShownIntegrasi);

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
                url: AppConfig.initGlobal + 'kill/data-integrasi',
                data: { key: key },
                success: function (response) {
                    if (response) {
                        swlSuccess();
                        table.ajax.reload(null, false);
                        loadSummaryIntegrasi();
                    }
                }
            });
        }
    });
});
