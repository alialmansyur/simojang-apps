const emptyStateMarkup = `
<div class="d-flex flex-column align-items-center justify-content-center text-center mt-5 mb-5 pb-4 tw-animate-entry">
    <img src="${AppConfig.initGlobal}apps/assets/images/empty-content-profile.png" alt="Tidak Ditemukan" style="max-width: 320px; margin-bottom: 2rem;">
    <h5 class="fw-bold" style="color: #1a202c; font-size: 1.35rem;">Pencarian Tidak Ditemukan</h5>
    <p class="text-muted mb-0" style="font-size: 1.05rem; max-width: 450px; margin: 0 auto; line-height: 1.6;">Tidak ada rekap data yang cocok dengan pencarian Anda.</p>
</div>
`;
const processingState = (window.ServiceTableUI && ServiceTableUI.createProcessingState)
    ? ServiceTableUI.createProcessingState('Memuat data...')
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

function updateServiceUI() {
    $.ajax({
        url: AppConfig.initGlobal + 'apps-karier/get-summary',
        method: 'POST',
        data: {
            bulan: selectedBulan,
        },
        dataType: 'json',
        success: function (res) {
            const s = res?.summary || {};
            if (window.ServiceTableUI) {
                $('#karierd-total-rekap').text(ServiceTableUI.formatNumber(s.total_rekap || 0));
                $('#karierd-total-instansi').text(ServiceTableUI.formatNumber(s.total_instansi || 0));
                $('#karierd-total-peserta').text(ServiceTableUI.formatNumber(s.total_peserta || 0));
                $('#karierd-total-memenuhi').text(ServiceTableUI.formatNumber(s.total_memenuhi || 0));
                $('#karierd-total-tidak-memenuhi').text(ServiceTableUI.formatNumber(s.total_tidak_memenuhi || 0));
                $('#karierd-last-update').text(ServiceTableUI.formatDateTime(s.last_update));
            }
        }
    });
}

const table = $('#dataTable').DataTable({
    responsive: {
        details: { type: 'column', target: 'td.dtr-control' }
    },
    processing: true,
    serverSide: true,
    order: [],
    dom: 'Bfrtip',
    buttons: ['copy', 'excel', 'pdf', 'print'],
    ajax: {
        url: AppConfig.initGlobal + 'apps-karier/get-data',
        type: 'POST',
        data: function (d) {
            d.bulan = selectedBulan;
            return d;
        }
    },
    columnDefs: [{ className: 'dtr-control', targets: 0, orderable: false }],
    columns: [
        { data: null, defaultContent: '' },
        { data: 'instansi_nama', name: 'd.nama' },
        { 
            data: 'tanggal', 
            name: 'a.tanggal',
            className: 'text-center',
            render: function(data) {
                if (!data) return '-';
                const parts = data.split('-');
                if (parts.length === 3) {
                    return `${parts[2]}-${parts[1]}-${parts[0]}`;
                }
                return data;
            }
        },
        { data: 'jenis_penilaian', name: 'a.jenis_penilaian', className: 'text-center' },
        { data: 'total_peserta', name: 'a.total_peserta', className: 'text-center' },
        { data: 'memenuhi', name: 'a.memenuhi', className: 'text-center' },
        { data: 'tidak_memenuhi', name: 'a.tidak_memenuhi', className: 'text-center' },
        { data: 'lulus', name: 'a.lulus', className: 'text-center' },
        { data: 'tidak_lulus', name: 'a.tidak_lulus', className: 'text-center' },
        { data: 'tidak_hadir', name: 'a.tidak_hadir', className: 'text-center' },
        { data: 'created_by', name: 'a.created_by', className: 'text-center' },
        { data: 'created_at', name: 'a.created_at', className: 'text-center' },
        {
            data: null,
            orderable: false,
            searchable: false,
            render: function (data, type, row) {
                return `
                    <button class="btn btn-sm btn-danger btn-remove" data-id="${row.uid}">
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
                key: 'karierd',
                table,
                initialPageSkeleton: false,
                initialTableSkeleton: false,
                initialBackdrop: false,
                pageSkeleton: false,
                tableSkeleton: false,
                backdrop: false,
                disableRecap: true,
                reloadSummaryOnClick: false,
                loadSummary: updateServiceUI
            });
        }
        updateServiceUI();
    }
});

table.on('xhr.dt', function () { updateServiceUI(); });

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

    if (selectedBulan.length > MAX_BULAN) {
        swlErrorHandler('Silakan pilih maksimal 6 bulan saja.');
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
    updateServiceUI();
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

$('#dataTable tbody').on('click', 'tr td .btn-remove', function (e) {
    e.preventDefault();
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
                url: AppConfig.initGlobal + "apps-karier/remove-data",
                data: { key: key },
                dataType: 'json',
                success: function (response) {
                    if (response.status) {
                        swlSuccess();
                        table.ajax.reload(null, false);
                        updateServiceUI();
                    } else {
                        swlErrorHandler(response.message);
                    }
                }
            });
        }
    });
});

$('.js-service-reload').on('click', function() {
    selectedBulan = [];
    $('.bulan-check').prop('checked', false);
    $('#dropdownBulan').text('Pilih Bulan');
    
    updateActiveFiltersLabel();
    table.ajax.reload(null, false);
    updateServiceUI();
});

window.updateServiceUI = updateServiceUI; // Expose for entry.js
