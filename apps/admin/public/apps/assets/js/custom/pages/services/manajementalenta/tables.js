const emptyLottie = (window.ServiceTableUI && ServiceTableUI.createEmptyLottie) ? ServiceTableUI.createEmptyLottie() : '<div class="text-center text-muted py-5">Tidak ada data.</div>';

const processingState = (window.ServiceTableUI && ServiceTableUI.createProcessingState)
    ? ServiceTableUI.createProcessingState('Memuat data manajemen talenta...')
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

function initBulanDropdownPortal() {
    const toggleBtn = document.getElementById('dropdownBulan');
    const menu = document.getElementById('bulanDropdown');
    const dropdownWrap = toggleBtn ? toggleBtn.closest('.dropdown') : null;
    if (!toggleBtn || !menu || !dropdownWrap) return;

    let originalParent = menu.parentElement;
    let originalNextSibling = menu.nextElementSibling;
    let isPortal = false;

    const placeMenu = function () {
        const rect = toggleBtn.getBoundingClientRect();
        menu.style.position = 'absolute';
        menu.style.top = `${rect.bottom + window.scrollY + 6}px`;
        menu.style.left = `${rect.left + window.scrollX}px`;
        menu.style.zIndex = '5000';
    };

    dropdownWrap.addEventListener('shown.bs.dropdown', function () {
        if (!isPortal) {
            originalParent = menu.parentElement;
            originalNextSibling = menu.nextElementSibling;
            document.body.appendChild(menu);
            isPortal = true;
        }
        placeMenu();
    });

    dropdownWrap.addEventListener('hidden.bs.dropdown', function () {
        if (!isPortal) return;

        menu.style.position = '';
        menu.style.top = '';
        menu.style.left = '';
        menu.style.zIndex = '';

        if (originalParent) {
            if (originalNextSibling && originalNextSibling.parentNode === originalParent) {
                originalParent.insertBefore(menu, originalNextSibling);
            } else {
                originalParent.appendChild(menu);
            }
        }

        isPortal = false;
    });

    window.addEventListener('resize', function () {
        if (!isPortal) return;
        placeMenu();
    });

    window.addEventListener('scroll', function () {
        if (!isPortal) return;
        placeMenu();
    }, true);
}

let selectedBulan = [];
initBulanDropdownPortal();
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
        url: AppConfig.initGlobal + 'fetch/data-mt',
        type: 'POST',
        data: function (d) {
            d.layanan = $('#layanan_id').val(), 
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
            render: function(data, type, row) {
                if (data) {
                    return '<img src="apps/assets/images/instansi/' + data + '" alt="logo" style="height:20px;">';
                } else {
                    return '<span class="text-muted">No Logo</span>';
                }
            }
        },        
        { data: 'instansi_name' },
        { data: 'mulai_implemen' }, 
        { data: 'step_name'},
        { data: 'total_percentase', className: 'text-center' },       
        { data: 'diperbaharui' },
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
    }     
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
            .map(b => b.text.substring(0, 3));

        $('#dropdownBulan').text(namaBulan.join(', '));
    } else {
        $('#dropdownBulan').text('Pilih Bulan');
    }

    table.ajax.reload();
});

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
                url: AppConfig.initGlobal + "kill/data-mt",
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

    console.log(row);
 
    const form = $('#form-usulan');

    $('#DataModalLabel').text('Update Data');

    form.find('[name="key"]').val(row.id);
    form.find('[name="period"]').val(row.period);
    form.find('[name="startdate"]').val(row.period_date);

    $('#DataModal').modal('show');
    $('#DataModal').one('shown.bs.modal', function () {
        const instansi = row.instansi_id || '';
        const instansiText = row.instansi_name || ''; 

        const step = row.rw_mt_id || '';
        const stepText = row.step_name || ''; 

        const select = form.find('[name="instansi"]');
        const selectt = form.find('[name="stepProgress"]');

        if (instansi) {
            const option = new Option(instansiText, instansi, true, true);
            select.append(option).trigger('change');
        }

        if (step) {
            const option = new Option(stepText, step, true, true);
            selectt.append(option).trigger('change');
        }
    });

});

