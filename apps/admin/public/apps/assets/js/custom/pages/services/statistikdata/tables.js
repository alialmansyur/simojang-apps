const emptyLottie = (window.ServiceTableUI && ServiceTableUI.createEmptyLottie)
    ? ServiceTableUI.createEmptyLottie()
    : '<div class="text-center text-muted py-5">Tidak ada data.</div>';
const processingState = (window.ServiceTableUI && ServiceTableUI.createProcessingState)
    ? ServiceTableUI.createProcessingState('Memuat data statistik...')
    : '<div class="text-center text-muted py-4">Memuat data...</div>';

function getActiveJenis() {
    return String((window.statistikState && window.statistikState.jenis) || '').trim();
}

function updateShownStatistik() {
    const info = table.page.info();
    $('#stk-data-shown').text(ServiceTableUI.formatNumber((info && info.recordsDisplay) || 0));
}

function loadSummaryStatistik() {
    const jenis = getActiveJenis();
    if (!jenis) {
        $('#stk-total-upload').text('0');
        $('#stk-total-data').text('0');
        $('#stk-total-instansi').text('0');
        $('#stk-total-periode').text('0');
        $('#stk-data-shown').text('0');
        $('#stk-last-update').text('-');
        return;
    }

    $.ajax({
        url: AppConfig.initGlobal + 'fetch/summary-statistik',
        type: 'POST',
        dataType: 'json',
        data: { jenis },
        success: function (response) {
            const s = response?.summary || {};
            $('#stk-total-upload').text(ServiceTableUI.formatNumber(s.total_upload || 0));
            $('#stk-total-data').text(ServiceTableUI.formatNumber(s.total_data || 0));
            $('#stk-total-instansi').text(ServiceTableUI.formatNumber(s.total_instansi || 0));
            $('#stk-total-periode').text(ServiceTableUI.formatNumber(s.total_periode || 0));
            $('#stk-last-update').text(ServiceTableUI.formatDateTime(s.last_update));
        }
    });
}

window.refreshStatistikSummary = loadSummaryStatistik;

const table = $('#dataTable').DataTable({
    responsive: {
        details: { type: 'column', target: 'td.dtr-control' }
    },
    processing: true,
    serverSide: true,
    order: [[1, 'desc']],
    dom: 'Bfrtip',
    buttons: ['copy', 'excel', 'pdf', 'print'],
    ajax: {
        url: AppConfig.initGlobal + 'fetch/data-statistik',
        type: 'POST',
        dataType: 'json',
        data: function (d) {
            d.jenis = getActiveJenis();
            return d;
        }
    },
    columnDefs: [{ className: 'dtr-control', targets: 0, orderable: false }],
    columns: [
        { data: null, defaultContent: '' },
        {
            data: null,
            render: function (_, __, row) {
                const total = Number(row.total || 0);
                const disabled = total <= 0 ? 'disabled' : '';
                const label = row.file_hash || '-';

                if (total <= 0) {
                    return `<span class="text-muted">${label}</span>`;
                }
                return `<a href="#" class="btn-detail" data-id="${row.id}" data-jenis="${row.jenis}" ${disabled}>${label}</a>`;
            }
        },
        { data: 'jenis', className: 'text-center' },
        { data: 'period', className: 'text-center' },
        { data: 'date', className: 'text-center' },
        { data: 'total', className: 'text-center' },
        { data: 'created_at', className: 'text-center' },
        { data: 'created_by', className: 'text-center' },
        {
            data: null,
            orderable: false,
            searchable: false,
            render: function (_, __, row) {
                return `
                    <button class="btn btn-sm btn-danger btn-remove" data-id="${row.id}" data-jenis="${row.jenis}">
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
    },
    initComplete: function () {
        if (window.ServiceTableUI) {
            ServiceTableUI.setup({
                key: 'stk',
                table,
                disableRecap: true,
                loadSummary: loadSummaryStatistik,
                processingText: 'Memuat data statistik...'
            });
        }
        updateShownStatistik();
        loadSummaryStatistik();
    }
});

table.on('xhr.dt', function () {
    loadSummaryStatistik();
});
table.on('draw.dt', updateShownStatistik);

$('#dataTable tbody').on('click', 'tr td .btn-remove', function () {
    const key = $(this).attr('data-id');
    const jenis = $(this).attr('data-jenis');

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
                url: AppConfig.initGlobal + 'kill/data-statistik',
                dataType: 'json',
                data: { key, jenis },
                success: function (response) {
                    if (response && response.status) {
                        swlSuccess();
                        table.ajax.reload(null, false);
                        loadSummaryStatistik();
                    } else {
                        swlErrorHandler((response && response.message) || 'Gagal menghapus data.');
                    }
                },
                error: function (xhr) {
                    const message = xhr.responseJSON && xhr.responseJSON.message
                        ? xhr.responseJSON.message
                        : 'Gagal menghapus data statistik.';
                    swlErrorHandler(message);
                }
            });
        }
    });
});
