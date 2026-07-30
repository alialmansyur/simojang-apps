const emptyLottie = (window.ServiceTableUI && ServiceTableUI.createEmptyLottie)
    ? ServiceTableUI.createEmptyLottie()
    : '<div class="text-center text-muted py-5">Tidak ada data.</div>';
const processingState = (window.ServiceTableUI && ServiceTableUI.createProcessingState)
    ? ServiceTableUI.createProcessingState('Memuat data DMS...')
    : '<div class="text-center text-muted py-4">Memuat data...</div>';

const bulanList = [
    { val: '01', text: 'Januari' },
    { val: '02', text: 'Februari' },
    { val: '03', text: 'Maret' },
    { val: '04', text: 'April' },
    { val: '05', text: 'Mei' },
    { val: '06', text: 'Juni' },
    { val: '07', text: 'Juli' },
    { val: '08', text: 'Agustus' },
    { val: '09', text: 'September' },
    { val: '10', text: 'Oktober' },
    { val: '11', text: 'November' },
    { val: '12', text: 'Desember' }
];

const bulanContainer = document.getElementById('bulanList');
bulanContainer.innerHTML = '';
bulanList.forEach((bulan) => {
    bulanContainer.insertAdjacentHTML('beforeend', `
        <li>
            <div class="form-check py-1">
                <input class="form-check-input bulan-check" type="checkbox" value="${bulan.val}" id="bulan${bulan.val}">
                <label class="form-check-label fw-semibold" for="bulan${bulan.val}">${bulan.text}</label>
            </div>
        </li>
    `);
});

let selectedBulan = [];

function updateShownDMS() {
    const info = table.page.info();
    $('#dms-data-shown').text(ServiceTableUI.formatNumber((info && info.recordsDisplay) || 0));
}

function loadSummaryDMS() {
    $.ajax({
        url: AppConfig.initGlobal + 'fetch/summary-dms',
        type: 'POST',
        dataType: 'json',
        data: { bulan: selectedBulan },
        success: function (response) {
            const s = response?.summary || {};
            $('#dms-total-data').text(ServiceTableUI.formatNumber(s.total_data || 0));
            $('#dms-total-instansi').text(ServiceTableUI.formatNumber(s.total_instansi || 0));
            $('#dms-total-dokumen').text(ServiceTableUI.formatNumber(s.total_dokumen || 0));
            $('#dms-total-periode').text(ServiceTableUI.formatNumber(s.total_periode || 0));
            $('#dms-last-update').text(ServiceTableUI.formatDateTime(s.last_update));
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
        url: AppConfig.initGlobal + 'fetch/data-dms',
        type: 'POST',
        data: function (d) {
            d.bulan = selectedBulan;
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
        { data: 'nama_instansi' },
        { data: 'period' },
        { data: 'period_start_date' },
        { data: 'period_end_date' },
        { data: 'jenis' },
        { data: 'total', className: 'text-center' },
        { data: 'created_by' },
        {
            data: null,
            orderable: false,
            searchable: false,
            render: (_, __, row) => `<button class="btn btn-sm btn-danger btn-remove" data-id="${row.id}"><i class='bi bi-trash'></i></button>`
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
                key: 'dms',
                table,
                loadSummary: loadSummaryDMS,
                cards: [
                    { id: 'total-data', label: 'Total Data', value: '0' },
                    { id: 'total-instansi', label: 'Total Instansi', value: '0' },
                    { id: 'total-dokumen', label: 'Total Dokumen', value: '0' },
                    { id: 'total-periode', label: 'Total Periode', value: '0' },
                    { id: 'data-shown', label: 'Data Ditampilkan', value: '0' },
                    { id: 'last-update', label: 'Update Terakhir', value: '-' }
                ]
            });
        }
        updateShownDMS();
        loadSummaryDMS();
    }
});
table.on('xhr.dt', function () { loadSummaryDMS(); });
table.on('draw.dt', updateShownDMS);

const MAX_BULAN = 2;
$(document).on('change', '.bulan-check', function () {
    const checked = $('.bulan-check:checked');
    if (checked.length > MAX_BULAN) {
        this.checked = false;
        swlErrorHandler('Riwayat ditampilkan maksimal 2 bulan.');
    }
});

$('#applyBulan').on('click', function () {
    selectedBulan = $('.bulan-check:checked').map(function () {
        return this.value;
    }).get();

    if (selectedBulan.length > 2) {
        swlErrorHandler('Silakan pilih maksimal 2 bulan saja.');
        return;
    }

    if (selectedBulan.length) {
        const namaBulan = bulanList.filter((b) => selectedBulan.includes(b.val)).map((b) => b.text.substring(0, 3));
        $('#dropdownBulan').text(namaBulan.join(', '));
    } else {
        $('#dropdownBulan').text('Pilih Bulan');
    }

    table.ajax.reload();
    loadSummaryDMS();
});

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
                url: AppConfig.initGlobal + 'kill/data-dms',
                data: { key: key },
                success: function (response) {
                    if (response) {
                        swlSuccess();
                        table.ajax.reload(null, false);
                        loadSummaryDMS();
                    }
                }
            });
        }
    });
});
