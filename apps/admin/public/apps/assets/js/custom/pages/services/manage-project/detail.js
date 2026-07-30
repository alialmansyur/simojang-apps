$(document).ready(function() {
    var projectUid = $('#project_uid').val();

    // Original Table Settings
    const emptyLottie = (window.ServiceTableUI && ServiceTableUI.createEmptyLottie)
        ? ServiceTableUI.createEmptyLottie()
        : '<div class="text-center text-muted py-5">Tidak ada data.</div>';
    const processingState = (window.ServiceTableUI && ServiceTableUI.createProcessingState)
        ? ServiceTableUI.createProcessingState('Memuat data...')
        : '<div class="text-center text-muted py-4">Memuat data...</div>';

    // Init DataTables for Progress
    var progressTable = $('#progressTable').DataTable({
        processing: true,
        serverSide: true,
        responsive: true,
        dom: 'Bfrtip',
        lengthMenu: [
            [10, 25, 50, -1],
            ['10', '25', '50', 'All']
        ],
        buttons: [
            { extend: 'copy', className: 'btn btn-primary', text: 'Copy' },
            { extend: 'excel', className: 'btn btn-primary', text: 'Excel' },
            { extend: 'pdf', className: 'btn btn-primary', text: 'PDF' },
            { extend: 'print', className: 'btn btn-primary', text: 'Print' }
        ],
        language: {
            emptyTable: (window.ServiceTableUI ? ServiceTableUI.createEmptyState() : 'Tidak ada data'),
            zeroRecords: (window.ServiceTableUI ? ServiceTableUI.createEmptyState() : 'Tidak ada data'),
            processing: processingState
        },
        initComplete: function () {
            if (window.ServiceTableUI) {
                ServiceTableUI.setup({
                    key: 'progress',
                    table: progressTable,
                    cards: []
                });
            }
        },
        ajax: {
            url: window.location.origin + '/fetch/data-manage-project',
            type: 'POST',
            data: function (d) {
                d.project_uid = projectUid;
                d.type = 'progress';
            }
        },
        columns: [
            {
                data: null,
                defaultContent: '',
                orderable: false,
                searchable: false,
                className: 'dtr-control'
            },
            {
                data: null,
                searchable: false,
                orderable: false,
                className: 'text-center',
                render: function (data, type, row, meta) {
                    return meta.row + meta.settings._iDisplayStart + 1;
                }
            },
            { 
                data: 'log_date',
                render: function(data) {
                    if(!data) return '-';
                    var date = new Date(data);
                    return date.toLocaleDateString('id-ID', {day: '2-digit', month: 'short', year: 'numeric'});
                }
            },
            { data: 'target_percentage', className: 'text-end inline-editable', name: 'target_percentage' },
            { 
                data: 'actual_percentage', 
                className: 'text-end fw-bold text-primary inline-editable',
                name: 'actual_percentage',
                render: function(data) {
                    return data + '%';
                }
            },
            { data: 'notes', className: 'inline-editable', name: 'notes' },
            { data: 'created_at' },
            {
                data: null,
                defaultContent: '',
                orderable: false,
                searchable: false
            }
        ],
        createdRow: function(row, data, dataIndex) {
            $(row).attr('data-id', data.id);
        },
        order: [[1, 'desc']]
    });

    // Init DataTables for Budget
    var budgetTable = $('#budgetTable').DataTable({
        processing: true,
        serverSide: true,
        responsive: true,
        dom: 'Bfrtip',
        lengthMenu: [
            [10, 25, 50, -1],
            ['10', '25', '50', 'All']
        ],
        buttons: [
            { extend: 'copy', className: 'btn btn-primary', text: 'Copy' },
            { extend: 'excel', className: 'btn btn-primary', text: 'Excel' },
            { extend: 'pdf', className: 'btn btn-primary', text: 'PDF' },
            { extend: 'print', className: 'btn btn-primary', text: 'Print' }
        ],
        language: {
            emptyTable: (window.ServiceTableUI ? ServiceTableUI.createEmptyState() : 'Tidak ada data'),
            zeroRecords: (window.ServiceTableUI ? ServiceTableUI.createEmptyState() : 'Tidak ada data'),
            processing: processingState
        },
        initComplete: function () {
            if (window.ServiceTableUI) {
                ServiceTableUI.setup({
                    key: 'budget',
                    table: budgetTable,
                    cards: []
                });
            }
        },
        ajax: {
            url: window.location.origin + '/fetch/data-manage-project',
            type: 'POST',
            data: function (d) {
                d.project_uid = projectUid;
                d.type = 'budget';
            }
        },
        columns: [
            {
                data: null,
                defaultContent: '',
                orderable: false,
                searchable: false,
                className: 'dtr-control'
            },
            {
                data: null,
                searchable: false,
                orderable: false,
                className: 'text-center',
                render: function (data, type, row, meta) {
                    return meta.row + meta.settings._iDisplayStart + 1;
                }
            },
            { 
                data: 'realization_date',
                render: function(data) {
                    if(!data) return '-';
                    var date = new Date(data);
                    return date.toLocaleDateString('id-ID', {day: '2-digit', month: 'short', year: 'numeric'});
                }
            },
            { 
                data: 'amount', 
                className: 'text-end fw-bold text-success inline-editable',
                name: 'amount',
                render: function(data) {
                    return 'Rp ' + Number(data).toLocaleString('id-ID');
                }
            },
            { data: 'description', className: 'inline-editable', name: 'description' },
            { data: 'created_at' },
            {
                data: null,
                defaultContent: '',
                orderable: false,
                searchable: false
            }
        ],
        createdRow: function(row, data, dataIndex) {
            $(row).attr('data-id', data.id);
        },
        order: [[1, 'desc']]
    });

    function formatDateIndo(dateStr) {
        if (!dateStr) return '-';
        const months = ["Jan", "Feb", "Mar", "Apr", "Mei", "Jun", "Jul", "Ags", "Sep", "Okt", "Nov", "Des"];
        const d = new Date(dateStr);
        return ('0' + d.getDate()).slice(-2) + ' ' + months[d.getMonth()] + ' ' + d.getFullYear();
    }

    function loadProjectOverview() {
        $.ajax({
            url: window.location.origin + '/fetch/project-overview',
            type: 'POST',
            data: { project_uid: projectUid },
            dataType: 'json',
            success: function(res) {
                if(res.status === 'success') {
                    var p = res.data;
                    $('#lblProjectName').text(p.name);
                    $('#lblProjectCategory').text(p.category || 'Tanpa Kategori');
                    $('#lblProjectDescription').text(p.description || '-');
                    $('#lblStartDate').text(formatDateIndo(p.start_date));
                    $('#lblTargetEndDate').text(formatDateIndo(p.target_end_date));
                    $('#lblProjectStatus').text(p.status);
                    
                    var budget = parseFloat(p.budget_amount) || 0;
                    var realized = parseFloat(p.realized_budget_amount) || 0;
                    var progress = parseFloat(p.progress_percentage) || 0;

                    $('#lblBudgetAmount').text('Rp ' + formatNumber(budget.toString()));
                    $('#realizedBudgetLabel').text('Rp ' + formatNumber(realized.toString()));
                    
                    var formattedProgress = progress.toLocaleString('id-ID', { minimumFractionDigits: 2, maximumFractionDigits: 2 }) + '%';
                    $('#progressPercentageLabel').text(formattedProgress);
                    
                    setTimeout(function() {
                        $('#projectSkeleton').addClass('d-none');
                        $('#projectContent').removeClass('d-none').hide().fadeIn(400, function() {
                            $('#progressFill').css('width', progress + '%');
                        });
                    }, 500); 
                }
            }
        });
    }

    loadProjectOverview();

    // Format mata uang
    $("input[data-type='currency']").on({
        keyup: function() {
            formatCurrency($(this));
        },
        blur: function() { 
            formatCurrency($(this), "blur");
        }
    });

    function formatNumber(n) {
        return n.replace(/\D/g, "").replace(/\B(?=(\d{3})+(?!\d))/g, ",");
    }

    function formatCurrency(input, blur) {
        var input_val = input.val();
        if (input_val === "") { return; }
        var original_len = input_val.length;
        var caret_pos = input.prop("selectionStart");
        if (input_val.indexOf(".") >= 0) {
            var decimal_pos = input_val.indexOf(".");
            var left_side = input_val.substring(0, decimal_pos);
            var right_side = input_val.substring(decimal_pos);
            left_side = formatNumber(left_side);
            right_side = formatNumber(right_side);
            if (blur === "blur") { right_side += "00"; }
            right_side = right_side.substring(0, 2);
            input_val = left_side + "." + right_side;
        } else {
            input_val = formatNumber(input_val);
            if (blur === "blur") { input_val += ".00"; }
        }
        input.val(input_val);
        var updated_len = input_val.length;
        caret_pos = updated_len - original_len + caret_pos;
        input[0].setSelectionRange(caret_pos, caret_pos);
    }

    // Use global showLoading

    // Submit Progress
    $('#formProgress').on('submit', function(e) {
        e.preventDefault();
        let btn = $('#btnSaveProgress');
        btn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Menyimpan...');
        
        $.ajax({
            url: window.location.origin + '/store/save-project-progress',
            type: 'POST',
            data: $(this).serialize(),
            dataType: 'json',
            success: function(response) {
                if(response.status == 'success'){
                    Swal.fire({
                        icon: 'success',
                        title: 'Berhasil',
                        text: response.message,
                        timer: 1500,
                        showConfirmButton: false
                    }).then(() => {
                        $('#ProgressModal').modal('hide');
                        $('#formProgress')[0].reset();
                        btn.prop('disabled', false).html('Simpan Progres');
                        progressTable.ajax.reload();
                        showLoading();
                        window.location.reload();
                    });
                } else {
                    Swal.fire('Gagal!', response.message, 'error');
                    btn.prop('disabled', false).html('Simpan Progres');
                }
            },
            error: function() {
                Swal.fire('Oops!', 'Terjadi kesalahan sistem.', 'error');
                btn.prop('disabled', false).html('Simpan Progres');
            }
        });
    });

    // Submit Budget
    $('#formBudget').on('submit', function(e) {
        e.preventDefault();
        let btn = $('#btnSaveBudget');
        btn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Menyimpan...');
        
        $.ajax({
            url: window.location.origin + '/store/save-project-budget',
            type: 'POST',
            data: $(this).serialize(),
            dataType: 'json',
            success: function(response) {
                if(response.status == 'success'){
                    Swal.fire({
                        icon: 'success',
                        title: 'Berhasil',
                        text: response.message,
                        timer: 1500,
                        showConfirmButton: false
                    }).then(() => {
                        $('#BudgetModal').modal('hide');
                        $('#formBudget')[0].reset();
                        btn.prop('disabled', false).html('Simpan Realisasi');
                        budgetTable.ajax.reload();
                        showLoading();
                        window.location.reload();
                    });
                } else {
                    Swal.fire('Gagal!', response.message, 'error');
                    btn.prop('disabled', false).html('Simpan Realisasi');
                }
            },
            error: function() {
                Swal.fire('Oops!', 'Terjadi kesalahan sistem.', 'error');
                btn.prop('disabled', false).html('Simpan Realisasi');
            }
        });
    });

    // Submit Edit Project
    $('#formEditProject').on('submit', function(e) {
        e.preventDefault();
        let btn = $('#btnUpdateProject');
        btn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Menyimpan...');
        
        $.ajax({
            url: window.location.origin + '/store/update-project',
            type: 'POST',
            data: $(this).serialize(),
            dataType: 'json',
            success: function(response) {
                if(response.status == 'success'){
                    Swal.fire({
                        icon: 'success',
                        title: 'Berhasil',
                        text: response.message,
                        timer: 1500,
                        showConfirmButton: false
                    }).then(() => {
                        $('#EditProjectModal').modal('hide');
                        showLoading();
                        window.location.reload();
                    });
                } else {
                    Swal.fire('Gagal!', response.message, 'error');
                    btn.prop('disabled', false).html('Simpan Perubahan');
                }
            },
            error: function() {
                Swal.fire('Oops!', 'Terjadi kesalahan sistem.', 'error');
                btn.prop('disabled', false).html('Simpan Perubahan');
            }
        });
    });

    // Edit Modals Logic
    $('#progressTable').on('click', 'tbody td.inline-editable', function() {
        var tr = $(this).closest('tr');
        var rowData = progressTable.row(tr).data();
        if (!rowData) return;
        
        var logDate = rowData.log_date ? rowData.log_date.substring(0, 10) : '';
        
        $('#progress_id').val(rowData.id);
        $('#formProgress [name="log_date"]').val(logDate);
        $('#formProgress [name="target_percentage"]').val(rowData.target_percentage);
        $('#formProgress [name="actual_percentage"]').val(rowData.actual_percentage);
        $('#formProgress [name="notes"]').val(rowData.notes);
        
        $('#ProgressModalLabel').text('Edit Progres Pekerjaan');
        $('#btnSaveProgress').text('Update Progres');
        $('#ProgressModal').modal('show');
    });

    $('#budgetTable').on('click', 'tbody td.inline-editable', function() {
        var tr = $(this).closest('tr');
        var rowData = budgetTable.row(tr).data();
        if (!rowData) return;
        
        var realDate = rowData.realization_date ? rowData.realization_date.substring(0, 10) : '';
        
        $('#budget_id').val(rowData.id);
        $('#formBudget [name="realization_date"]').val(realDate);
        $('#formBudget [name="amount"]').val(formatNumber(rowData.amount.toString()));
        $('#formBudget [name="description"]').val(rowData.description);
        
        $('#BudgetModalLabel').text('Edit Realisasi Anggaran');
        $('#btnSaveBudget').text('Update Realisasi');
        $('#BudgetModal').modal('show');
    });

    // Reset modals on 'Tambah' click
    $('button[data-bs-target="#ProgressModal"]').on('click', function() {
        $('#formProgress')[0].reset();
        $('#progress_id').val('');
        $('#ProgressModalLabel').text('Update Progres Pekerjaan');
        $('#btnSaveProgress').text('Simpan Progres');
    });

    $('button[data-bs-target="#BudgetModal"]').on('click', function() {
        $('#formBudget')[0].reset();
        $('#budget_id').val('');
        $('#BudgetModalLabel').text('Tambah Realisasi Anggaran');
        $('#btnSaveBudget').text('Simpan Realisasi');
    });

});
