const emptyLottie = (window.ServiceTableUI && ServiceTableUI.createEmptyLottie)
    ? ServiceTableUI.createEmptyLottie()
    : '<div class="text-center text-muted py-5">Tidak ada data.</div>';
const processingState = (window.ServiceTableUI && ServiceTableUI.createProcessingState)
    ? ServiceTableUI.createProcessingState('Memuat data IKPA...')
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

function updateShownIKPA() {
    const info = table.page.info();
    $('#ikpa-data-shown').text(ServiceTableUI.formatNumber((info && info.recordsDisplay) || 0));
}

function loadSummaryIKPA() {
    $.ajax({
        url: AppConfig.initGlobal + 'fetch/summary-ikpa',
        type: 'POST',
        dataType: 'json',
        data: { bulan: selectedBulan },
        success: function (response) {
            const s = response?.summary || {};
            $('#ikpa-total-data').text(ServiceTableUI.formatNumber(s.total_data || 0));
            $('#ikpa-rerata').text(Number(s.rata_nilai_akhir || 0).toFixed(2));
            $('#ikpa-total-nilai').text(ServiceTableUI.formatNumber(s.total_nilai || 0, 2));
            $('#ikpa-bulan-dipilih').text(ServiceTableUI.formatNumber(selectedBulan.length || 0));
            $('#ikpa-last-update').text(ServiceTableUI.formatDateTime(s.last_update));
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
        url: AppConfig.initGlobal + 'fetch/data-ikpa',
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
            data: 'nama_kategori',
            className: 'text-center',
            render: function (data, type, row) {
                if (type !== 'display') return data;
                let colorClass = 'bg-light-secondary';
                switch (row.warna) {
                    case 'biru': colorClass = 'bg-light-primary'; break;
                    case 'biru-muda': colorClass = 'bg-light-info'; break;
                    case 'kuning': colorClass = 'bg-light-warning'; break;
                    case 'merah': colorClass = 'bg-light-danger'; break;
                }
                return `<span class="badge ${colorClass}">${data}</span>`;
            }
        },
        { data: 'period', className: 'text-center' },
        { data: 'period_start_date', className: 'text-center' },
        { data: 'period_end_date', className: 'text-center' },
        { data: 'uraian_satker' },
        { data: 'kode_kppn' },
        { data: 'kode_ba' },
        { data: 'nilai_total', className: 'text-center' },
        { data: 'konversi_bobot', className: 'text-center' },
        { data: 'dispensasi_spm', className: 'text-center' },
        { data: 'nilai_akhir', className: 'text-center' },
        { data: 'created_at' },
        {
            data: null,
            orderable: false,
            searchable: false,
            render: function (_, __, row) {
                return `<button class="btn btn-sm btn-danger btn-remove" data-id="${row.id}"><i class='bi bi-trash'></i></button>`;
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
                key: 'ikpa',
                table,
                recapMountSelector: '.page-heading',
                loadSummary: loadSummaryIKPA,
                cards: [
                    { id: 'total-data', label: 'Total Upload', value: '0' },
                    { id: 'rerata', label: 'Rata-rata Nilai', value: '0.00' },
                    { id: 'total-nilai', label: 'Akumulasi Nilai', value: '0.00' },
                    { id: 'bulan-dipilih', label: 'Bulan Dipilih', value: '0' },
                    { id: 'data-shown', label: 'Data Ditampilkan', value: '0' },
                    { id: 'last-update', label: 'Update Terakhir', value: '-' }
                ]
            });
        }
        updateShownIKPA();
        loadSummaryIKPA();
    }
});
table.on('draw.dt', updateShownIKPA);

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
    loadSummaryIKPA();
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

$('#dataTable tbody').on('click', '.btn-remove', function () {
    const key = $(this).attr('data-id');
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
            url: AppConfig.initGlobal + 'kill/data-ikpa',
            data: { key },
            success: function (response) {
                if (!response) return;
                swlSuccess();
                table.ajax.reload(null, false);
                loadSummaryIKPA();
            }
        });
    });
});
