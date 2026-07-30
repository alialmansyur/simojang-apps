const emptyLottie = (window.ServiceTableUI && ServiceTableUI.createEmptyLottie)
    ? ServiceTableUI.createEmptyLottie()
    : '<div class="text-center text-muted py-5">Tidak ada data.</div>';
const processingState = (window.ServiceTableUI && ServiceTableUI.createProcessingState)
    ? ServiceTableUI.createProcessingState('Memuat data konsultasi...')
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

function updateShownKonsul() {
    const info = table.page.info();
    $('#konsul-data-shown').text(ServiceTableUI.formatNumber((info && info.recordsDisplay) || 0));
}

function loadSummaryKonsul() {
    $.ajax({
        url: AppConfig.initGlobal + 'fetch/summary-konsultasi',
        type: 'POST',
        dataType: 'json',
        data: { bulan: selectedBulan },
        success: function (response) {
            const s = response?.summary || {};
            $('#konsul-total-data').text(ServiceTableUI.formatNumber(s.total_data || 0));
            $('#konsul-total-pelayanan').text(ServiceTableUI.formatNumber(s.total_pelayanan || 0));
            $('#konsul-total-kanal').text(ServiceTableUI.formatNumber(s.total_kanal || 0));
            $('#konsul-bulan-dipilih').text(ServiceTableUI.formatNumber(selectedBulan.length || 0));
            $('#konsul-last-update').text(ServiceTableUI.formatDateTime(s.last_update));
        }
    });
}

const table = $('#dataTable').DataTable({
    responsive: { details: { type: 'column', target: 'td.dtr-control' } },
    processing: true,
    serverSide: true,
    order: [[1, 'asc']],
    dom: 'Bfrtip',
    buttons: ['copy', 'excel', 'pdf', 'print'],
    ajax: {
        url: AppConfig.initGlobal + 'fetch/data-konsultasi',
        type: 'POST',
        data: function (d) { d.bulan = selectedBulan; }
    },
    columnDefs: [{ className: 'dtr-control', targets: 0, orderable: false }],
    columns: [
        { data: null, defaultContent: '' },
        { data: 'period' },
        { data: 'period_start_date', className: 'text-center' },
        { data: 'period_end_date', className: 'text-center' },
        {
            data: 'pelayanan',
            className: 'text-center',
            render: function (data) {
                if (!data) return '-';
                let badgeClass = 'bg-light-secondary';
                if (String(data).startsWith('PTSP')) badgeClass = 'bg-light-primary';
                else if (String(data).startsWith('WhatsApp')) badgeClass = 'bg-light-success';
                else if (String(data).startsWith('Helpdesk')) badgeClass = 'bg-light-warning';
                else if (['Instagram', 'Facebook', 'X / Twitter', 'TikTok', 'Media Sosial'].includes(String(data))) badgeClass = 'bg-light-info';
                return `<span class="badge ${badgeClass}">${data}</span>`;
            }
        },
        { data: 'jumlah', className: 'text-center', render: $.fn.dataTable.render.number(',', '.', 0) },
        { data: 'created_by', className: 'text-center' },
        { data: 'created_at', className: 'text-center' },
        {
            data: null,
            orderable: false,
            searchable: false,
            render: function (_, __, row) {
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
                key: 'konsul',
                table,
                recapMountSelector: '.page-heading',
                loadSummary: loadSummaryKonsul,
                cards: [
                    { id: 'total-data', label: 'Total Periode', value: '0' },
                    { id: 'total-pelayanan', label: 'Total Pelayanan', value: '0' },
                    { id: 'total-kanal', label: 'Kanal Aktif', value: '0' },
                    { id: 'bulan-dipilih', label: 'Bulan Dipilih', value: '0' },
                    { id: 'data-shown', label: 'Data Ditampilkan', value: '0' },
                    { id: 'last-update', label: 'Update Terakhir', value: '-' }
                ]
            });
        }
        updateShownKonsul();
        loadSummaryKonsul();
    }
});
table.on('draw.dt', updateShownKonsul);

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

    table.ajax.reload();
    loadSummaryKonsul();
});

$('#dataTable tbody').on('click', '.btn-remove', function () {
    const key = $(this).data('id');
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
            url: AppConfig.initGlobal + 'kill/data-konsultasi',
            data: { key },
            dataType: 'json',
            success: function (res) {
                if (!res?.status) return;
                swlSuccess();
                table.ajax.reload(null, false);
                loadSummaryKonsul();
            }
        });
    });
});

$('#dataTable tbody').on('click', '.btn-update', function () {
    let tr = $(this).closest('tr');
    if (tr.hasClass('child')) tr = tr.prev('.parent');

    const row = table.row(tr).data();
    if (!row) return;

    const form = $('#form-usulan');
    $('#DataModalLabel').text('Update Data');
    form.find('[name="key"]').val(row.id);
    form.find('[name="period"]').val((row.period || '').substring(0, 7));
    form.find('[name="syncdate1"]').val(row.period_start_date || row.period_date || '');
    form.find('[name="syncdate2"]').val(row.period_end_date || '');
    form.find('[name="summary"]').val(row.jumlah || '');
    form.find('[name="notes"]').val(row.keterangan || '');

    $('#DataModal').modal('show');
    $('#DataModal').one('shown.bs.modal', function () {
        form.find('[name="media"]').val(row.pelayanan || '').trigger('change');
    });
});
