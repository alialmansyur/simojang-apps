$(document).ready(function () {

    let masterTable;
    let currentType = null;
    const emptyState = (window.ServiceTableUI && ServiceTableUI.createEmptyState)
        ? ServiceTableUI.createEmptyState('Belum ada data master untuk kategori ini.')
        : '<div class="text-center text-muted py-5">Tidak ada data.</div>';
    const processingState = (window.ServiceTableUI && ServiceTableUI.createProcessingState)
        ? ServiceTableUI.createProcessingState('Memuat data master...')
        : '<div class="text-center text-muted py-4">Memuat data...</div>';

    const masterConfig = {
        data_pegawai_pendidikan: {
            title: 'Master Data Pendidikan',
            url: AppConfig.initGlobal + 'fetch/master-pendidikan',
            columns: [
                { title: 'Nama Pendidikan', data: 'nama' }
            ]
        },
        data_pegawai_unit_kerja: {
            title: 'Master Data Unit Kerja',
            url: AppConfig.initGlobal + 'fetch/master-unit-kerja',
            columns: [
                { title: 'Nama Unit Kerja', data: 'nama' }
            ]
        },
        data_pegawai_unit_sk: {
            title: 'Master Data Unit SK',
            url: AppConfig.initGlobal + 'fetch/master-unit-sk',
            columns: [
                { title: 'Nama Unit SK', data: 'nama' }
            ]
        },
        data_pegawai_jenis_jabatan: {
            title: 'Master Data Jenis Jabatan',
            url: AppConfig.initGlobal + 'fetch/master-jenis-jabatan',
            columns: [
                { title: 'Jenis Jabatan', data: 'nama' }
            ]
        },
        data_pegawai_jenis_pegawai: {
            title: 'Master Data Jenis Pegawai',
            url: AppConfig.initGlobal + 'fetch/master-jenis-pegawai',
            columns: [
                { title: 'Jenis Pegawai', data: 'nama' }
            ]
        },
        data_pegawai_agama: {
            title: 'Master Data Agama',
            url: AppConfig.initGlobal + 'fetch/master-agama',
            columns: [
                { title: 'Nama Agama', data: 'nama' }
            ]
        },
        data_pegawai_golongan: {
            title: 'Master Data Golongan',
            url: AppConfig.initGlobal + 'fetch/master-golongan',
            columns: [
                { title: 'Nama Golongan', data: 'nama' }
            ]
        },
        data_pegawai_pangkat: {
            title: 'Master Data Pangkat',
            url: AppConfig.initGlobal + 'fetch/master-pangkat',
            columns: [
                { title: 'Nama Pangkat', data: 'nama' }
            ]
        }                
    };

    // ========================
    // KLIK MASTER CARD
    // ========================
    $('.master-card').on('click', function () {
        const type = $(this).data('type');
        const config = masterConfig[type];
        if (!config) return;

        $('#masterModalLabel').text(config.title);
        $('#masterModal').modal('show');

        initMasterTable(type, config);
    });

    // ========================
    // INIT DATATABLE
    // ========================
    function initMasterTable(type, config) {

        currentType = type;

        // Destroy total + bersihkan thead & tbody
        if ($.fn.DataTable.isDataTable('#masterTable')) {
            masterTable.clear().destroy();
        }

        $('#masterTable thead').empty();
        $('#masterTable tbody').empty();

        const theadHtml = `
            <tr>
                ${config.columns.map(col => `<th><b>${col.title}</b></th>`).join('')}
                <th class="text-end" style="width:120px"></th>
            </tr>
        `;

        $('#masterTable thead').html(theadHtml);


        masterTable = $('#masterTable').DataTable({
        ajax: {
            url: config.url,
            type: 'GET',
            dataSrc: ''
        },
        columns: [
            ...config.columns,
            {
                data: null,
                orderable: false,
                className: 'text-end align-middle',
                render: function (data) {
                    return `
                        <button 
                            class="btn btn-sm bg-light-primary btn-edit"
                            data-id="${data.id}"
                            data-nama="${data.nama}">
                            Ubah Data
                        </button>
                        <button 
                            class="btn btn-sm bg-light-danger btn-delete"
                            data-id="${data.id}">
                            Hapus Data
                        </button>                        
                    `;
                }
            }
        ],
        processing: true,
        responsive: false,
        searching: false,
        paging: false,
        ordering: false,
        info: false,
        autoWidth: false,
        language: {
            emptyTable: (window.ServiceTableUI ? ServiceTableUI.createEmptyState() : 'Tidak ada data'),
            zeroRecords: (window.ServiceTableUI ? ServiceTableUI.createEmptyState() : 'Tidak ada data'),
            processing: processingState
        }
    });

    }

    // ========================
    // TAMBAH DATA
    // ========================
    $('#btnAddMaster').on('click', function () {
        $('#formMaster')[0].reset();
        $('#master_id').val('');
        $('#master_type').val(currentType);
        $('#formMasterTitle').text('Tambah Data');
        $('#formMasterModal').modal('show');
    });

    // ========================
    // EDIT DATA
    // ========================
    $('#masterTable').on('click', '.btn-edit', function () {
        $('#master_id').val($(this).data('id'));
        $('#master_nama').val($(this).data('nama'));
        $('#master_type').val(currentType);

        $('#formMasterTitle').text('Edit Data');
        $('#formMasterModal').modal('show');
    });

    // ========================
    // SUBMIT FORM
    // ========================
    $('#formMaster').on('submit', function (e) {
        e.preventDefault();

        $.ajax({
            url: AppConfig.initGlobal + 'store/save-data-master-statistik',
            type: 'POST',
            data: $(this).serialize(),
            dataType: 'json',
            success: function () {
                $('#formMasterModal').modal('hide');
                swlSuccess();
                masterTable.ajax.reload(null, false);
            }
        });
    });

    // ========================
    // DELETE DATA
    // ========================    
    $('#masterTable').on('click', '.btn-delete', function () {
        const id = $(this).data('id');

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
                    url: AppConfig.initGlobal + 'store/delete-data-master-statistik',
                    type: 'POST',
                    dataType: 'json',
                    data: {
                        id: id,
                        type: currentType
                    },
                    success: function () {
                        swlSuccess();
                        masterTable.ajax.reload(null, false);
                    }
                });
            }
        });
    });


});
