const emptyLottie = (window.ServiceTableUI && ServiceTableUI.createEmptyLottie)
    ? ServiceTableUI.createEmptyLottie()
    : '<div class="text-center text-muted py-5">Tidak ada data.</div>';

const processingState = (window.ServiceTableUI && ServiceTableUI.createProcessingState)
    ? ServiceTableUI.createProcessingState('Memuat data merit...')
    : emptyLottie;

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
if (bulanContainer) {
    bulanList.forEach((bulan) => {
        bulanContainer.insertAdjacentHTML('beforeend', `
            <li>
                <div class="form-check py-1">
                    <input class="form-check-input bulan-check"
                        type="checkbox"
                        value="${bulan.val}"
                        id="bulan${bulan.val}">
                    <label class="form-check-label fw-semibold" for="bulan${bulan.val}">
                        ${bulan.text}
                    </label>
                </div>
            </li>
        `);
    });
}

let selectedBulan = [];

function formatNumber(value) {
    if (window.ServiceTableUI && typeof ServiceTableUI.formatNumber === 'function') {
        return ServiceTableUI.formatNumber(value || 0);
    }
    const num = Number(value || 0);
    return Number.isFinite(num) ? num.toLocaleString('id-ID') : '0';
}

function formatDateTime(value) {
    if (window.ServiceTableUI && typeof ServiceTableUI.formatDateTime === 'function') {
        return ServiceTableUI.formatDateTime(value);
    }
    if (!value) return '-';
    const date = new Date(value.replace(' ', 'T'));
    if (Number.isNaN(date.getTime())) return value;
    return `${date.toLocaleDateString('id-ID')} ${date.toLocaleTimeString('id-ID', { hour: '2-digit', minute: '2-digit' })}`;
}

function updateShownMerit() {
    const info = table.page.info();
    $('#merit-data-shown').text(formatNumber((info && info.recordsDisplay) || 0));
}

function renderMeritSummary(summary) {
    $('.js-total-data').text(formatNumber(summary.total_data));
    $('.js-total-usul').text(formatNumber(summary.total_usul));
    $('.js-total-realisasi').text(formatNumber(summary.total_realisasi));
    $('.js-rata-sla').text(summary.rata_sla != null ? `${summary.rata_sla}%` : '0%');
    $('.js-last-update').text(formatDateTime(summary.last_update));
}

function loadMeritSummary() {
    return $.ajax({
        url: AppConfig.initGlobal + 'fetch/summary-merit',
        type: 'POST',
        dataType: 'json',
        data: {
            bulan: selectedBulan
        }
    }).done((response) => {
        if (response && response.status === 'success') {
            renderMeritSummary(response.summary || {});
        }
    }).fail((xhr) => {
        const message = xhr.responseJSON && xhr.responseJSON.error
            ? xhr.responseJSON.error
            : 'Gagal memuat ringkasan data.';
        swlErrorHandler(message);
        renderMeritSummary({});
    });
}

window.loadMeritSummary = loadMeritSummary;

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
        url: AppConfig.initGlobal + 'fetch/data-merit',
        type: 'POST',
        dataType: 'json',
        data(d) {
            d.bulan = selectedBulan;
            return d;
        },
        error(xhr) {
            const message = xhr.responseJSON && xhr.responseJSON.error
                ? xhr.responseJSON.error
                : 'Gagal memuat data merit.';
            swlErrorHandler(message);
        }
    },
    columnDefs: [{ className: 'dtr-control', targets: 0, orderable: false }],
    columns: [
        { data: null, defaultContent: '' },
        { data: 'period', className: 'text-center' },
        { data: 'period_start_date', className: 'text-center' },
        { data: 'period_end_date', className: 'text-center' },
        { data: 'usul_masuk', className: 'text-center' },
        { data: 'ms', className: 'text-center' },
        { data: 'tms', className: 'text-center' },
        { data: 'total_realisasi', className: 'text-center' },
        { data: 'sla_sesuai', className: 'text-center' },
        { data: 'sla_tidak_sesuai', className: 'text-center' },
        {
            data: 'persentase_sla',
            className: 'text-center',
            render(data) {
                if (data === null || data === undefined || data === '') return '0%';
                return `${data}%`;
            }
        },
        { data: 'created_by', className: 'text-center' },
        { data: 'created_at', className: 'text-center' },
        {
            data: null,
            orderable: false,
            searchable: false,
            render(data, type, row) {
                return `
                    <button class="btn btn-sm btn-primary btn-update" data-id="${row.id}">
                        <i class='bi bi-pencil'></i>
                    </button>
                    <button class="btn btn-sm btn-danger btn-remove" data-id="${row.id}">
                        <i class='bi bi-trash'></i>
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
    initComplete() {
        if (window.ServiceTableUI) {
            ServiceTableUI.setup({
                key: 'merit',
                table,
                loadSummary: loadMeritSummary,
                reloadSummaryOnClick: false,
                processingText: 'Memuat data merit...'
            });
        }
        updateShownMerit();
        loadMeritSummary();
    }
});
table.on('xhr.dt', function () { loadMeritSummary(); });
table.on('draw.dt', updateShownMerit);

const MAX_BULAN = 2;
$(document).on('change', '.bulan-check', function () {
    const checked = $('.bulan-check:checked');
    if (checked.length > MAX_BULAN) {
        this.checked = false;
        swlErrorHandler('Riwayat ditampilkan maksimal 2 bulan.');
    }
});

$('#applyBulan').on('click', function () {
    selectedBulan = $('.bulan-check:checked')
        .map(function () { return this.value; })
        .get();

    if (selectedBulan.length > 2) {
        swlErrorHandler('Silakan pilih maksimal 2 bulan saja.');
        return;
    }

    if (selectedBulan.length) {
        const namaBulan = bulanList
            .filter((b) => selectedBulan.includes(b.val))
            .map((b) => b.text.substring(0, 3));

        $('#dropdownBulan').text(namaBulan.join(', '));
    } else {
        $('#dropdownBulan').text('Pilih Bulan');
    }

    table.ajax.reload(null, false);
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
                url: AppConfig.initGlobal + 'kill/data-merit',
                dataType: 'json',
                data: { key },
                success(response) {
                    if (response && response.status) {
                        swlSuccess();
                        table.ajax.reload(null, false);
                    } else {
                        swlErrorHandler((response && response.message) || 'Gagal menghapus data.');
                    }
                },
                error(xhr) {
                    const message = xhr.responseJSON && xhr.responseJSON.message
                        ? xhr.responseJSON.message
                        : 'Gagal menghapus data.';
                    swlErrorHandler(message);
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
    if (!row) {
        return;
    }

    $('#DataModalLabel').text('Update Data');
    const form = $('#form-usulan');
    form.find('[name="key"]').val(row.id);
    form.find('[name="period"]').val((row.period || '').substring(0, 7));
    form.find('[name="syncdate1"]').val(row.period_start_date || '');
    form.find('[name="syncdate2"]').val(row.period_end_date || '');
    form.find('[name="usul_masuk"]').val(row.usul_masuk || 0);
    form.find('[name="ms"]').val(row.ms || 0);
    form.find('[name="tms"]').val(row.tms || 0);
    form.find('[name="total_realisasi"]').val(row.total_realisasi || 0);
    form.find('[name="sla_sesuai"]').val(row.sla_sesuai || 0);
    form.find('[name="sla_tidak_sesuai"]').val(row.sla_tidak_sesuai || 0);
    form.find('[name="persentase_sla"]').val(row.persentase_sla || 0);

    $('#DataModal').modal('show');
});
