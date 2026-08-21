$(document).ready(function () {
    $('#dataTable').on('processing.dt', function (e, settings, processing) {
        var tbody = $(this).find('tbody');
        if (processing) {
            tbody.html(`
            <tr>
                <td colspan="6" class="text-center">
                <div class="spinner-border text-primary" role="status">
                    <span class="visually-hidden">Loading...</span>
                </div>
                </td>
            </tr>
            `);
        }
    });

    $('#dataTable').DataTable({
        responsive: {
            details: {
                type: 'column',
                target: 'td.dtr-control' // hanya klik di kolom tertentu yang akan membuka child row
            }
        },
        processing: true,
        serverSide: true,
        pageLength: 25,
        order: [[1, 'asc']],
    // dom: 'Bfrtip',
        buttons: ['copy', 'excel', 'pdf', 'print'],
        ajax: {
            url: AppConfig.initGlobal + 'store/pull-datalist-instansi',
            type: 'POST'
        },
        columnDefs: [
            {
                className: 'dtr-control', 
                orderable: false,
                targets: 0
            }
        ],
        columns: [
            { data: null, defaultContent: '' }, 
            {
                data: 'is_status',
                render: function(data, type, row) {
                    let checked = data == 1 ? 'checked' : '';
                    let label = data == 1 ? 'Aktif' : 'Non-Aktif';
                    return `
                        <div class="form-check form-switch">
                            <input class="form-check-input btn-status" type="checkbox"
                                id="switch-${row.id}"
                                ${checked}
                                name="status_poli" data-key="${row.id}">
                            <label class="form-check-label" for="switch-${row.id}">${label}</label>
                        </div>`;
                }
            }, 
            { data: 'kodeins' },
            { data: 'nama' },           
            { data: 'updated_at' },
            {
                data: null,
                orderable: false,
                searchable: false,
                render: function(data, type, row) {
                    return `<button class="btn btn-sm btn-light-danger btn-remove" data-id="${row.id}">
                                <i class='bi bi-trash'></i>
                            </button>
                            <button class="btn btn-sm btn-light-primary btn-update" data-id="${row.id}">
                                <i class='bi bi-pencil'></i>
                            </button>`;
                }
            }
        ]
    });    

    $('#dataTable tbody').on('click', 'tr td .btn-status', function (e) {
        e.stopPropagation(); // cegah buka child row
        var $checkbox = $(this);
        var key = $checkbox.attr('data-key');
        var sts = $checkbox.prop('checked') ? 1 : 0;
        if (sts == 0) {
            Swal.fire({
                text: "Apa anda yakin akan menonaktifkan instansi ini? Saat data non-aktif maka data instansi tidak akan muncul.",
                icon: "warning",
                showCancelButton: true,
                confirmButtonColor: "#d63031",
                confirmButtonText: "Ya",
                cancelButtonText: "Tidak"
            }).then((result) => {
                if (result.isConfirmed) {
                    statusData(key, sts);
                } else {
                    $checkbox.prop('checked', true);
                }
            });
        } else {
            statusData(key, sts);
        }
    });

    $('#dataTable tbody').on('click', 'tr td .btn-remove', function (e) {
        e.stopPropagation();
        var key = $(this).attr('data-id');
        Swal.fire({
            text: "Apa anda yakin akan mengahapus data ini?",
            icon: "warning",
            showCancelButton: true,
            confirmButtonColor: "#d63031",
            confirmButtonText: "Ya",
            cancelButtonText: "Tidak"
        }).then((result) => {
            if (result.isConfirmed) {
                $.ajax({
                    type: "DELETE",
                    url: AppConfig.initGlobal + "api/ref/instansi/" + key,
                    success: function (response) {
                        if (response && response.status) {
                            swlSuccess();
                            $('#dataTable').DataTable().ajax.reload(null, false);
                        } else {
                            swlErrorHandler(response.message || 'Gagal menghapus data');
                        }
                    },
                    error: function (xhr) {
                        var res = xhr.responseJSON;
                        swlErrorHandler(res && res.message ? res.message : 'Gagal menghapus data');
                    }
                });
            }
        });
    });

    $('#dataTable tbody').on('click', 'tr td .btn-update', function (e) {
        e.stopPropagation();
        var data = $('#dataTable').DataTable().row($(this).parents('tr')).data();
        $('#instansi_id').val(data.id);
        $('#kodeins').val(data.kodeins);
        $('#nama').val(data.nama);
        $('#DataModalTitle').text('Edit Data Instansi');
        $('#DataModal').modal('show');
    });

    $('#btnSaveData').on('click', function () {
        var id = $('#instansi_id').val();
        var kodeins = $('#kodeins').val();
        var nama = $('#nama').val();

        if (!kodeins || !nama) {
            swlErrorHandler('Kode dan Nama Instansi wajib diisi');
            return;
        }

        var payload = { kodeins: kodeins, nama: nama, kanreg: 3 }; 
        
        var url = AppConfig.initGlobal + "api/ref/instansi";
        var method = "POST";
        if (id) {
            url += "/" + id;
            method = "PUT";
        }

        $.ajax({
            type: method,
            url: url,
            data: JSON.stringify(payload),
            contentType: "application/json",
            success: function (response) {
                if (response.status) {
                    $('#DataModal').modal('hide');
                    swlSuccess();
                    $('#dataTable').DataTable().ajax.reload(null, false);
                } else {
                    swlErrorHandler(response.message || 'Gagal menyimpan data');
                }
            },
            error: function (xhr) {
                var res = xhr.responseJSON;
                swlErrorHandler(res && res.message ? res.message : 'Gagal menyimpan data');
            }
        });
    });

    $('#DataModal').on('hidden.bs.modal', function () {
        $('#DataForm')[0].reset();
        $('#instansi_id').val('');
        $('#DataModalTitle').text('Tambah Data Instansi');
    });

    function statusData(key, sts) {
        swlwaitProsessing
        $.ajax({
            type: "POST",
            url: AppConfig.initGlobal + "status-data",
            data: {
                key: key,
                status: sts,
                tableinfo: 'instansi',
            },
            dataType: 'json',
            success: function (response) {
                swlSuccess();
                $('#dataTable').DataTable().ajax.reload();
            }
        });
    }

    function swlErrorHandler(msg) {
        Swal.fire({
            toast: true,
            position: 'top',
            icon: 'error',
            title: msg,
            timer: 1000,
            allowOutsideClick: false,
            allowEscapeKey: false,
            showConfirmButton: false,
        });
    }

    function swlSuccess() {
        Swal.fire({
            toast: true,
            position: 'top',
            icon: 'success',
            title: 'Data berhasil di simpan',
            timer: 1000,
            allowOutsideClick: false,
            allowEscapeKey: false,
            showConfirmButton: false,
        });
    }



});