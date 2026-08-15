const emptyLottie = (window.ServiceTableUI && ServiceTableUI.createEmptyLottie)
    ? ServiceTableUI.createEmptyLottie()
    : '<div class="text-center text-muted py-5">Tidak ada data.</div>';
const processingState = (window.ServiceTableUI && ServiceTableUI.createProcessingState)
    ? ServiceTableUI.createProcessingState('Memuat data statistik internal...')
    : '<div class="text-center text-muted py-4">Memuat data...</div>';

let tableMode = 'pegawai'; // default
function updateShownStkin() {
    const info = table.page.info();
    $('#stkin-data-shown').text(ServiceTableUI.formatNumber((info && info.recordsDisplay) || 0));
}

function loadSummaryStatistikInternal() {
    $.ajax({
        url: AppConfig.initGlobal + 'fetch/summary-statistik-internal',
        type: 'POST',
        dataType: 'json',
        data: {
            unit: selectedData,
            mode: tableMode
        },
        success: function (response) {
            const s = response?.summary || {};
            $('#stkin-total-data').text(ServiceTableUI.formatNumber(s.total_data || 0));
            $('#stkin-last-update').text(ServiceTableUI.formatDateTime(s.last_update));
        }
    });
}

window.loadSummaryStatistikInternal = loadSummaryStatistikInternal;

const table = $('#dataTable').DataTable({
    responsive: {
        details: { type: 'column', target: 'td.dtr-control' }
    },
    processing: true,
    serverSide: true,
    lengthChange: true,
    pageLength: 10,   
    lengthMenu: [
        [10, 25, 50, 100],
        [10, 25, 50, 100]
    ],    
    order: [[1, 'asc']],    
    // dom: 'lBfrtip',
    buttons: ['copy', 'excel', 'pdf', 'print'],
    ajax: {
        url: AppConfig.initGlobal + 'fetch/data-statistik-pegawai',
        type: 'POST',
        data: function (d) {
            d.unit = selectedData;
            d.mode = tableMode;
        }
    },
    columnDefs: [{ className: 'dtr-control', targets: 0, orderable: false }],
    columns: [
        { data: null, defaultContent: '' },
        { data: 'nip' },   
        { data: 'nama' },   
        {
            data: 'gender',
            render: function (data, type, row) {
                if (type === 'display' || type === 'filter') {
                    return data == 1 ? 'Pria' :
                        data == 2 ? 'Wanita' : '-';
                }
                return data; 
            }
        },
        {
            data: 'generasi',
            render: function (data, type) {

                if (type !== 'display') return data;

                let color = 'dark';

                switch (data) {
                    case 'Baby Boomer':
                        color = 'secondary';
                        break;
                    case 'Gen X':
                        color = 'primary';
                        break;
                    case 'Gen Y':
                        color = 'success';
                        break;
                    case 'Gen Z':
                        color = 'warning';
                        break;
                    case 'Gen Alpha':
                        color = 'info';
                        break;
                    default:
                        color = 'dark';
                }

                return `<span class="badge bg-${color}">${data}</span>`;
            }
        },
        { data: 'tgl_lahir' },   
        { data: 'unit_kerja' },   
        { data: 'unit_sk' },   
        { data: 'jenis_jabatan' },   
        { data: 'jabatan' },   
        { data: 'menikah' },   
        { data: 'agama' },           
        { data: 'pendidikan' },   
        { data: 'golongan' },   
        { data: 'pangkat' },   
        { data: 'tmt_gol' },   
        { data: 'phone' },   
        { data: 'email' },   
        { data: 'updated_at' },   
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
                key: 'stkin',
                table,

                loadSummary: loadSummaryStatistikInternal,
                cards: [
                    { id: 'total-data', label: 'Total Pegawai', value: '0' },
                    { id: 'data-shown', label: 'Data Ditampilkan', value: '0' },
                    { id: 'last-update', label: 'Update Terakhir', value: '-' }
                ]
            });
        }
        updateShownStkin();
        loadSummaryStatistikInternal();
    }
});
table.on('draw.dt', updateShownStkin);

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
                url: AppConfig.initGlobal + "kill/data-statistik-internal",
                data: {
                    key: key
                },
                success: function (response) {
                    if (response) {
                        swlSuccess();
                        table.ajax.reload(null, false);
                        loadSummaryStatistikInternal();
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

    const form = $('#form-usulan');

    $('#DataModalLabel').text('Update Data Pegawai');

    // ========================
    // SET VALUE INPUT
    // ========================
    form.find('[name="key"]').val(row.id);
    form.find('[name="nip"]').val(row.nip);
    form.find('[name="nama"]').val(row.nama);
    form.find('[name="gender"]').val(row.gender);
    form.find('[name="tgl_lahir"]').val(row.tgl_lahir);
    form.find('[name="menikah"]').val(row.menikah);
    form.find('[name="jabatan"]').val(row.jabatan);
    form.find('[name="gol"]').val(row.gol);
    form.find('[name="pangkat"]').val(row.pangkat);
    form.find('[name="tmt_gol"]').val(row.tmt_gol);
    form.find('[name="phone"]').val(row.phone);
    form.find('[name="email"]').val(row.email);

    $('#DataModal').modal('show');

    // ========================
    // SET SELECT2 (DYNAMIC)
    // ========================
    $('#DataModal').one('shown.bs.modal', function () {

        const mapSelect = (selectEl, value, text) => {
            if (!value) return;
            selectEl.empty();
            if (value.toString().includes(',')) {
                const vals = value.toString().split(',');
                const txts = text.toString().split(',');
                for (let i = 0; i < vals.length; i++) {
                    const option = new Option(txts[i].trim(), vals[i].trim(), true, true);
                    selectEl.append(option);
                }
                selectEl.trigger('change');
            } else {
                const option = new Option(text, value, true, true);
                selectEl.append(option).trigger('change');
            }
        };

        mapSelect(form.find('[name="agama"]'), row.agama_id || '', row.agama || '');
        mapSelect(form.find('[name="pendidikan"]'), row.pendidikan_id || '', row.pendidikan || '');
        mapSelect(form.find('[name="status_pegawai"]'), row.status_pegawai_id || '', row.status_pegawai || '');
        mapSelect(form.find('[name="unit_kerja[]"]'), row.unit_kerja_id || '', row.unit_kerja || '');
        mapSelect(form.find('[name="unit_sk"]'), row.unit_sk_id || '', row.unit_sk || '');
        mapSelect(form.find('[name="jenis_jabatan"]'), row.jenis_jabatan_id || '', row.jenis_jabatan || '');
        mapSelect(form.find('[name="jabatan"]'), row.jabatan_id || '', row.jabatan || '');
        mapSelect(form.find('[name="golongan"]'), row.gol_id || '', row.golongan || '');
        mapSelect(form.find('[name="pangkat"]'), row.pangkat_id || '', row.pangkat || '');

    });


});

function setSelect2Value(form, name, id, text) {
    if (!id) return;

    const select = form.find('[name="' + name + '"]');
    const option = new Option(text, id, true, true);
    select.append(option).trigger('change');
}

$('#pegawaiTab button').on('click', function () {
    tableMode = $(this).data('mode');
    table.ajax.reload(null, false);
    loadSummaryStatistikInternal();
});
