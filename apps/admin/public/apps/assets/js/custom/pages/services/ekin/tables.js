const emptyLottie = (window.ServiceTableUI && ServiceTableUI.createEmptyLottie)
    ? ServiceTableUI.createEmptyLottie()
    : '<div class="text-center text-muted py-5">Tidak ada data.</div>';
const processingState = (window.ServiceTableUI && ServiceTableUI.createProcessingState)
    ? ServiceTableUI.createProcessingState('Memuat data E-Kinerja...')
    : '<div class="text-center text-muted py-4">Memuat data...</div>';

const bulanList = [
    { val: '01', text: 'Januari' }, { val: '02', text: 'Februari' }, { val: '03', text: 'Maret' },
    { val: '04', text: 'April' }, { val: '05', text: 'Mei' }, { val: '06', text: 'Juni' },
    { val: '07', text: 'Juli' }, { val: '08', text: 'Agustus' }, { val: '09', text: 'September' },
    { val: '10', text: 'Oktober' }, { val: '11', text: 'November' }, { val: '12', text: 'Desember' }
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
let childCache = {};

function updateShownEKIN() {
    const info = table.page.info();
    $('#ekin-data-shown').text(ServiceTableUI.formatNumber((info && info.recordsDisplay) || 0));
}

function loadSummaryEKIN() {
    $.ajax({
        url: AppConfig.initGlobal + 'fetch/summary-ekin',
        type: 'POST',
        dataType: 'json',
        data: { bulan: selectedBulan },
        success: function (response) {
            const s = response?.summary || {};
            $('#ekin-total-data').text(ServiceTableUI.formatNumber(s.total_data || 0));
            $('#ekin-total-harian').text(ServiceTableUI.formatNumber(s.total_harian || 0));
            $('#ekin-total-nip').text(ServiceTableUI.formatNumber(s.total_nip || 0));
            $('#ekin-total-realisasi').text(ServiceTableUI.formatNumber(s.total_realisasi || 0));
            $('#ekin-last-update').text(ServiceTableUI.formatDateTime(s.last_update));
        }
    });
}

function loadChildByPeriod(periodDate) {
    return $.ajax({
        url: AppConfig.initGlobal + 'fetch/child-ekin',
        type: 'POST',
        dataType: 'json',
        data: { period_date: periodDate }
    });
}

function buildChildHtml(list = []) {
    if (!list.length) {
        return '<div class="px-3 py-2 text-muted small">Belum ada data upload pada periode ini.</div>';
    }

    const rows = list.map((row) => `
        <tr>
            <td>${row.period || '-'}</td>
            <td>${row.sub_unit || '-'}</td>
            <td>${row.periode || '-'}</td>
            <td>${row.tanggal_kegiatan || '-'}</td>
            <td class="text-center">${ServiceTableUI.formatNumber(row.total_nip || 0)}</td>
            <td class="text-center">${ServiceTableUI.formatNumber(row.total_kegiatan || 0)}</td>
            <td class="text-center">${ServiceTableUI.formatNumber(row.total_realisasi || 0)}</td>
            <td class="text-center">${row.created_by || '-'}</td>
            <td class="text-center">${ServiceTableUI.formatDateTime(row.created_at)}</td>
            <td class="text-center">
                <button class="btn btn-sm btn-info btn-detail me-1" data-id="${row.ekin_key}">
                    <i class="bi bi-search"></i>
                </button>
                <button class="btn btn-sm btn-danger ekin-btn-remove" data-id="${row.ekin_key}">
                    <i class="bi bi-trash"></i>
                </button>
            </td>
        </tr>
    `).join('');

    return `
        <div class="table-responsive">
            <table class="table table-sm table-bordered mb-0">
                <thead>
                    <tr>
                        <th>Periode</th>
                        <th>Sub Unit</th>
                        <th>Periode (File)</th>
                        <th>Tanggal Ekin</th>
                        <th>Total NIP</th>
                        <th>Total Kegiatan</th>
                        <th>Total Realisasi</th>
                        <th>PIC</th>
                        <th>Upload</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>${rows}</tbody>
            </table>
        </div>
    `;
}

const table = $('#dataTable').DataTable({
    responsive: false,
    processing: true,
    serverSide: true,
    order: [[1, 'desc']],
    buttons: ['copy', 'excel', 'pdf', 'print'],
    ajax: {
        url: AppConfig.initGlobal + 'fetch/data-ekin',
        type: 'POST',
        data: function (d) {
            d.bulan = selectedBulan;
            return d;
        }
    },
    columnDefs: [{ className: 'text-center', targets: 0, orderable: false }],
    columns: [
        {
            data: null,
            render: function (_, __, row) {
                return `<button class="btn btn-sm btn-light ekin-expand" data-date="${row.period_date}">
                    <i class="bi bi-plus"></i>
                </button>`;
            }
        },
        { data: 'period_date_label', className: 'text-center fw-semibold' },
        { data: 'total_upload', className: 'text-center fw-semibold' },
        { data: 'total_sub_unit', className: 'text-center' },
        { data: 'total_nip', className: 'text-center' },
        { data: 'total_kegiatan', className: 'text-center' },
        { data: 'total_realisasi', className: 'text-center' },
        { data: 'last_upload_at', className: 'text-center', render: function (v) { return ServiceTableUI.formatDateTime(v); } }
    ],
    language: {
        emptyTable: (window.ServiceTableUI ? ServiceTableUI.createEmptyState() : 'Tidak ada data'),
        zeroRecords: (window.ServiceTableUI ? ServiceTableUI.createEmptyState() : 'Tidak ada data'),
        processing: processingState
    },
    initComplete: function () {
        if (window.ServiceTableUI) {
            ServiceTableUI.setup({
                key: 'ekin',
                table,

                loadSummary: loadSummaryEKIN,
                cards: [
                    { id: 'total-data', label: 'Total Upload', value: '0' },
                    { id: 'total-harian', label: 'Header Harian', value: '0' },
                    { id: 'total-nip', label: 'Total NIP', value: '0' },
                    { id: 'total-realisasi', label: 'Total Realisasi', value: '0' },
                    { id: 'data-shown', label: 'Data Ditampilkan', value: '0' },
                    { id: 'last-update', label: 'Update Terakhir', value: '-' }
                ]
            });
        }
        updateShownEKIN();
        loadSummaryEKIN();
    }
});
table.on('draw.dt', updateShownEKIN);
table.on('xhr.dt', function () {
    childCache = {};
});

const MAX_BULAN = 2;
$(document).on('change', '.bulan-check', function () {
    const checked = $('.bulan-check:checked');
    if (checked.length > MAX_BULAN) {
        this.checked = false;
        swlErrorHandler('Riwayat ditampilkan maksimal 2 bulan.');
    }
});

$('#applyBulan').on('click', function () {
    selectedBulan = $('.bulan-check:checked').map(function () { return this.value; }).get();
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

    updateActiveFiltersLabel();
    table.ajax.reload();
    loadSummaryEKIN();
});

function updateActiveFiltersLabel() {
    const $container = $('#activeFilterContainer');
    const $list = $container.find('.active-filters-list');
    $list.empty();
    
    let hasFilters = false;

    if (selectedBulan.length > 0) {
        hasFilters = true;
        const labels = bulanList
            .filter(b => selectedBulan.includes(b.val))
            .map(b => b.text);
        
        $list.append(`<span class="badge bg-light text-primary border border-primary mb-1 filter-badge" style="font-weight: 500;">Bulan: ${labels.join(', ')}</span>`);
    }

    if (hasFilters) {
        $container.addClass('d-flex').show();
    } else {
        $container.removeClass('d-flex').hide();
    }
}

$('#dataTable tbody').on('click', '.ekin-expand', function () {
    const $btn = $(this);
    const tr = $btn.closest('tr');
    const row = table.row(tr);
    const periodDate = String($btn.data('date') || '');
    if (!periodDate) return;

    if (row.child.isShown()) {
        row.child.hide();
        tr.removeClass('shown');
        $btn.html('<i class="bi bi-plus"></i>');
        return;
    }

    $btn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm"></span>');
    const applyRows = function (rows) {
        row.child(buildChildHtml(rows)).show();
        tr.addClass('shown');
        $btn.html('<i class="bi bi-dash"></i>');
        $btn.prop('disabled', false);
    };

    if (childCache[periodDate]) {
        applyRows(childCache[periodDate]);
        return;
    }

    loadChildByPeriod(periodDate).done(function (response) {
        const rows = response?.status === 'success' && Array.isArray(response.list) ? response.list : [];
        childCache[periodDate] = rows;
        applyRows(rows);
    }).fail(function () {
        row.child('<div class="px-3 py-2 text-danger small">Gagal memuat data child.</div>').show();
        tr.addClass('shown');
        $btn.html('<i class="bi bi-dash"></i>');
        $btn.prop('disabled', false);
    });
});

$('#dataTable tbody').on('click', '.ekin-btn-remove', function () {
    const key = $(this).data('id');
    if (!key) return;

    Swal.fire({
        text: 'Apa anda yakin akan menghapus data ini?',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#d63031',
        confirmButtonText: 'Ya',
        cancelButtonText: 'Tidak'
    }).then((result) => {
        if (!result.isConfirmed) return;
        $.ajax({
            type: 'POST',
            url: AppConfig.initGlobal + 'kill/data-ekin',
            data: { key },
            success: function (response) {
                if (!response) return;
                swlSuccess();
                childCache = {};
                table.ajax.reload(null, false);
                loadSummaryEKIN();
            }
        });
    });
});
