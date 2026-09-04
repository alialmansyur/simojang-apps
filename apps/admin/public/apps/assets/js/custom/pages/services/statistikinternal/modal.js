$(document).ready(function () {

    let masterTable;
    let currentType = null;
    let currentJabatanStatus = '';
    const emptyState = (window.ServiceTableUI && ServiceTableUI.createEmptyState)
        ? ServiceTableUI.createEmptyState('Belum ada data master untuk kategori ini.')
        : '<div class="text-center text-muted py-5">Tidak ada data.</div>';
    const processingState = (window.ServiceTableUI && ServiceTableUI.createProcessingState)
        ? ServiceTableUI.createProcessingState('Memuat data master...')
        : '<div class="text-center text-muted py-4">Memuat data...</div>';

    // ========================
    // FILTER JABATAN (KRG / UPT)
    // ========================
    $('#masterJabatanFilter').on('click', 'button', function () {
        $('#masterJabatanFilter button').removeClass('active');
        $(this).addClass('active');
        currentJabatanStatus = $(this).data('status') || '';
        if (currentType === 'data_pegawai_jabatan' && masterTable) {
            masterTable.ajax.reload(null, true);
        }
    });

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
        data_pegawai_jabatan: {
            title: 'Master Data Jabatan',
            url: AppConfig.initGlobal + 'fetch/master-jabatan',
            columns: [
                { 
                    title: 'No', 
                    data: null,
                    className: 'text-center align-middle',
                    render: function (data, type, row, meta) {
                        return (meta.row + (meta.settings._iDisplayStart || 0) + 1);
                    }
                },
                { title: 'Nama Jabatan', data: 'nama', className: 'align-middle' },
                { title: 'Kelas Jabatan', data: 'kelas_jabatan', className: 'text-center align-middle', defaultContent: '-' },
                { title: 'Formasi', data: 'formasi', className: 'text-center align-middle', defaultContent: '0' },
                { title: 'Bezzeting', data: 'bezzeting', className: 'text-center align-middle', defaultContent: '0' },
                { title: 'Lowongan', data: 'lowongan', className: 'text-center align-middle', defaultContent: '0' },
                { 
                    title: 'Keterangan', 
                    data: 'keterangan',
                    className: 'text-center align-middle',
                    render: function (data) {
                        if (data === 'Memenuhi') {
                            return '<span class="badge bg-light-success text-success fw-bold">Memenuhi</span>';
                        } else if (data === 'Kelebihan') {
                            return '<span class="badge bg-light-danger text-danger fw-bold">Kelebihan</span>';
                        } else {
                            return '<span class="badge bg-light-warning text-warning fw-bold">Kekurangan</span>';
                        }
                    }
                }
            ],
            // Define extra fields for the form (beyond 'nama')
            fields: [
                { name: 'kelas_jabatan', label: 'Kelas Jabatan', type: 'number' },
                { name: 'kebutuhan', label: 'Formasi (Kebutuhan)', type: 'number' },
                { 
                    name: 'is_status', 
                    label: 'Unit / Grouping', 
                    type: 'select',
                    options: [
                        { value: 'KRG', text: 'Kantor Regional III BKN (KRG)' },
                        { value: 'UPT', text: 'UPSCPKP ASN Serang (UPT)' }
                    ]
                }
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

        if (type === 'data_pegawai_jabatan') {
            $('#masterJabatanFilterWrapper').show();
            $('#masterJabatanFilter button').removeClass('active');
            $('#masterJabatanFilter button[data-status=""]').addClass('active');
            currentJabatanStatus = '';
        } else {
            $('#masterJabatanFilterWrapper').hide();
            currentJabatanStatus = '';
        }

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
                data: function (d) {
                    if (type === 'data_pegawai_jabatan' && currentJabatanStatus) {
                        d.is_status = currentJabatanStatus;
                    }
                },
                dataSrc: ''
            },
            columns: [
                ...config.columns,
                {
                    data: null,
                    orderable: false,
                    className: 'text-end align-middle',
                    render: function (data) {
                        // Encode all row data as JSON in data-row attribute for edit
                        const rowJson = encodeURIComponent(JSON.stringify(data));
                        return `
                        <button 
                            class="btn btn-sm bg-light-primary btn-edit"
                            data-id="${data.id}"
                            data-row="${rowJson}">
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
            searching: true,
            paging: true,
            pageLength: 10,
            ordering: false,
            info: true,
            autoWidth: false,
            language: {
                emptyTable: (window.ServiceTableUI ? ServiceTableUI.createEmptyState() : 'Tidak ada data'),
                zeroRecords: (window.ServiceTableUI ? ServiceTableUI.createEmptyState() : 'Tidak ada data'),
                processing: processingState
            }
        });

    }

    // ========================
    // BUILD DYNAMIC FORM FIELDS
    // ========================
    function buildFormFields() {
        const config = masterConfig[currentType];
        let html = '';

        // Always show 'nama' field
        html += `
            <div class="mb-2">
                <label class="form-label">Nama</label>
                <input type="text" class="form-control" name="nama" id="master_nama" required>
            </div>
        `;

        // Add extra fields if defined
        if (config && config.fields) {
            config.fields.forEach(function (field) {
                if (field.type === 'select') {
                    const opts = (field.options || []).map(opt => `<option value="${opt.value}">${opt.text}</option>`).join('');
                    html += `
                        <div class="mb-2">
                            <label class="form-label">${field.label}</label>
                            <select class="form-select" name="${field.name}" id="master_${field.name}">
                                ${opts}
                            </select>
                        </div>
                    `;
                } else {
                    const inputType = field.type || 'text';
                    html += `
                        <div class="mb-2">
                            <label class="form-label">${field.label}</label>
                            <input type="${inputType}" class="form-control" name="${field.name}" id="master_${field.name}">
                        </div>
                    `;
                }
            });
        }

        $('#formMasterDynamicFields').html(html);
    }

    // ========================
    // TAMBAH DATA
    // ========================
    $('#btnAddMaster').on('click', function () {
        buildFormFields();
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
        const rowData = JSON.parse(decodeURIComponent($(this).data('row')));

        buildFormFields();

        $('#master_id').val(rowData.id);
        $('#master_type').val(currentType);
        $('#master_nama').val(rowData.nama);

        // Fill extra fields if any
        const config = masterConfig[currentType];
        if (config && config.fields) {
            config.fields.forEach(function (field) {
                let val = rowData[field.name];
                if (field.name === 'kebutuhan' && (val === undefined || val === null)) {
                    val = rowData.formasi;
                }
                $('#master_' + field.name).val(val !== undefined && val !== null ? val : '');
            });
        }

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
            text: "Apa anda yakin akan menghapus data ini ?",
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
