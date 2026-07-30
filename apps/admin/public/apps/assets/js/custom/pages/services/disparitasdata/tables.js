const emptyLottie = (window.ServiceTableUI && ServiceTableUI.createEmptyLottie)
    ? ServiceTableUI.createEmptyLottie()
    : '<div class="text-center text-muted py-5">Tidak ada data.</div>';
const processingState = (window.ServiceTableUI && ServiceTableUI.createProcessingState)
    ? ServiceTableUI.createProcessingState('Memuat data disparitas...')
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

function updateShownDisparitas() {
    const info = table.page.info();
    $('#dsp-data-shown').text(ServiceTableUI.formatNumber((info && info.recordsDisplay) || 0));
}

function loadSummaryDisparitas() {
    $.ajax({
        url: AppConfig.initGlobal + 'fetch/summary-disparitas',
        type: 'POST',
        dataType: 'json',
        data: { bulan: selectedBulan },
        success: function (response) {
            const s = response?.summary || {};
            $('#dsp-total-upload').text(ServiceTableUI.formatNumber(s.total_upload || 0));
            $('#dsp-total-anomali').text(ServiceTableUI.formatNumber(s.total_anomali || 0));
            $('#dsp-total-instansi').text(ServiceTableUI.formatNumber(s.total_instansi || 0));
            $('#dsp-total-jenis-anomali').text(ServiceTableUI.formatNumber(s.total_jenis_anomali || 0));
            $('#dsp-last-update').text(ServiceTableUI.formatDateTime(s.last_update));
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
        url: AppConfig.initGlobal + 'fetch/data-disparitas',
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
            data: 'file_hash',
            render: (_, __, row) => `<strong><a href="#" class="btn-detail" data-id="${row.id}">${row.file_hash}</a></strong>`
        },
        { data: 'period', className: 'text-center' },
        { data: 'period_start_date', className: 'text-center' },
        { data: 'period_end_date', className: 'text-center' },
        { data: 'total', className: 'text-center' },
        { data: 'created_at' },
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
                key: 'dsp',
                table,
                loadSummary: loadSummaryDisparitas,
                cards: [
                    { id: 'total-upload', label: 'Total Upload', value: '0' },
                    { id: 'total-anomali', label: 'Total Anomali', value: '0' },
                    { id: 'total-instansi', label: 'Total Instansi', value: '0' },
                    { id: 'total-jenis-anomali', label: 'Jenis Anomali', value: '0' },
                    { id: 'data-shown', label: 'Data Ditampilkan', value: '0' },
                    { id: 'last-update', label: 'Update Terakhir', value: '-' }
                ]
            });
        }
        updateShownDisparitas();
        loadSummaryDisparitas();
    }
});
table.on('xhr.dt', function () { loadSummaryDisparitas(); });
table.on('draw.dt', updateShownDisparitas);

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
    loadSummaryDisparitas();
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
                url: AppConfig.initGlobal + 'kill/data-disparitas',
                data: { key: key },
                success: function (response) {
                    if (response) {
                        swlSuccess();
                        table.ajax.reload(null, false);
                        loadSummaryDisparitas();
                    }
                }
            });
        }
    });
});
