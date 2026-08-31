const emptyStateMarkup = `
<div class="d-flex flex-column align-items-center justify-content-center text-center mt-5 mb-5 pb-4 tw-animate-entry">
    <img src="${AppConfig.initGlobal}apps/assets/images/empty-content-profile.png" alt="Tidak Ditemukan" style="max-width: 320px; margin-bottom: 2rem;">
    <h5 class="fw-bold" style="color: #1a202c; font-size: 1.35rem;">Pencarian Tidak Ditemukan</h5>
    <p class="text-muted mb-0" style="font-size: 1.05rem; max-width: 450px; margin: 0 auto; line-height: 1.6;">Tidak ada rekap data yang cocok dengan pencarian Anda.</p>
</div>
`;
const processingState = (window.ServiceTableUI && ServiceTableUI.createProcessingState)
    ? ServiceTableUI.createProcessingState('Memuat rekap detail...')
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


const paramKey = window.location.pathname
    .replace(/\/$/, '')
    .split('/')
    .pop();

let selectedBulan = [];

function loadMetaDetailTilok() {
    return $.ajax({
        url: AppConfig.initGlobal + 'fetch/meta-tilok-detail',
        type: 'POST',
        dataType: 'json',
        data: { key: paramKey },
        success: function (response) {
            const meta = response?.meta || {};
            $('#catDetailTilok').text(meta.nama_tilok || '-');
            if (meta.nama_seleksi) {
                $('#catDetailBadge').text(meta.nama_seleksi);
            } else {
                $('#catDetailBadge').text('Titik Lokasi');
            }
            $('#detailKeyFormCreate').val(meta.id || '');
        },
        error: function () {
            $('#catDetailTilok').text('-');
            $('#catDetailBadge').text('Titik Lokasi');
            $('#detailKeyFormCreate').val('');
        }
    });
}

function loadSummaryDetailTilok() {
    $.ajax({
        url: AppConfig.initGlobal + 'fetch/summary-tilok-detail',
        type: 'POST',
        dataType: 'json',
        data: {
            key: paramKey,
            bulan: selectedBulan
        },
        success: function (response) {
            const s = response?.summary || {};
            $('#catd-total-rekap').text(ServiceTableUI.formatNumber(s.total_rekap || 0));
            $('#catd-total-instansi').text(ServiceTableUI.formatNumber(s.total_instansi || 0));
            $('#catd-total-hadir').text(ServiceTableUI.formatNumber(s.total_hadir || 0));
            $('#catd-total-tidak-hadir').text(ServiceTableUI.formatNumber(s.total_tidak_hadir || 0));
            $('#catd-total-peserta').text(ServiceTableUI.formatNumber(s.total_peserta || 0));
            $('#catd-last-update').text(ServiceTableUI.formatDateTime(s.last_update));
        }
    });
}

function formatDateOnly(value) {
    if (!value) return '-';

    const d = new Date(`${value}T00:00:00`);
    if (Number.isNaN(d.getTime())) return value;

    return d.toLocaleDateString('id-ID', {
        year: 'numeric',
        month: 'short',
        day: '2-digit',
    });
}

const table = $('#dataTable').DataTable({
    responsive: {
        details: { type: 'column', target: 'td.dtr-control' }
    },
    processing: true,
    serverSide: true,
    order: [],
    buttons: ['copy', 'excel', 'pdf', 'print'],
    ajax: {
        url: AppConfig.initGlobal + 'fetch/data-tilok-detail',
        type: 'POST',
        data: function (d) {
            d.key = paramKey;
            d.bulan = selectedBulan;
            return d;
        }
    },
    columnDefs: [{ className: 'dtr-control', targets: 0, orderable: false }],
    columns: [
        { data: null, defaultContent: '' },
        { data: 'instansi_nama' },
        {
            data: 'period_date',
            className: 'text-center',
            render: function (data, type) {
                if (type === 'sort' || type === 'type') {
                    return data || '';
                }

                return formatDateOnly(data);
            }
        },
        { data: 'sesi', className: 'text-center' },
        { data: 'nilai_min', className: 'text-center' },
        { data: 'nilai_max', className: 'text-center' },
        { data: 'hadir', className: 'text-center' },
        { data: 'tidak_hadir', className: 'text-center' },
        { data: 'reschedule', className: 'text-center' },
        {
            data: null,
            orderable: false,
            className: 'text-center fw-bold',
            render: function (data, type, row) {
                return (parseInt(row.hadir) || 0) + (parseInt(row.tidak_hadir) || 0);
            }
        },        
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
                key: 'catd',
                table,
                initialPageSkeleton: false,
                initialTableSkeleton: false,
                initialBackdrop: false,
                pageSkeleton: false,
                tableSkeleton: false,
                backdrop: false,
                reloadSummaryOnClick: false,
                loadSummary: loadSummaryDetailTilok,
                cards: [
                    { id: 'total-rekap', label: 'Total Rekap', value: '0' },
                    { id: 'total-instansi', label: 'Total Instansi', value: '0' },
                    { id: 'total-hadir', label: 'Total Hadir', value: '0' },
                    { id: 'total-tidak-hadir', label: 'Total Tidak Hadir', value: '0' },
                    { id: 'total-peserta', label: 'Total Peserta', value: '0' },
                    { id: 'last-update', label: 'Update Terakhir', value: '-' }
                ]
            });
        }
        loadSummaryDetailTilok();
    }
});
table.on('xhr.dt', function () { loadSummaryDetailTilok(); });

const MAX_BULAN = 6;
$(document).on('change', '.bulan-check', function () {
    const checked = $('.bulan-check:checked');

    if (checked.length > MAX_BULAN) {
        this.checked = false;
        swlErrorHandler('Riwayat ditampilkan maksimal 6 bulan.');
    }
});

$('#applyBulan').on('click', function () {

    selectedBulan = $('.bulan-check:checked')
        .map(function () {
            return this.value;
        })
        .get();

    if (selectedBulan.length > 6) {
        swlErrorHandler('Silakan pilih maksimal 6 bulan saja.');
        return;
    }

    if (selectedBulan.length) {
        const namaBulan = bulanList
            .filter(b => selectedBulan.includes(b.val))
            .map(b => b.text.substring(0, 3));

        $('#dropdownBulan').text(namaBulan.join(', '));
    } else {
        $('#dropdownBulan').text('Pilih Bulan');
    }
    table.ajax.reload();
    loadSummaryDetailTilok();
});

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
            swlwaitProsessing();
            $.ajax({
                type: "POST",
                url: AppConfig.initGlobal + "kill/data-tilok-rekap",
                data: {
                    key: key
                },
                success: function (response) {
                    if (response) {
                        swlSuccess();
                        table.ajax.reload(null, false);
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

    const form = $('#form-usulan-edit');

    $('#DataModalLabel').text('Update Data');

    form.find('[name="key"]').val(row.id);
    form.find('[name="tanggal"]').val(row.period_date);
    form.find('[name="sesi"]').val(row.sesi);
    form.find('[name="nilai_min"]').val(row.nilai_min);
    form.find('[name="nilai_max"]').val(row.nilai_max);
    form.find('[name="hadir"]').val(row.hadir);
    form.find('[name="tidak_hadir"]').val(row.tidak_hadir);
    form.find('[name="reschedule"]').val(row.reschedule);

    $('#DataModalDetail').modal('show');
    $('#DataModalDetail').one('shown.bs.modal', function () {
        const instansi = row.instansi_id || '';
        const instansiText = row.instansi_nama || ''; 

        const select = form.find('[name="instansi"]');

        if (instansi) {
            const option = new Option(instansiText, instansi, true, true);
            select.append(option).trigger('change');
        }
    });

});


$(document).on('click', '.sbmt-edit', function () {
    $('#form-usulan-edit').submit();
});

$('#form-usulan-edit').on('submit', function (e) {
    e.preventDefault();
    $('#DataModalDetail').modal('hide');
    swlwaitProsessing();
    $.ajax({
        url: AppConfig.initGlobal + 'store/update-data-hasil-cat',
        type: 'POST',
        data: $(this).serialize(),
        success: function (response) {
            if (response.status == 'error') {
                swlErrorHandler(response.message);
            } else {
                if (response) {
                    $('#dataTable').DataTable().ajax.reload();
                    swlSuccess();
                    loadSummaryDetailTilok();
                }
            }
        }
    });
});

loadMetaDetailTilok();
