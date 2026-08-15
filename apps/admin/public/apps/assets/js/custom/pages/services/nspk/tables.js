const emptyLottie = (window.ServiceTableUI && ServiceTableUI.createEmptyLottie)
    ? ServiceTableUI.createEmptyLottie()
    : '<div class="text-center text-muted py-5">Tidak ada data.</div>';

const processingState = (window.ServiceTableUI && ServiceTableUI.createProcessingState)
    ? ServiceTableUI.createProcessingState('Memuat data NSPK...')
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

function updateShownNspk() {
    const info = table.page.info();
    $('#nspk-data-shown').text(formatNumber((info && info.recordsDisplay) || 0));
}

function getTopLevelLabel(summary) {
    const levels = [
        { level: 'A', total: Number(summary.level_a || 0) },
        { level: 'B', total: Number(summary.level_b || 0) },
        { level: 'C', total: Number(summary.level_c || 0) }
    ];

    levels.sort((a, b) => b.total - a.total);
    if (!levels[0] || levels[0].total <= 0) {
        return '-';
    }

    return `${levels[0].level} (${formatNumber(levels[0].total)})`;
}

function renderNspkSummary(summary) {
    $('.js-total-data').text(formatNumber(summary.total_data));
    $('.js-total-instansi').text(formatNumber(summary.total_instansi));
    $('.js-top-level').text(getTopLevelLabel(summary));
    $('.js-level-combo').text(`${formatNumber(summary.level_a)} / ${formatNumber(summary.level_b)} / ${formatNumber(summary.level_c)}`);
    $('.js-last-update').text(formatDateTime(summary.last_update));
}

function loadNspkSummary() {
    return $.ajax({
        url: AppConfig.initGlobal + 'fetch/summary-nspk',
        type: 'POST',
        dataType: 'json',
        data: {
            bulan: selectedBulan
        }
    }).done((response) => {
        if (response && response.status === 'success') {
            renderNspkSummary(response.summary || {});
        }
    }).fail((xhr) => {
        const message = xhr.responseJSON && xhr.responseJSON.error
            ? xhr.responseJSON.error
            : 'Gagal memuat ringkasan data.';
        swlErrorHandler(message);
        renderNspkSummary({});
    });
}

window.loadNspkSummary = loadNspkSummary;

const table = $('#dataTable').DataTable({
    responsive: {
        details: { type: 'column', target: 'td.dtr-control' }
    },
    processing: true,
    serverSide: true,
    order: [[1, 'asc']],
    buttons: ['copy', 'excel', 'pdf', 'print'],
    ajax: {
        url: AppConfig.initGlobal + 'fetch/data-nspk',
        type: 'POST',
        dataType: 'json',
        data(d) {
            d.bulan = selectedBulan;
            return d;
        },
        error(xhr) {
            const message = xhr.responseJSON && xhr.responseJSON.error
                ? xhr.responseJSON.error
                : 'Gagal memuat data NSPK.';
            swlErrorHandler(message);
        }
    },
    columnDefs: [{ className: 'dtr-control', targets: 0, orderable: false }],
    columns: [
        { data: null, defaultContent: '' },
        {
            data: 'logo',
            className: 'text-center',
            render(data) {
                if (data) {
                    return `<img src="apps/assets/images/instansi/${data}" alt="logo" style="height:20px;">`;
                }
                return '<span class="text-muted">No Logo</span>';
            }
        },
        { data: 'instansi_name' },
        {
            data: 'wilayah',
            render(data) {
                if (!data) {
                    return '<span class="badge bg-secondary">-</span>';
                }
                return `<span class="badge bg-primary">${data}</span>`;
            }
        },
        { data: 'period', className: 'text-center' },
        { data: 'period_start_date', className: 'text-center' },
        { data: 'period_end_date', className: 'text-center' },
        {
            data: 'level',
            className: 'text-center',
            render(data) {
                if (!data) {
                    return '<span class="badge bg-secondary">-</span>';
                }
                let warna = 'bg-primary';
                if (data === 'A') warna = 'bg-success';
                if (data === 'B') warna = 'bg-warning text-dark';
                if (data === 'C') warna = 'bg-danger';
                return `<span class="badge ${warna}">${data}</span>`;
            }
        },
        {
            data: null,
            className: 'text-center',
            render(data, type, row) {
                return row.updated_at || row.created_at || '-';
            }
        },
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
                key: 'nspk',
                table,
                disableRecap: true,
                loadSummary: loadNspkSummary,
                reloadSummaryOnClick: false,
                processingText: 'Memuat data NSPK...'
            });
        }
        updateShownNspk();
        loadNspkSummary();
    }
});
table.on('xhr.dt', function () { loadNspkSummary(); });
table.on('draw.dt', updateShownNspk);

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
            .map((b) => b.text);

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
                url: AppConfig.initGlobal + 'kill/data-nspk',
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

    const form = $('#form-usulan');
    $('#DataModalLabel').text('Update Data');

    form.find('[name="key"]').val(row.id);
    form.find('[name="period"]').val((row.period || '').substring(0, 7));
    form.find('[name="syncdate1"]').val(row.period_start_date || '');
    form.find('[name="syncdate2"]').val(row.period_end_date || '');

    $('#DataModal').modal('show');
    $('#DataModal').one('shown.bs.modal', function () {
        const instansi = row.instansi_id || '';
        const instansiText = row.instansi_name || '';

        const selectInstansi = form.find('[name="instansi"]');
        if (instansi) {
            const option = new Option(instansiText, instansi, true, true);
            selectInstansi.append(option).trigger('change');
        }

        form.find('[name="level"]').val(row.level || '').trigger('change');
    });
});
