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
    pageLength: 25,   
    lengthMenu: [
        [25, 50, 100, 150, 200],
        [25, 50, 100, 150, 200]
    ],    
    order: [[1, 'asc']],    
    dom: 'Bfrtip',
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
                recapMountSelector: '.page-heading',
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

        setSelect2Value(form, 'status_pegawai', row.status_pegawai, row.status_pegawai);
        setSelect2Value(form, 'agama', row.agama, row.agama);
        setSelect2Value(form, 'pendidikan', row.pendidikan, row.pendidikan);

        setSelect2Value(form, 'unit_kerja', row.unit_kerja_id, row.unit_kerja);
        setSelect2Value(form, 'unit_sk', row.unit_sk_id, row.unit_sk);
        setSelect2Value(form, 'jenis_jabatan', row.jenis_jabatan_id, row.jenis_jabatan);
    });

    $('#DataModal').one('shown.bs.modal', function () {

        const mapSelect = (selectEl, value, text) => {
            if (!value) return;
            const option = new Option(text, value, true, true);
            selectEl.append(option).trigger('change');
        };

        const agama            = row.agama_id || '';
        const agmaText         = row.agama || '';

        const pendidikan       = row.pendidikan_id || '';
        const pendidikanText   = row.pendidikan || '';

        const statusPegawai    = row.status_pegawai_id || '';
        const statusPegawaiText= row.status_pegawai || '';     

        const jenisjabatan     = row.jenis_jabatan_id || '';
        const jenisjabatanText = row.jenis_jabatan || '';             

        const unitKerja        = row.unit_kerja_id || '';
        const unitKerjaText    = row.unit_kerja || '';        

        const unitSK           = row.unit_sk_id || '';
        const unitSKText       = row.unit_sk || '';       
        
        const golongan         = row.gol_id || ''; 
        const golonganText     = row.golongan || '';           

        const pangkat         = row.pangkat_id || '';
        const pangkatText     = row.pangkat || '';                  

        const select1 = form.find('[name="status_pegawai"]');
        const select2 = form.find('[name="agama"]');
        const select3 = form.find('[name="pendidikan"]');
        const select4 = form.find('[name="unit_kerja"]');
        const select5 = form.find('[name="unit_sk"]');
        const select6 = form.find('[name="jenis_jabatan"]');
        const select7 = form.find('[name="golongan"]');
        const select8 = form.find('[name="pangkat"]');

        mapSelect(select1, statusPegawai, statusPegawaiText);
        mapSelect(select2, agama, agmaText);
        mapSelect(select3, pendidikan, pendidikanText);
        mapSelect(select4, unitKerja, unitKerjaText);
        mapSelect(select5, unitSK, unitSKText);
        mapSelect(select6, jenisjabatan, jenisjabatanText);
        mapSelect(select7, golongan, golonganText);
        mapSelect(select8, pangkat, pangkatText);

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
