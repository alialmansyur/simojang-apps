$(document).ready(function () {
    try {
        console.log("IKK Main v3 loaded");
    // -------------------------------------------------------------------------
    // SELECT2 HELPERS
    // -------------------------------------------------------------------------
    function initSelect2() {
        $('.select2-modal').select2({ dropdownParent: $('.modal.show'), width: '100%' });
    }

    function loadOptions(url, targetSelect, extraData = {}, callback = null) {
        $.post(AppConfig.initGlobal + url, extraData, function (res) {
            let html = '<option value="">-- Pilih --</option>';
            res.forEach(item => {
                html += `<option value="${item.id}">${item.text}</option>`;
            });
            $(targetSelect).html(html);
            if (callback) callback();
        });
    }

    // -------------------------------------------------------------------------
    // MAIN TABLE: SASARAN
    // -------------------------------------------------------------------------
    let dtSasaran = $('#table-sasaran').DataTable({
        processing: true, serverSide: true, responsive: false,
        dom: 'rtip', // Hide default search bar to use the custom one
        ajax: { url: AppConfig.initGlobal + 'fetch/ikk-sasaran', type: 'POST' },
        columns: [
            { 
                className: 'details-control', orderable: false, data: null, defaultContent: '<i class="bi bi-chevron-right"></i>' 
            },
            { data: 'no_urut' },
            { data: 'nama_sasaran' },
            {
                data: 'id', orderable: false, searchable: false,
                render: function (data) {
                    return `
                        <button class="btn btn-sm btn-warning btn-edit-sasaran" data-id="${data}" title="Edit"><i class="bi bi-pencil"></i></button>
                        <button class="btn btn-sm btn-danger btn-delete-sasaran" data-id="${data}" title="Hapus"><i class="bi bi-trash"></i></button>
                        <button class="btn btn-sm btn-info btn-add-indikator-inline" data-id="${data}" title="Tambah Indikator"><i class="bi bi-plus-circle"></i></button>
                    `;
                }
            }
        ]
    });

    $('#searchInput').on('keyup', function () {
        dtSasaran.search(this.value).draw();
    });

    // Sub-Table Formats
    function formatIndikator(sasaranId) {
        return `
            <div class="nested-table-container">
                <h6 class="mb-2 text-primary"><i class="bi bi-diagram-3 me-1"></i> Data Indikator</h6>
                <table class="table table-sm table-bordered w-100" id="table-indikator-${sasaranId}">
                    <thead class="table-light">
                        <tr>
                            <th></th>
                            <th>No Urut</th>
                            <th>Nama Indikator</th>
                            <th>Satuan</th>
                            <th>Target Vol</th>
                            <th style="width:120px;">Aksi</th>
                        </tr>
                    </thead>
                    <tbody></tbody>
                </table>
            </div>
        `;
    }

    function formatKegiatan(indikatorId) {
        return `
            <div class="nested-table-container">
                <h6 class="mb-2 text-success"><i class="bi bi-diagram-2 me-1"></i> Data Kegiatan</h6>
                <table class="table table-sm table-bordered w-100" id="table-kegiatan-${indikatorId}">
                    <thead class="table-light">
                        <tr>
                            <th></th>
                            <th>No Urut</th>
                            <th>Nama Kegiatan</th>
                            <th>Satuan</th>
                            <th>Target Vol</th>
                            <th>Tim Kerja / Pengampu</th>
                            <th style="width:230px;">Aksi</th>
                        </tr>
                    </thead>
                    <tbody></tbody>
                </table>
            </div>
        `;
    }

    function formatTransaksi(kegiatanId) {
        return `
            <div class="nested-table-container">
                <div class="d-flex justify-content-between mb-2">
                    <h6 class="text-danger mb-0"><i class="bi bi-journal-text me-1"></i> Transaksi Harian</h6>
                    <button class="btn btn-sm btn-danger btn-add-transaksi-inline" data-id="${kegiatanId}"><i class="bi bi-plus"></i> Input Transaksi</button>
                </div>
                <table class="table table-sm table-bordered w-100" id="table-transaksi-${kegiatanId}">
                    <thead class="table-light">
                        <tr>
                            <th>Pelaksana</th>
                            <th>Periode</th>
                            <th>Target Mgn</th>
                            <th>Realisasi</th>
                            <th>Progress (%)</th>
                            <th style="width:100px;">Aksi</th>
                        </tr>
                    </thead>
                    <tbody></tbody>
                </table>
            </div>
        `;
    }

    // EXPAND SASARAN -> SHOW INDIKATOR
    $('#table-sasaran tbody').on('click', 'td.details-control', function () {
        let tr = $(this).closest('tr');
        let row = dtSasaran.row(tr);
        let sasaranId = row.data().id;

        if (row.child.isShown()) {
            row.child.hide();
            tr.removeClass('shown');
        } else {
            row.child(formatIndikator(sasaranId)).show();
            tr.addClass('shown');
            
            // Init Indikator DataTable
            $(`#table-indikator-${sasaranId}`).DataTable({
                processing: true, serverSide: true, responsive: false, lengthChange: false, info: false,
                ajax: { 
                    url: AppConfig.initGlobal + 'fetch/ikk-indikator', 
                    type: 'POST',
                    data: function(d) { d.sasaran_id = sasaranId; }
                },
                columns: [
                    { className: 'details-control-indikator text-center text-success', orderable: false, data: null, defaultContent: '<i class="bi bi-chevron-right" style="cursor:pointer; width:30px;"></i>' },
                    { data: 'no_urut' },
                    { data: 'nama_indikator' },
                    { data: 'satuan' },
                    { data: 'target_volume' },
                    {
                        data: 'id', orderable: false, searchable: false,
                        render: function (data) {
                            return `
                                <button class="btn btn-sm btn-warning btn-edit-indikator" data-id="${data}" title="Edit"><i class="bi bi-pencil"></i></button>
                                <button class="btn btn-sm btn-danger btn-delete-indikator" data-id="${data}" title="Hapus"><i class="bi bi-trash"></i></button>
                                <button class="btn btn-sm btn-success btn-add-kegiatan-inline" data-id="${data}" title="Tambah Kegiatan"><i class="bi bi-plus-circle"></i></button>
                            `;
                        }
                    }
                ]
            });
        }
    });

    // EXPAND INDIKATOR -> SHOW KEGIATAN
    $('#table-sasaran tbody').on('click', 'td.details-control-indikator', function () {
        let tableIndikator = $(this).closest('table').DataTable();
        let tr = $(this).closest('tr');
        let row = tableIndikator.row(tr);
        let indikatorId = row.data().id;

        if (row.child.isShown()) {
            row.child.hide();
            tr.find('i.bi').css('transform', 'rotate(0deg)');
        } else {
            row.child(formatKegiatan(indikatorId)).show();
            tr.find('i.bi').css('transform', 'rotate(90deg)');
            
            // Init Kegiatan DataTable
            $(`#table-kegiatan-${indikatorId}`).DataTable({
                processing: true, serverSide: true, responsive: false, lengthChange: false, info: false,
                ajax: { 
                    url: AppConfig.initGlobal + 'fetch/ikk-kegiatan', 
                    type: 'POST',
                    data: function(d) { d.indikator_id = indikatorId; }
                },
                columns: [
                    { className: 'details-control-kegiatan text-center text-danger', orderable: false, data: null, defaultContent: '<i class="bi bi-chevron-right" style="cursor:pointer; width:30px;"></i>' },
                    { data: 'no_urut' },
                    { data: 'nama_kegiatan' },
                    { data: 'satuan' },
                    { data: 'target_volume' },
                    { data: 'pengampu_list' },
                    {
                        data: 'id', orderable: false, searchable: false,
                        render: function (data) {
                            return `
                                <button class="btn btn-sm btn-secondary btn-assign-pengampu-inline" data-id="${data}" title="Atur Pengampu"><i class="bi bi-people"></i></button>
                                <button class="btn btn-sm btn-warning btn-edit-kegiatan" data-id="${data}" title="Edit"><i class="bi bi-pencil"></i></button>
                                <button class="btn btn-sm btn-danger btn-delete-kegiatan" data-id="${data}" title="Hapus"><i class="bi bi-trash"></i></button>
                            `;
                        }
                    }
                ]
            });
        }
    });

    // EXPAND KEGIATAN -> SHOW TRANSAKSI HARIAN
    $('#table-sasaran tbody').on('click', 'td.details-control-kegiatan', function () {
        let tableKegiatan = $(this).closest('table').DataTable();
        let tr = $(this).closest('tr');
        let row = tableKegiatan.row(tr);
        let kegiatanId = row.data().id;

        if (row.child.isShown()) {
            row.child.hide();
            tr.find('i.bi').css('transform', 'rotate(0deg)');
        } else {
            row.child(formatTransaksi(kegiatanId)).show();
            tr.find('i.bi').css('transform', 'rotate(90deg)');
            
            // Init Transaksi DataTable
            $(`#table-transaksi-${kegiatanId}`).DataTable({
                processing: true, serverSide: true, responsive: false, lengthChange: false, info: false, searching: false,
                ajax: { 
                    url: AppConfig.initGlobal + 'fetch/ikk-transaksi', 
                    type: 'POST',
                    data: function(d) { d.kegiatan_id = kegiatanId; }
                },
                columns: [
                    { data: 'pelaksana_kegiatan_harian' },
                    { data: 'period_start_date', render: function(data, type, row){ return data ? data + ' s.d ' + row.period_end_date : '-'; } },
                    { data: 'target_mingguan' },
                    { data: 'jumlah_realisasi' },
                    { data: 'progres_realisasi' },
                    {
                        data: 'id', orderable: false, searchable: false,
                        render: function (data) {
                            return `
                                <button class="btn btn-sm btn-warning btn-edit-transaksi" data-id="${data}" data-kegiatan="${kegiatanId}" title="Edit"><i class="bi bi-pencil"></i></button>
                                <button class="btn btn-sm btn-danger btn-delete-transaksi" data-id="${data}" data-kegiatan="${kegiatanId}" title="Hapus"><i class="bi bi-trash"></i></button>
                            `;
                        }
                    }
                ]
            });
        }
    });

    $('#btn-reload-tables').click(function () {
        dtSasaran.ajax.reload(null, false);
        Swal.fire({icon: 'success', text: 'Data dimuat ulang', timer: 1500, showConfirmButton: false});
    });

    // -------------------------------------------------------------------------
    // CRUD SASARAN
    // -------------------------------------------------------------------------
    $('.btn-add-sasaran').click(function () {
        $('#form-sasaran')[0].reset();
        $('#sasaran_key').val('');
        $('#modal-sasaran').modal('show');
    });

    $('#form-sasaran').submit(function (e) {
        e.preventDefault();
        $.post(AppConfig.initGlobal + 'store/ikk-sasaran', $(this).serialize(), function (res) {
            if (res.status === 'success') {
                Swal.fire({icon: 'success', text: res.message, timer: 1500, showConfirmButton: false});
                $('#modal-sasaran').modal('hide');
                dtSasaran.ajax.reload(null, false);
            } else { Swal.fire({icon: 'error', title: 'Gagal', text: res.message}); }
        });
    });

    $('#table-sasaran').on('click', '.btn-edit-sasaran', function () {
        let row = dtSasaran.row($(this).parents('tr')).data();
        $('#sasaran_key').val(row.id);
        $('#sasaran_no_urut').val(row.no_urut);
        $('#sasaran_nama_sasaran').val(row.nama_sasaran);
        $('#modal-sasaran').modal('show');
    });

    $('#table-sasaran').on('click', '.btn-delete-sasaran', function () {
        let id = $(this).data('id');
        Swal.fire({
            title: 'Hapus Sasaran?', text: "Data indikator dan kegiatan dibawahnya akan ikut terhapus!",
            icon: 'warning', showCancelButton: true, confirmButtonText: 'Ya, Hapus'
        }).then((result) => {
            if (result.isConfirmed) {
                $.post(AppConfig.initGlobal + 'kill/ikk-sasaran', { key: id }, function (res) {
                    if (res.status) { Swal.fire({icon: 'success', text: res.message, timer: 1500, showConfirmButton: false}); dtSasaran.ajax.reload(null, false); } 
                    else { Swal.fire({icon: 'error', title: 'Gagal', text: res.message}); }
                });
            }
        });
    });

    // -------------------------------------------------------------------------
    // CRUD INDIKATOR
    // -------------------------------------------------------------------------
    $('#table-sasaran').on('click', '.btn-add-indikator-inline', function () {
        let sasaranId = $(this).data('id');
        $('#form-indikator')[0].reset();
        $('#indikator_key').val('');
        $('#indikator_sasaran_id_hidden').val(sasaranId);
        $('#modal-indikator').modal('show');
    });

    $('#form-indikator').submit(function (e) {
        e.preventDefault();
        let sasaranId = $('#indikator_sasaran_id_hidden').val();
        let payload = $(this).serialize() + '&sasaran_id=' + sasaranId;
        $.post(AppConfig.initGlobal + 'store/ikk-indikator', payload, function (res) {
            if (res.status === 'success') {
                Swal.fire({icon: 'success', text: res.message, timer: 1500, showConfirmButton: false});
                $('#modal-indikator').modal('hide');
                $(`#table-indikator-${sasaranId}`).DataTable().ajax.reload(null, false);
            } else { Swal.fire({icon: 'error', title: 'Gagal', text: res.message}); }
        });
    });

    $('#table-sasaran').on('click', '.btn-edit-indikator', function () {
        let tableIndikator = $(this).closest('table').DataTable();
        let row = tableIndikator.row($(this).parents('tr')).data();
        $('#indikator_key').val(row.id);
        $('#indikator_sasaran_id_hidden').val(row.sasaran_id);
        $('#indikator_no_urut').val(row.no_urut);
        $('#indikator_nama_indikator').val(row.nama_indikator);
        $('#indikator_satuan').val(row.satuan);
        $('#indikator_target_volume').val(row.target_volume);
        $('#modal-indikator').modal('show');
    });

    $('#table-sasaran').on('click', '.btn-delete-indikator', function () {
        let id = $(this).data('id');
        let sasaranId = $(this).closest('table').attr('id').replace('table-indikator-', '');
        Swal.fire({
            title: 'Hapus Indikator?', text: "Data kegiatan dibawahnya akan ikut terhapus!",
            icon: 'warning', showCancelButton: true, confirmButtonText: 'Ya, Hapus'
        }).then((result) => {
            if (result.isConfirmed) {
                $.post(AppConfig.initGlobal + 'kill/ikk-indikator', { key: id }, function (res) {
                    if (res.status) { Swal.fire({icon: 'success', text: res.message, timer: 1500, showConfirmButton: false}); $(`#table-indikator-${sasaranId}`).DataTable().ajax.reload(null, false); } 
                    else { Swal.fire({icon: 'error', title: 'Gagal', text: res.message}); }
                });
            }
        });
    });

    // -------------------------------------------------------------------------
    // CRUD KEGIATAN
    // -------------------------------------------------------------------------
    $('#table-sasaran').on('click', '.btn-add-kegiatan-inline', function () {
        let indikatorId = $(this).data('id');
        $('#form-kegiatan')[0].reset();
        $('#kegiatan_key').val('');
        $('#kegiatan_indikator_id_hidden').val(indikatorId);
        $('#modal-kegiatan').modal('show');
    });

    $('#form-kegiatan').submit(function (e) {
        e.preventDefault();
        let indikatorId = $('#kegiatan_indikator_id_hidden').val();
        let payload = $(this).serialize() + '&indikator_id=' + indikatorId;
        $.post(AppConfig.initGlobal + 'store/ikk-kegiatan', payload, function (res) {
            if (res.status === 'success') {
                Swal.fire({icon: 'success', text: res.message, timer: 1500, showConfirmButton: false});
                $('#modal-kegiatan').modal('hide');
                $(`#table-kegiatan-${indikatorId}`).DataTable().ajax.reload(null, false);
            } else { Swal.fire({icon: 'error', title: 'Gagal', text: res.message}); }
        });
    });

    $('#table-sasaran').on('click', '.btn-edit-kegiatan', function () {
        let tableKegiatan = $(this).closest('table').DataTable();
        let row = tableKegiatan.row($(this).parents('tr')).data();
        $('#kegiatan_key').val(row.id);
        $('#kegiatan_indikator_id_hidden').val(row.indikator_id);
        $('#kegiatan_no_urut').val(row.no_urut);
        $('#kegiatan_nama_kegiatan').val(row.nama_kegiatan);
        $('#kegiatan_satuan').val(row.satuan);
        $('#kegiatan_target_volume').val(row.target_volume);
        $('#modal-kegiatan').modal('show');
    });

    $('#table-sasaran').on('click', '.btn-delete-kegiatan', function () {
        let id = $(this).data('id');
        let indikatorId = $(this).closest('table').attr('id').replace('table-kegiatan-', '');
        Swal.fire({
            title: 'Hapus Kegiatan?', text: "Yakin ingin menghapus kegiatan ini?",
            icon: 'warning', showCancelButton: true, confirmButtonText: 'Ya, Hapus'
        }).then((result) => {
            if (result.isConfirmed) {
                $.post(AppConfig.initGlobal + 'kill/ikk-kegiatan', { key: id }, function (res) {
                    if (res.status) { Swal.fire({icon: 'success', text: res.message, timer: 1500, showConfirmButton: false}); $(`#table-kegiatan-${indikatorId}`).DataTable().ajax.reload(null, false); } 
                    else { Swal.fire({icon: 'error', title: 'Gagal', text: res.message}); }
                });
            }
        });
    });

    // -------------------------------------------------------------------------
    // CRUD PENGAMPU
    // -------------------------------------------------------------------------
    $('#table-sasaran').on('click', '.btn-assign-pengampu-inline', function () {
        let kegiatanId = $(this).data('id');
        $('#form-pengampu')[0].reset();
        $('#pengampu_key').val('');
        $('#pengampu_kegiatan_id_hidden').val(kegiatanId);
        
        $('#pengampu_timkerja_id').val(null).trigger('change');
        loadOptions('fetch/ikk-options-timkerja', '#pengampu_timkerja_id');
        $('#modal-pengampu').modal('show');
        setTimeout(initSelect2, 200);
    });

    $('#form-pengampu').submit(function (e) {
        e.preventDefault();
        let kegiatanId = $('#pengampu_kegiatan_id_hidden').val();
        $.post(AppConfig.initGlobal + 'store/ikk-pengampu', $(this).serialize(), function (res) {
            if (res.status === 'success') {
                Swal.fire({icon: 'success', text: res.message, timer: 1500, showConfirmButton: false});
                $('#modal-pengampu').modal('hide');
                // We reload the Kegiatan table to reflect the new pengampu list
                let tableKegiatan = $(`#table-kegiatan-${$(`#table-kegiatan-${kegiatanId}`).closest('table').attr('id') || ''}`).DataTable();
                if(tableKegiatan) {
                    // This finds the closest parent DataTable of the button or we can just find any table-kegiatan that is visible
                    $('.nested-table-container table[id^="table-kegiatan-"]').each(function(){
                        if($.fn.DataTable.isDataTable(this)) {
                            $(this).DataTable().ajax.reload(null, false);
                        }
                    });
                }
            } else { Swal.fire({icon: 'error', title: 'Gagal', text: res.message}); }
        });
    });

    // -------------------------------------------------------------------------
    // CRUD TRANSAKSI
    // -------------------------------------------------------------------------
    $('#table-sasaran').on('click', '.btn-add-transaksi-inline', function () {
        let kegiatanId = $(this).data('id');
        $('#form-transaksi')[0].reset();
        $('#transaksi_key').val('');
        $('#transaksi_kegiatan_id_hidden').val(kegiatanId);
        $('#modal-transaksi').modal('show');
    });

    $('#form-transaksi').submit(function (e) {
        e.preventDefault();
        let kegiatanId = $('#transaksi_kegiatan_id_hidden').val();
        let payload = $(this).serialize() + '&kegiatan_id=' + kegiatanId;
        $.post(AppConfig.initGlobal + 'store/ikk-transaksi', payload, function (res) {
            if (res.status === 'success') {
                Swal.fire({icon: 'success', text: res.message, timer: 1500, showConfirmButton: false});
                $('#modal-transaksi').modal('hide');
                $(`#table-transaksi-${kegiatanId}`).DataTable().ajax.reload(null, false);
            } else { Swal.fire({icon: 'error', title: 'Gagal', text: res.message}); }
        });
    });

    $('#table-sasaran').on('click', '.btn-edit-transaksi', function () {
        let tableTransaksi = $(this).closest('table').DataTable();
        let row = tableTransaksi.row($(this).parents('tr')).data();
        let kegiatanId = $(this).data('kegiatan');
        
        $('#transaksi_key').val(row.id);
        $('#transaksi_kegiatan_id_hidden').val(kegiatanId);
        $('#transaksi_pelaksana').val(row.pelaksana_kegiatan_harian);
        $('#transaksi_start_date').val(row.period_start_date);
        $('#transaksi_end_date').val(row.period_end_date);
        $('#transaksi_target_mingguan').val(row.target_mingguan);
        $('#transaksi_jumlah_realisasi').val(row.jumlah_realisasi);
        $('#transaksi_progres').val(row.progres_realisasi);
        $('#transaksi_bukti').val(row.bukti_pekerjaan);
        $('#modal-transaksi').modal('show');
    });

    $('#table-sasaran').on('click', '.btn-delete-transaksi', function () {
        let id = $(this).data('id');
        let kegiatanId = $(this).data('kegiatan');
        Swal.fire({
            title: 'Hapus Transaksi?', text: "Yakin ingin menghapus transaksi harian ini?",
            icon: 'warning', showCancelButton: true, confirmButtonText: 'Ya, Hapus'
        }).then((result) => {
            if (result.isConfirmed) {
                $.post(AppConfig.initGlobal + 'kill/ikk-transaksi', { key: id }, function (res) {
                    if (res.status) { Swal.fire({icon: 'success', text: res.message, timer: 1500, showConfirmButton: false}); $(`#table-transaksi-${kegiatanId}`).DataTable().ajax.reload(null, false); } 
                    else { Swal.fire({icon: 'error', title: 'Gagal', text: res.message}); }
                });
            }
        });
    });
    } catch(e) { alert('JS Error: ' + e.message); console.error(e); }
});
