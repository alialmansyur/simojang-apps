const emptyLottie = (window.ServiceTableUI && ServiceTableUI.createEmptyLottie)
    ? ServiceTableUI.createEmptyLottie()
    : '<div class="text-center text-muted py-5">Tidak ada data.</div>';

const processingState = (window.ServiceTableUI && ServiceTableUI.createProcessingState)
    ? ServiceTableUI.createProcessingState('Memuat data wasdal...')
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
    bulanList.forEach(bulan => {
        bulanContainer.insertAdjacentHTML('beforeend', `
            <li>
                <div class="form-check py-1">
                    <input class="form-check-input bulan-check"
                           type="checkbox"
                           value="${bulan.val}"
                           id="bulan${bulan.val}">
                    <label class="form-check-label fw-semibold"
                           for="bulan${bulan.val}">
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

function updateShownWasdal() {
    const info = table.page.info();
    $('#wasdal-data-shown').text(formatNumber((info && info.recordsDisplay) || 0));
}

function renderSummary(summary) {
    $('.js-total-data').text(formatNumber(summary.total_data || 0));
    $('.js-total-kasus').text(formatNumber(summary.total_kasus || 0));
    $('.js-total-instansi').text(formatNumber(summary.total_instansi || 0));

    const topInstansi = Array.isArray(summary.top_instansi) ? summary.top_instansi : [];
    const topPermasalahan = Array.isArray(summary.top_permasalahan) ? summary.top_permasalahan : [];
    const topInstansiText = topInstansi.length
        ? `${topInstansi[0].instansi_name || '-'} (${formatNumber(topInstansi[0].total_kasus || 0)})`
        : '-';
    const topPermasalahanText = topPermasalahan.length
        ? `${topPermasalahan[0].permasalahan || '-'} (${formatNumber(topPermasalahan[0].total_kasus || 0)})`
        : '-';

    $('.js-top-instansi').text(topInstansiText);
    $('.js-top-permasalahan').text(topPermasalahanText);
    $('.js-last-update').text(formatDateTime(summary.last_update));
}

function loadSummary() {
    return $.ajax({
        url: AppConfig.initGlobal + 'fetch/summary-wasdal',
        type: 'POST',
        dataType: 'json',
        data: {
            bulan: selectedBulan
        }
    }).done(function (response) {
        if (response && response.status === 'success') {
            renderSummary(response.summary || {});
        }
    }).fail(function () {
        renderSummary({});
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
        url: AppConfig.initGlobal + 'fetch/data-wasdal',
        type: 'POST',
        dataType: 'json',
        data: function (d) {
            d.bulan = selectedBulan;
            return d;
        },
        error: function (xhr) {
            const message = xhr.responseJSON && xhr.responseJSON.error
                ? xhr.responseJSON.error
                : 'Gagal memuat data wasdal.';
            swlErrorHandler(message);
        }
    },
    columnDefs: [{ className: 'dtr-control', targets: 0, orderable: false }],
    columns: [
        { data: null, defaultContent: '' },
        { 
            data: 'logo',
            className: 'text-center',
            render: function(data) {
                if (data) {
                    return '<img src="apps/assets/images/instansi/' + data + '" alt="logo" style="height:20px;">';
                } else {
                    return '<span class="text-muted">No Logo</span>';
                }
            }
        },
        { data: 'instansi_name' },
        { data: 'period', className: 'text-center' },
        { data: 'period_start_date', className: 'text-center' },
        { data: 'period_end_date', className: 'text-center' },
        { data: 'permasalahan' },
        { data: 'total', className: 'text-center' },
        { data: 'created_by', className: 'text-center' },
        { data: 'created_at', className: 'text-center' },
        {
            data: null,
            orderable: false,
            searchable: false,
            render: function(data, type, row) {
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
    initComplete: function () {
        if (window.ServiceTableUI) {
            ServiceTableUI.setup({
                key: 'wasdal',
                table,
                disableRecap: true,
                loadSummary,
                reloadSummaryOnClick: false,
                processingText: 'Memuat data wasdal...'
            });
        }
        updateShownWasdal();
        loadSummary();
    }
});
table.on('xhr.dt', function () { loadSummary(); });
table.on('draw.dt', updateShownWasdal);

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
        .map(function () {
            return this.value;
        })
        .get();

    if (selectedBulan.length > 2) {
        swlErrorHandler('Silakan pilih maksimal 2 bulan saja.');
        return;
    }

    if (selectedBulan.length) {
        const namaBulan = bulanList
            .filter(b => selectedBulan.includes(b.val))
            .map(b => b.text);

        $('#dropdownBulan').text(namaBulan.join(', '));
    } else {
        $('#dropdownBulan').text('Pilih Bulan');
    }

    updateActiveFiltersLabel();

    table.ajax.reload(null, false);
});

function updateActiveFiltersLabel() {
    let hasFilter = false;
    const $container = $('.active-filters-container');
    
    $container.find('.filter-badge').remove();
    
    if (selectedBulan && selectedBulan.length > 0) {
        const labels = bulanList
            .filter((item) => selectedBulan.includes(item.val))
            .map((item) => item.text);
            
        $container.append(`<span class="badge bg-light text-primary border border-primary mb-1 filter-badge" style="font-weight: 500;">Bulan: ${labels.join(', ')}</span>`);
        hasFilter = true;
    }
    
    if (hasFilter) {
        $container.addClass('d-flex').show();
    } else {
        $container.removeClass('d-flex').hide();
    }
}

function ynIcon(data) {
    return data === '1'
        ? '<span class="text-success">TRUE</span>'
        : '<span class="text-danger">FALSE</span>';
}   

$('#dataTable tbody').on('click', 'tr td .btn-remove', function () {
    var key = $(this).attr('data-id');
    Swal.fire({
        text: "Apa anda yakin akan mengahapus data ini ?",
        icon: "warning",    
        showCancelButton: true,
        confirmButtonColor: "#d63031",
        confirmButtonText: "Ya",
        cancelButtonText: "Tidak"
    }).then((result) => {
        if (result.isConfirmed) {
            $.ajax({
                type: "POST",
                url: AppConfig.initGlobal + "kill/data-wasdal",
                data: {
                    key: key
                },
                success: function (response) {
                    if (response) {
                        swlSuccess();
                        table.ajax.reload(null, false);
                    }
                },
                error: function () {
                    swlErrorHandler('Gagal menghapus data.');
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

    $('#DataModalLabel').text('Update Data');

    form.find('[name="key"]').val(row.id);
    form.find('[name="period"]').val(row.period);
    form.find('[name="syncdate1"]').val(row.period_start_date);
    form.find('[name="syncdate2"]').val(row.period_end_date);
    form.find('[name="permasalahan"]').val(row.permasalahan);
    form.find('[name="total"]').val(row.total);

    $('#DataModal').modal('show');
    $('#DataModal').one('shown.bs.modal', function () {
        const instansi = row.instansi_id || '';
        const instansiText = row.instansi_name || ''; 

        const select = form.find('[name="instansi"]');
        select.find('option').remove();

        if (instansi) {
            const option = new Option(instansiText, instansi, true, true);
            select.append(option).trigger('change');
        }
    });

});
