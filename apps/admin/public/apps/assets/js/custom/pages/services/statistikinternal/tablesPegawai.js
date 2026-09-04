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
            $('#stkin-total-aktif').text(ServiceTableUI.formatNumber(s.total_aktif || 0));
            $('#stkin-total-nonaktif').text(ServiceTableUI.formatNumber(s.total_nonaktif || 0));
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
    order: [[2, 'asc']],    
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
    columnDefs: [
        { className: 'dtr-control', targets: 0, orderable: false },
        { targets: [1, 20], orderable: false, searchable: false, className: 'text-center align-middle' }
    ],
    columns: [
        { data: null, defaultContent: '', orderable: false, searchable: false },
        {
            data: 'is_status',
            orderable: false,
            searchable: false,
            className: 'text-center align-middle',
            render: function(data, type, row) {
                let checked = (data == 1 || data === '1' || data === true) ? 'checked' : '';
                return `
                    <div class="form-check form-switch d-inline-flex align-items-center justify-content-center m-0 p-0" style="min-height: auto;">
                        <input class="form-check-input btn-status m-0" type="checkbox"
                            id="switch-${row.id}"
                            ${checked}
                            data-key="${row.id}"
                            style="cursor: pointer; float: none; margin: 0 !important;">
                    </div>`;
            }
        },
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
            className: 'text-center align-middle',
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
                    { id: 'total-aktif', label: 'Pegawai Aktif', value: '0' },
                    { id: 'total-nonaktif', label: 'Pegawai Non-Aktif', value: '0' },
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

$('#dataTable tbody').on('click', '.btn-status', function (e) {
    e.stopPropagation();
    const $checkbox = $(this);
    const key = $checkbox.data('key');
    const isChecked = $checkbox.prop('checked');
    const newStatus = isChecked ? 1 : 0;
    const previousStatus = isChecked ? 0 : 1;
    const actionText = newStatus === 1 ? 'mengaktifkan' : 'menonaktifkan';

    Swal.fire({
        text: `Apakah Anda yakin ingin ${actionText} data pegawai ini?`,
        icon: "warning",
        showCancelButton: true,
        confirmButtonColor: newStatus === 1 ? "#3085d6" : "#d63031",
        cancelButtonColor: "#6c757d",
        confirmButtonText: "Ya",
        cancelButtonText: "Batal",
        allowOutsideClick: false
    }).then((result) => {
        if (result.isConfirmed) {
            $.ajax({
                type: "POST",
                url: AppConfig.initGlobal + "status-data",
                data: {
                    key: key,
                    status: newStatus,
                    tableinfo: 'pegawai'
                },
                dataType: 'json',
                success: function (response) {
                    if (response && response.status) {
                        swlSuccess('Status berhasil diperbarui');
                        table.ajax.reload(null, false);
                        if (typeof loadSummaryStatistikInternal === 'function') {
                            loadSummaryStatistikInternal();
                        }
                        if (typeof loadData === 'function') {
                            loadData();
                        }
                    } else {
                        $checkbox.prop('checked', previousStatus === 1);
                        swlErrorHandler(response?.message || 'Gagal mengubah status');
                    }
                },
                error: function (xhr) {
                    $checkbox.prop('checked', previousStatus === 1);
                    const msg = xhr.responseJSON?.message || 'Terjadi kesalahan saat mengubah status';
                    swlErrorHandler(msg);
                }
            });
        } else {
            $checkbox.prop('checked', previousStatus === 1);
        }
    });
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
    form.find('[name="nip"]').val(row.nip).prop('readonly', true);
    form.find('[name="nama"]').val(row.nama);
    form.find('[name="gender"]').val(row.gender);
    form.find('[name="tgl_lahir"]').val(row.tgl_lahir);
    form.find('[name="menikah"]').val(row.menikah);
    form.find('[name="tmt_gol"]').val(row.tmt_gol);
    form.find('[name="phone"]').val(row.phone);
    form.find('[name="email"]').val(row.email);

    $('#DataModal').modal('show');

    // ========================
    // SET SELECT2 (DYNAMIC)
    // ========================
    $('#DataModal').one('shown.bs.modal', function () {

        const mapSelect = (selectEl, value, text) => {
            if (!value || value === '0' || value === 0) return;
            selectEl.empty();
            if (value.toString().includes(',')) {
                const vals = value.toString().split(',');
                const txts = (text || '').toString().split(',');
                for (let i = 0; i < vals.length; i++) {
                    const t = txts[i] ? txts[i].trim() : vals[i].trim();
                    const option = new Option(t, vals[i].trim(), true, true);
                    selectEl.append(option);
                }
                selectEl.trigger('change');
            } else {
                const option = new Option(text || value, value, true, true);
                selectEl.append(option).trigger('change');
            }
        };

        mapSelect(form.find('[name="agama"]'), row.agama_id, row.agama);
        mapSelect(form.find('[name="pendidikan"]'), row.pendidikan_id, row.pendidikan);
        mapSelect(form.find('[name="status_pegawai"]'), row.status_pegawai_id, row.status_pegawai);
        mapSelect(form.find('[name="unit_kerja[]"]'), row.unit_kerja_id, row.unit_kerja);
        mapSelect(form.find('[name="unit_sk"]'), row.unit_sk_id, row.unit_sk);
        mapSelect(form.find('[name="jenis_jabatan"]'), row.jenis_jabatan_id, row.jenis_jabatan);
        mapSelect(form.find('[name="jabatan"]'), row.jabatan_id, row.jabatan);
        mapSelect(form.find('[name="golongan"]'), row.gol_id, row.golongan);
        mapSelect(form.find('[name="pangkat"]'), row.pangkat_id, row.pangkat);

    });


});

function setSelect2Value(form, name, id, text) {
    if (!id) return;

    const select = form.find('[name="' + name + '"]');
    const option = new Option(text, id, true, true);
    select.append(option).trigger('change');
}

$('#pegawaiTab button').on('click', function () {
    const mode = $(this).data('mode');
    if (mode) {
        tableMode = mode;
        table.ajax.reload(null, false);
        loadSummaryStatistikInternal();
    }
});
