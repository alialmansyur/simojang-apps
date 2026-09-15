$(document).ready(function() {
    var projectUid = $('#project_uid').val();

    // Original Table Settings
    const emptyLottie = (window.ServiceTableUI && ServiceTableUI.createEmptyLottie)
        ? ServiceTableUI.createEmptyLottie()
        : '<div class="text-center text-muted py-5">Tidak ada data.</div>';
    const processingState = (window.ServiceTableUI && ServiceTableUI.createProcessingState)
        ? ServiceTableUI.createProcessingState('Memuat data...')
        : '<div class="text-center text-muted py-4">Memuat data...</div>';

    function formatDateIndo(dateStr) {
        if (!dateStr) return '-';
        const months = ["Jan", "Feb", "Mar", "Apr", "Mei", "Jun", "Jul", "Ags", "Sep", "Okt", "Nov", "Des"];
        const d = new Date(dateStr);
        if (isNaN(d.getTime())) return dateStr;
        return ('0' + d.getDate()).slice(-2) + ' ' + months[d.getMonth()] + ' ' + d.getFullYear();
    }

    // -------------------------------------------------------------
    // Format Child Rows (Expand / Append (+))
    // -------------------------------------------------------------
    function formatProgressChild(d) {
        if (!d) return '';
        var periodStart = d.start_date ? formatDateIndo(d.start_date) : '-';
        var periodEnd = d.end_date ? formatDateIndo(d.end_date) : '-';
        var fullPeriod = (d.start_date || d.end_date) ? (periodStart + ' s.d ' + periodEnd) : 'Tidak diset';
        var target = d.target_percentage ? (parseFloat(d.target_percentage).toLocaleString('id-ID', { minimumFractionDigits: 2, maximumFractionDigits: 2 }) + '%') : '-';
        var actual = d.actual_percentage ? (parseFloat(d.actual_percentage).toLocaleString('id-ID', { minimumFractionDigits: 2, maximumFractionDigits: 2 }) + '%') : '0%';
        var notesSafe = d.notes ? $('<div>').text(d.notes).html() : '<span class="text-muted fst-italic">Tidak ada catatan kendala</span>';
        var createdBy = d.created_by ? $('<div>').text(d.created_by).html() : '-';
        var createdAt = d.created_at || '-';
        
        var photoHtml = '';
        if (d.file_name && d.file_path) {
            var imgUrl = AppConfig.initGlobal + d.file_path + '/' + d.file_name;
            photoHtml = `
                <div class="col-md-4 mb-2 mb-md-0">
                    <div class="child-detail-label">Dokumentasi / Foto Kegiatan</div>
                    <div class="position-relative d-inline-block mt-1">
                        <img src="${imgUrl}" alt="Dokumentasi" class="img-fluid rounded flat-border view-progress-photo-btn" 
                            style="max-height: 140px; cursor: pointer; object-fit: cover; border: 1px solid #cbd5e1;"
                            data-img="${imgUrl}" 
                            data-title="Dokumentasi Progres Proyek"
                            data-date="${formatDateIndo(d.log_date)}"
                            data-actual="${d.actual_percentage}%"
                            data-period="${fullPeriod}"
                            data-notes="${d.notes ? $('<div>').text(d.notes).html() : '-'}">
                        <div class="mt-2">
                            <button type="button" class="btn btn-sm btn-outline-primary py-1 px-2 view-progress-photo-btn" 
                                style="font-size: 0.8rem;"
                                data-img="${imgUrl}" 
                                data-title="Dokumentasi Progres Proyek"
                                data-date="${formatDateIndo(d.log_date)}"
                                data-actual="${d.actual_percentage}%"
                                data-period="${fullPeriod}"
                                data-notes="${d.notes ? $('<div>').text(d.notes).html() : '-'}">
                                Perbesar Foto
                            </button>
                        </div>
                    </div>
                </div>
            `;
        } else {
            photoHtml = `
                <div class="col-md-4 mb-2 mb-md-0">
                    <div class="child-detail-label">Dokumentasi / Foto Kegiatan</div>
                    <div class="p-3 text-center bg-white rounded border text-muted mt-1" style="font-size: 0.85rem; border-color: #e2e8f0 !important;">
                        Belum ada dokumentasi foto
                    </div>
                </div>
            `;
        }

        return `
            <div class="child-detail-card my-1 text-start">
                <div class="row g-3">
                    ${photoHtml}
                    <div class="col-md-8">
                        <div class="row g-2 mb-2">
                            <div class="col-sm-6">
                                <div class="child-detail-label">Tanggal Log Update</div>
                                <div class="child-detail-value">${formatDateIndo(d.log_date)}</div>
                            </div>
                            <div class="col-sm-6">
                                <div class="child-detail-label">Rentang Periode Pekerjaan</div>
                                <div class="child-detail-value text-primary">${fullPeriod}</div>
                            </div>
                            <div class="col-sm-6 mt-2">
                                <div class="child-detail-label">Target Progres</div>
                                <div class="child-detail-value">${target}</div>
                            </div>
                            <div class="col-sm-6 mt-2">
                                <div class="child-detail-label">Realisasi Aktual</div>
                                <div class="child-detail-value text-success fw-bold">${actual}</div>
                            </div>
                        </div>
                        <div class="mb-2">
                            <div class="child-detail-label">Catatan Kendala / Rincian Kegiatan</div>
                            <div class="p-2 bg-white rounded border text-dark text-wrap-normal" style="font-size: 0.875rem; line-height: 1.5; border-color: #e2e8f0 !important; color: #1e293b !important;">
                                ${notesSafe}
                            </div>
                        </div>
                        <div class="d-flex flex-wrap justify-content-between align-items-center pt-2 border-top text-muted" style="font-size: 0.8rem; border-color: #e2e8f0 !important;">
                            <div>
                                <span>Diinput oleh: <strong class="text-dark">${createdBy}</strong></span>
                                <span class="ms-3">Waktu Entri: <span class="text-dark">${createdAt}</span></span>
                            </div>
                            <div class="d-flex gap-1 mt-2 mt-sm-0">
                                <button class="btn btn-sm btn-primary py-1 px-2 btn-update" data-id="${d.id}" style="font-size: 0.8rem;"><i class="bi bi-pencil me-1"></i> Edit</button>
                                <button class="btn btn-sm btn-danger py-1 px-2 btn-remove" data-id="${d.id}" style="font-size: 0.8rem;"><i class="bi bi-trash me-1"></i> Hapus</button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        `;
    }

    function formatBudgetChild(d) {
        if (!d) return '';
        var realDate = d.realization_date ? formatDateIndo(d.realization_date) : '-';
        var amount = d.amount ? ('Rp ' + Number(d.amount).toLocaleString('id-ID')) : 'Rp 0';
        var descSafe = d.description ? $('<div>').text(d.description).html() : '<span class="text-muted fst-italic">Tidak ada uraian keterangan</span>';
        var createdBy = d.created_by ? $('<div>').text(d.created_by).html() : '-';
        var createdAt = d.created_at || '-';

        return `
            <div class="child-detail-card my-1 text-start">
                <div class="row g-2 mb-2">
                    <div class="col-sm-4">
                        <div class="child-detail-label">Tanggal Realisasi</div>
                        <div class="child-detail-value">${realDate}</div>
                    </div>
                    <div class="col-sm-4">
                        <div class="child-detail-label">Jumlah Pengeluaran</div>
                        <div class="child-detail-value text-success fw-bold">${amount}</div>
                    </div>
                    <div class="col-sm-4">
                        <div class="child-detail-label">Diinput Oleh</div>
                        <div class="child-detail-value text-dark">${createdBy}</div>
                    </div>
                </div>
                <div class="mb-2">
                    <div class="child-detail-label">Uraian / Keterangan Lengkap</div>
                    <div class="p-2 bg-white rounded border text-dark text-wrap-normal" style="font-size: 0.875rem; line-height: 1.5; border-color: #e2e8f0 !important; color: #1e293b !important;">
                        ${descSafe}
                    </div>
                </div>
                <div class="d-flex flex-wrap justify-content-between align-items-center pt-2 border-top text-muted" style="font-size: 0.8rem; border-color: #e2e8f0 !important;">
                    <div>
                        <span>Waktu Entri: <span class="text-dark">${createdAt}</span></span>
                    </div>
                    <div class="d-flex gap-1 mt-2 mt-sm-0">
                        <button class="btn btn-sm btn-primary py-1 px-2 btn-update" data-id="${d.id}" style="font-size: 0.8rem;"><i class="bi bi-pencil me-1"></i> Edit</button>
                        <button class="btn btn-sm btn-danger py-1 px-2 btn-remove" data-id="${d.id}" style="font-size: 0.8rem;"><i class="bi bi-trash me-1"></i> Hapus</button>
                    </div>
                </div>
            </div>
        `;
    }

    // -------------------------------------------------------------
    // Init DataTables for Progress
    // -------------------------------------------------------------
    var progressTable = $('#progressTable').DataTable({
        processing: true,
        serverSide: true,
        responsive: false,
        autoWidth: false,
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
            url: AppConfig.initGlobal + 'fetch/data-manage-project',
            type: 'POST',
            data: function (d) {
                d.project_uid = projectUid;
                d.type = 'progress';
            }
        },
        columns: [
            {
                data: null,
                orderable: false,
                searchable: false,
                className: 'text-center align-middle',
                width: '4%',
                render: function (data, type, row) {
                    return '<button type="button" class="btn-dt-expand btn-dt-expand-progress" title="Lihat rincian">+</button>';
                }
            },
            {
                data: null,
                searchable: false,
                orderable: false,
                className: 'text-center align-middle',
                width: '5%',
                render: function (data, type, row, meta) {
                    return '<span style="color: #111827 !important; font-weight: 600;">' + (meta.row + meta.settings._iDisplayStart + 1) + '</span>';
                }
            },
            { 
                data: 'log_date',
                name: 'log_date',
                className: 'align-middle text-nowrap',
                width: '12%',
                render: function(data) {
                    if(!data) return '-';
                    return '<span class="fw-bold" style="color: #111827 !important;">' + formatDateIndo(data) + '</span>';
                }
            },
            { 
                data: null,
                name: 'start_date',
                orderable: false,
                className: 'align-middle text-nowrap',
                width: '18%',
                render: function(data, type, row) {
                    if (!row.start_date && !row.end_date) return '<span style="color: #64748b !important; font-size: 0.85rem;">-</span>';
                    var start = row.start_date ? formatDateIndo(row.start_date) : '-';
                    var end = row.end_date ? formatDateIndo(row.end_date) : '-';
                    return '<span class="fw-bold" style="color: #111827 !important; font-size: 0.88rem;">' + start + ' - ' + end + '</span>';
                }
            },
            { 
                data: 'target_percentage', 
                className: 'text-end align-middle', 
                name: 'target_percentage',
                width: '9%',
                render: function(data) {
                    return data ? ('<span class="fw-bold" style="color: #111827 !important;">' + parseFloat(data).toLocaleString('id-ID', { minimumFractionDigits: 2, maximumFractionDigits: 2 }) + '%</span>') : '-';
                }
            },
            { 
                data: 'actual_percentage', 
                className: 'text-end fw-bold text-primary align-middle',
                name: 'actual_percentage',
                width: '10%',
                render: function(data) {
                    return data ? (parseFloat(data).toLocaleString('id-ID', { minimumFractionDigits: 2, maximumFractionDigits: 2 }) + '%') : '0%';
                }
            },
            {
                data: 'file_name',
                orderable: false,
                searchable: false,
                className: 'text-center align-middle',
                width: '9%',
                render: function(data, type, row) {
                    if (data && row.file_path) {
                        var imgUrl = AppConfig.initGlobal + row.file_path + '/' + data;
                        var periodText = (row.start_date ? formatDateIndo(row.start_date) : '-') + ' s.d ' + (row.end_date ? formatDateIndo(row.end_date) : '-');
                        var notesSafe = $('<div>').text(row.notes || '').html();
                        return `
                            <img src="${imgUrl}" alt="Dokumentasi" class="img-doc-thumb flat-border view-progress-photo-btn" 
                                data-img="${imgUrl}" 
                                data-title="Dokumentasi Progres Proyek"
                                data-date="${formatDateIndo(row.log_date)}"
                                data-actual="${row.actual_percentage}%"
                                data-period="${periodText}"
                                data-notes="${notesSafe}"
                                title="Klik untuk memperbesar foto">
                        `;
                    }
                    return '<span style="color: #64748b !important; font-size: 0.85rem;">-</span>';
                }
            },
            { 
                data: 'notes', 
                name: 'notes',
                className: 'align-middle text-wrap-normal',
                width: '23%',
                render: function(data) {
                    if (!data) return '<span style="color: #64748b !important; font-size: 0.85rem;">-</span>';
                    var safe = $('<div>').text(data).html();
                    return `<div class="text-wrap-normal" style="font-size: 0.88rem; line-height: 1.4; color: #111827 !important; font-weight: 500;">${safe}</div>`;
                }
            },
            {
                data: null,
                orderable: false,
                searchable: false,
                className: 'text-center align-middle text-nowrap',
                width: '10%',
                render: function (_, __, row) {
                    return `
                        <div class="d-flex justify-content-center gap-1">
                            <button class="btn btn-sm btn-primary btn-update" data-id="${row.id}" title="Edit"><i class="bi bi-pencil"></i></button>
                            <button class="btn btn-sm btn-danger btn-remove" data-id="${row.id}" title="Hapus"><i class="bi bi-trash"></i></button>
                        </div>
                    `;
                }
            }
        ],
        createdRow: function(row, data, dataIndex) {
            $(row).attr('data-id', data.id);
        },
        order: [[2, 'desc']]
    });

    // -------------------------------------------------------------
    // Init DataTables for Budget
    // -------------------------------------------------------------
    var budgetTable = $('#budgetTable').DataTable({
        processing: true,
        serverSide: true,
        responsive: false,
        autoWidth: false,
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
            url: AppConfig.initGlobal + 'fetch/data-manage-project',
            type: 'POST',
            data: function (d) {
                d.project_uid = projectUid;
                d.type = 'budget';
            }
        },
        columns: [
            {
                data: null,
                orderable: false,
                searchable: false,
                className: 'text-center align-middle',
                width: '4%',
                render: function (data, type, row) {
                    return '<button type="button" class="btn-dt-expand btn-dt-expand-budget" title="Lihat rincian">+</button>';
                }
            },
            {
                data: null,
                searchable: false,
                orderable: false,
                className: 'text-center align-middle',
                width: '5%',
                render: function (data, type, row, meta) {
                    return '<span style="color: #111827 !important; font-weight: 600;">' + (meta.row + meta.settings._iDisplayStart + 1) + '</span>';
                }
            },
            { 
                data: 'realization_date',
                name: 'realization_date',
                className: 'align-middle text-nowrap',
                width: '16%',
                render: function(data) {
                    if(!data) return '-';
                    return '<span class="fw-bold" style="color: #111827 !important;">' + formatDateIndo(data) + '</span>';
                }
            },
            { 
                data: 'amount', 
                className: 'text-end fw-bold text-success align-middle text-nowrap',
                name: 'amount',
                width: '18%',
                render: function(data) {
                    return 'Rp ' + Number(data).toLocaleString('id-ID');
                }
            },
            { 
                data: 'description', 
                name: 'description',
                className: 'align-middle text-wrap-normal',
                width: '47%',
                render: function(data) {
                    if (!data) return '<span style="color: #64748b !important; font-size: 0.85rem;">-</span>';
                    var safe = $('<div>').text(data).html();
                    return `<div class="text-wrap-normal" style="font-size: 0.88rem; line-height: 1.5; color: #111827 !important; font-weight: 500;">${safe}</div>`;
                }
            },
            {
                data: null,
                orderable: false,
                searchable: false,
                className: 'text-center align-middle text-nowrap',
                width: '10%',
                render: function (_, __, row) {
                    return `
                        <div class="d-flex justify-content-center gap-1">
                            <button class="btn btn-sm btn-primary btn-update" data-id="${row.id}" title="Edit"><i class="bi bi-pencil"></i></button>
                            <button class="btn btn-sm btn-danger btn-remove" data-id="${row.id}" title="Hapus"><i class="bi bi-trash"></i></button>
                        </div>
                    `;
                }
            }
        ],
        createdRow: function(row, data, dataIndex) {
            $(row).attr('data-id', data.id);
        },
        order: [[2, 'desc']]
    });

    // -------------------------------------------------------------
    // Expand / Collapse Row Event Handlers (+)
    // -------------------------------------------------------------
    $('#progressTable tbody').on('click', '.btn-dt-expand-progress', function (e) {
        e.stopPropagation();
        var tr = $(this).closest('tr');
        var row = progressTable.row(tr);
        var btn = $(this);

        if (row.child.isShown()) {
            row.child.hide();
            tr.removeClass('shown');
            btn.text('+').removeClass('bg-secondary text-white border-secondary');
        } else {
            row.child(formatProgressChild(row.data())).show();
            tr.addClass('shown');
            btn.text('-').addClass('bg-secondary text-white border-secondary');
        }
    });

    $('#budgetTable tbody').on('click', '.btn-dt-expand-budget', function (e) {
        e.stopPropagation();
        var tr = $(this).closest('tr');
        var row = budgetTable.row(tr);
        var btn = $(this);

        if (row.child.isShown()) {
            row.child.hide();
            tr.removeClass('shown');
            btn.text('+').removeClass('bg-secondary text-white border-secondary');
        } else {
            row.child(formatBudgetChild(row.data())).show();
            tr.addClass('shown');
            btn.text('-').addClass('bg-secondary text-white border-secondary');
        }
    });

    // -------------------------------------------------------------
    // Project Overview
    // -------------------------------------------------------------
    function loadProjectOverview() {
        $.ajax({
            url: AppConfig.initGlobal + 'fetch/project-overview',
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

    // -------------------------------------------------------------
    // Dropzone & Upload Foto (Form Progress)
    // -------------------------------------------------------------
    function handleProgressFile(file) {
        if (!file) return;
        if (file.size > 2 * 1024 * 1024) {
            Swal.fire('Perhatian', 'Ukuran file maksimal 2MB.', 'warning');
            $('#inputProgressFoto').val('');
            return;
        }

        const validTypes = ['image/jpeg', 'image/jpg', 'image/png', 'image/webp'];
        if (!validTypes.includes(file.type)) {
            Swal.fire('Perhatian', 'Format file harus berupa JPG, PNG, atau WebP.', 'warning');
            $('#inputProgressFoto').val('');
            return;
        }

        const reader = new FileReader();
        reader.onload = function(e) {
            $('#progressUploadPlaceholder').addClass('d-none');
            $('#progressUploadPreview').removeClass('d-none');
            $('#progressUploadPreview img').attr('src', e.target.result);
        };
        reader.readAsDataURL(file);
    }

    $('#inputProgressFoto').on('change', function (e) {
        const file = e.target.files[0];
        handleProgressFile(file);
    });

    $('#btnRemoveProgressPhoto').on('click', function (e) {
        e.stopPropagation();
        $('#inputProgressFoto').val('');
        $('#progressUploadPreview').addClass('d-none');
        $('#progressUploadPlaceholder').removeClass('d-none');
        $('#progressUploadPreview img').attr('src', '');
    });

    const progressUploadArea = $('#progressUploadArea');
    progressUploadArea.on('dragover', function(e) {
        e.preventDefault();
        e.stopPropagation();
        progressUploadArea.addClass('drag-over border-primary');
    });

    progressUploadArea.on('dragleave', function(e) {
        e.preventDefault();
        e.stopPropagation();
        progressUploadArea.removeClass('drag-over border-primary');
    });

    progressUploadArea.on('drop', function(e) {
        e.preventDefault();
        e.stopPropagation();
        progressUploadArea.removeClass('drag-over border-primary');

        const files = e.originalEvent.dataTransfer.files;
        if (files && files.length > 0) {
            const file = files[0];
            const dataTransfer = new DataTransfer();
            dataTransfer.items.add(file);
            $('#inputProgressFoto')[0].files = dataTransfer.files;
            handleProgressFile(file);
        }
    });

    function resetProgressModal() {
        $('#formProgress')[0].reset();
        $('#progress_id').val('');
        $('#progress_start_date').val('');
        $('#progress_end_date').val('');
        $('#btnRemoveProgressPhoto').click();
        $('#ProgressModalLabel').text('Update Progres Pekerjaan');
        $('#btnSaveProgress').text('Simpan Progres');
    }

    $('#ProgressModal').on('hidden.bs.modal', function () {
        resetProgressModal();
    });

    // -------------------------------------------------------------
    // Submit Progress (Multipart / FormData)
    // -------------------------------------------------------------
    $('#formProgress').on('submit', function(e) {
        e.preventDefault();
        let btn = $('#btnSaveProgress');
        let originalText = btn.text();
        btn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Menyimpan...');
        
        let formData = new FormData(this);

        $.ajax({
            url: AppConfig.initGlobal + 'store/save-project-progress',
            type: 'POST',
            data: formData,
            processData: false,
            contentType: false,
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
                        resetProgressModal();
                        btn.prop('disabled', false).text(originalText);
                        progressTable.ajax.reload(null, false);
                        loadProjectOverview();
                    });
                } else {
                    Swal.fire('Gagal!', response.message, 'error');
                    btn.prop('disabled', false).text(originalText);
                }
            },
            error: function() {
                Swal.fire('Oops!', 'Terjadi kesalahan sistem.', 'error');
                btn.prop('disabled', false).text(originalText);
            }
        });
    });

    // -------------------------------------------------------------
    // Submit Budget
    // -------------------------------------------------------------
    $('#formBudget').on('submit', function(e) {
        e.preventDefault();
        let btn = $('#btnSaveBudget');
        btn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Menyimpan...');
        
        $.ajax({
            url: AppConfig.initGlobal + 'store/save-project-budget',
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
                        budgetTable.ajax.reload(null, false);
                        loadProjectOverview();
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

    // -------------------------------------------------------------
    // Submit Edit Project
    // -------------------------------------------------------------
    $('#formEditProject').on('submit', function(e) {
        e.preventDefault();
        let btn = $('#btnUpdateProject');
        btn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Menyimpan...');
        
        $.ajax({
            url: AppConfig.initGlobal + 'store/update-project',
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
                        btn.prop('disabled', false).html('Simpan Perubahan');
                        loadProjectOverview();
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

    // Helper to find row data whether clicked on parent or child row
    function findRowData(btn, table) {
        var tr = btn.closest('tr');
        if (tr.hasClass('child')) {
            tr = tr.prev();
        }
        var data = table.row(tr).data();
        if (!data) {
            var id = btn.data('id');
            table.rows().every(function() {
                var d = this.data();
                if (d && d.id == id) {
                    data = d;
                }
            });
        }
        return data;
    }

    // -------------------------------------------------------------
    // Edit & Remove Progress Handlers
    // -------------------------------------------------------------
    $(document).on('click', '#progressTable .btn-update', function() {
        var rowData = findRowData($(this), progressTable);
        if (!rowData) return;
        
        var logDate = rowData.log_date ? rowData.log_date.substring(0, 10) : '';
        var startDate = rowData.start_date ? rowData.start_date.substring(0, 10) : '';
        var endDate = rowData.end_date ? rowData.end_date.substring(0, 10) : '';

        $('#progress_id').val(rowData.id);
        $('#formProgress [name="log_date"]').val(logDate);
        $('#progress_start_date').val(startDate);
        $('#progress_end_date').val(endDate);
        $('#formProgress [name="target_percentage"]').val(rowData.target_percentage);
        $('#formProgress [name="actual_percentage"]').val(rowData.actual_percentage);
        $('#formProgress [name="notes"]').val(rowData.notes);
        
        // Reset file input
        $('#inputProgressFoto').val('');

        // Photo preview handling if existing
        if (rowData.file_name && rowData.file_path) {
            var imgUrl = AppConfig.initGlobal + rowData.file_path + '/' + rowData.file_name;
            $('#progressUploadPlaceholder').addClass('d-none');
            $('#progressUploadPreview').removeClass('d-none');
            $('#progressUploadPreview img').attr('src', imgUrl);
        } else {
            $('#progressUploadPreview').addClass('d-none');
            $('#progressUploadPlaceholder').removeClass('d-none');
            $('#progressUploadPreview img').attr('src', '');
        }

        $('#ProgressModalLabel').text('Edit Progres Pekerjaan');
        $('#btnSaveProgress').text('Update Progres');
        $('#ProgressModal').modal('show');
    });

    $(document).on('click', '#progressTable .btn-remove', function() {
        const id = $(this).data('id');
        Swal.fire({
            text: 'Apa anda yakin akan menghapus data progres ini?',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d63031',
            confirmButtonText: 'Ya, Hapus',
            cancelButtonText: 'Batal'
        }).then((result) => {
            if (!result.isConfirmed) return;
            $.ajax({
                type: 'POST',
                url: AppConfig.initGlobal + 'kill/project-progress',
                data: { id: id, project_uid: projectUid },
                dataType: 'json',
                success: function (response) {
                    if (response.status) {
                        Swal.fire('Terhapus!', response.message, 'success');
                        progressTable.ajax.reload(null, false);
                        loadProjectOverview();
                    } else {
                        Swal.fire('Gagal!', response.message, 'error');
                    }
                }
            });
        });
    });

    // -------------------------------------------------------------
    // Edit & Remove Budget Handlers
    // -------------------------------------------------------------
    $(document).on('click', '#budgetTable .btn-update', function() {
        var rowData = findRowData($(this), budgetTable);
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

    $(document).on('click', '#budgetTable .btn-remove', function() {
        const id = $(this).data('id');
        Swal.fire({
            text: 'Apa anda yakin akan menghapus data realisasi anggaran ini?',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d63031',
            confirmButtonText: 'Ya, Hapus',
            cancelButtonText: 'Batal'
        }).then((result) => {
            if (!result.isConfirmed) return;
            $.ajax({
                type: 'POST',
                url: AppConfig.initGlobal + 'kill/project-budget',
                data: { id: id, project_uid: projectUid },
                dataType: 'json',
                success: function (response) {
                    if (response.status) {
                        Swal.fire('Terhapus!', response.message, 'success');
                        budgetTable.ajax.reload(null, false);
                        loadProjectOverview();
                    } else {
                        Swal.fire('Gagal!', response.message, 'error');
                    }
                }
            });
        });
    });

    // -------------------------------------------------------------
    // View Progress Photo Lightbox
    // -------------------------------------------------------------
    $(document).on('click', '.view-progress-photo-btn', function(e) {
        e.stopPropagation();
        var img = $(this).data('img');
        var title = $(this).data('title');
        var actual = $(this).data('actual');
        var period = $(this).data('period');
        var date = $(this).data('date');
        var notes = $(this).data('notes');

        $('#viewProgressImg').attr('src', img);
        $('#viewProgressTitle').text(title || 'Dokumentasi Progres');
        $('#viewProgressBadge').text(actual || '0%');
        $('#viewProgressPeriod').html('<i class="bi bi-calendar-range me-1"></i> ' + (period || '-'));
        $('#viewProgressDate').html('<i class="bi bi-calendar3 me-1"></i> ' + (date || '-'));
        $('#viewProgressNotes').text(notes || '-');

        $('#modalViewProgressPhoto').modal('show');
    });

    // Reset modals on 'Tambah' click
    $('button[data-bs-target="#ProgressModal"]').on('click', function() {
        resetProgressModal();
    });

    $('button[data-bs-target="#BudgetModal"]').on('click', function() {
        $('#formBudget')[0].reset();
        $('#budget_id').val('');
        $('#BudgetModalLabel').text('Tambah Realisasi Anggaran');
        $('#btnSaveBudget').text('Simpan Realisasi');
    });

});
