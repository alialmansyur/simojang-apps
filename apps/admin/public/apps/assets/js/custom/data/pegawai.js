$(document).ready(function () {
    $('#dataTable').on('processing.dt', function (e, settings, processing) {
        var tbody = $(this).find('tbody');
        if (processing) {
            tbody.html(`
            <tr>
                <td colspan="13" class="text-center">
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
            url: AppConfig.initGlobal + 'store/pull-datalist-pegawai',
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
                        <div class="form-check form-switch d-flex align-items-center gap-2">
                            <input class="form-check-input btn-status m-0" type="checkbox"
                                id="switch-${row.id}"
                                ${checked}
                                name="status_poli" data-key="${row.id}" style="cursor: pointer;">
                            <label class="form-check-label mb-0" for="switch-${row.id}" style="cursor: pointer;">${label}</label>
                        </div>`;
                }
            }, 
            { data: 'nip' },
            { data: 'nama_formatted' },            
            { data: 'pangkat' },
            { data: 'gol' },
            { data: 'pendidikan' },
            { data: 'tgl_lahir' },
            { data: 'gender' },
            { data: 'menikah' },
            { data: 'phone' },
            { data: 'email' },
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

    // $('#dataTable tbody').on('click', 'tr td .btn-remove', function () {
    //     var key = $(this).attr('data-id');
    //     Swal.fire({
    //         text: "Apa anda yakin akan mengahapus data ini ?",
    //         icon: "warning",
    //         showCancelButton: true,
    //         confirmButtonColor: "#d63031",
    //         confirmButtonText: "Ya",
    //         cancelButtonText: "Tidak"
    //     }).then((result) => {
    //         if (result.isConfirmed) {
    //             $.ajax({
    //                 type: "POST",
    //                 url:  AppConfig.initGlobal + "store/remove-data-alihmedia",
    //                 data: {key: key},
    //                 success: function (response) {
    //                     if (response) {
    //                         $('#dataTable').DataTable().ajax.reload();
    //                         loadData();
    //                     }
    //                 }
    //             });
    //         }
    //     });
    // });

    function statusData(key, sts) {
        $.ajax({
            type: "POST",
            url: AppConfig.initGlobal + "status-data",
            data: {
                key: key,
                status: sts,
                tableinfo: 'member',
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