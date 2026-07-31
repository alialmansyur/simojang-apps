const MODULE_MONTHS = [
    { val: '01', text: 'Januari' },
    { val: '02', text: 'Februari' },
    { val: '03', text: 'Maret' },
    { val: '04', text: 'April' },
    { val: '05', text: 'Mei' },
    { val: '06', text: 'Juni' },
    { val: '07', text: 'Juli' },
    { val: '08', text: 'Agustus' },
    { val: '09', text: 'September' },
    { val: '10', text: 'Oktober' },
    { val: '11', text: 'November' },
    { val: '12', text: 'Desember' }
];

const MODULE_MAX_MONTH = 2;
let selectedMonths = [];
let currentKey = null;
let dtDetail = null;
let pond = null;
const emptyLottie = (window.ServiceTableUI && typeof window.ServiceTableUI.createEmptyLottie === 'function')
    ? window.ServiceTableUI.createEmptyLottie()
    : '<div class="text-center text-muted py-5">Tidak ada data.</div>';
const processingState = (window.ServiceTableUI && typeof window.ServiceTableUI.createProcessingState === 'function')
    ? window.ServiceTableUI.createProcessingState('Memuat data...')
    : '<div class="text-center text-muted py-4">Memuat data...</div>';
const DETAIL_HEADER_DEFAULT = `
    <tr>
        <th class="text-center">Logo</th>
        <th class="text-center">Nama Instansi</th>
        <th class="text-center">Formasi</th>
        <th class="text-center">Usul</th>
        <th class="text-center">MS</th>
        <th class="text-center">BTS</th>
        <th class="text-center">TMS</th>
        <th class="text-center">Sudah Cetak</th>
        <th class="text-center">Belum Cetak</th>
        <th class="text-center">Proses SK</th>
        <th class="text-center">Selesai SK</th>
        <th class="text-center">Usul Input</th>
        <th class="text-center">Proses NI</th>
        <th class="text-center">Selesai NI</th>
        <th class="text-center">Proses Cetak</th>
        <th class="text-center">Selesai Cetak</th>
        <th class="text-center">Jadwal Tunggu</th>
        <th class="text-center">SK PPPK Selesai</th>
    </tr>
`;
const DETAIL_HEADER_SPECIAL = `
    <tr>
        <th class="text-center">Nama Instansi</th>
        <th class="text-center">Target Tahun</th>
        <th class="text-center">Target Bulan</th>
        <th class="text-center">Usul</th>
        <th class="text-center">MS</th>
        <th class="text-center">BTS</th>
        <th class="text-center">TMS</th>
        <th class="text-center">Sisa</th>
        <th class="text-center">SLA Bawah</th>
        <th class="text-center">SLA Atas</th>
        <th class="text-center">Upload By</th>
        <th class="text-center">Tanggal Upload</th>
    </tr>
`;

function setDetailHeaderByLayanan(layananId) {
    const jenis = String(layananId || '');
    const isSpecial = ['3', '4', '5', '6', '7', '8', '9', '10', '11', '12'].includes(jenis);
    $('#dataTableDetail thead').html(isSpecial ? DETAIL_HEADER_SPECIAL : DETAIL_HEADER_DEFAULT);
}

function initMonthPicker() {
    const monthList = document.getElementById('monthList');
    if (!monthList) return;

    monthList.innerHTML = '';
    MODULE_MONTHS.forEach((m) => {
        monthList.insertAdjacentHTML('beforeend', `
            <li>
                <div class="form-check py-1">
                    <input class="form-check-input module-month-check" type="checkbox" value="${m.val}" id="moduleMonth${m.val}">
                    <label class="form-check-label fw-semibold" for="moduleMonth${m.val}">${m.text}</label>
                </div>
            </li>
        `);
    });
}

function getDocCategory() {
    const picker = $('#docCategoryPicker');
    if (!picker.length) return '';
    return String(picker.val() || '').trim();
}

function getDefaultDocCategory() {
    const picker = $('#docCategoryPicker');
    if (!picker.length) return '';
    const first = picker.find('option').first().val();
    return String(first || '').trim();
}

function updateActiveFilterLabels() {
    const $container = $('#activeFiltersLabel');
    const $catBadge = $('#filterCategoryBadge');
    const $monthBadge = $('#filterMonthBadge');

    if (!$container.length) return;

    let hasFilter = false;

    if ($('#docCategoryPicker').length) {
        const catText = $('#docCategoryPicker option:selected').text().trim();
        if (catText) {
            $catBadge.text('Kategori: ' + catText).show();
            hasFilter = true;
        } else {
            $catBadge.hide();
        }
    } else {
        $catBadge.hide();
    }

    if (selectedMonths && selectedMonths.length > 0) {
        const monthNames = MODULE_MONTHS
            .filter((m) => selectedMonths.includes(m.val))
            .map((m) => m.text)
            .join(', ');
        $monthBadge.text('Bulan: ' + monthNames).show();
        hasFilter = true;
    } else {
        $monthBadge.hide();
    }

    if (hasFilter) {
        $container.fadeIn(200);
    } else {
        $container.fadeOut(200);
    }
}

function setSummary(summary = {}) {
    const totalFile = Number(summary.total_file ?? 0);
    const totalDetail = Number(summary.total_baris_detail ?? 0);
    const totalInstansi = Number(summary.total_instansi ?? 0);
    const formatNumber = (window.ServiceTableUI && typeof window.ServiceTableUI.formatNumber === 'function')
        ? window.ServiceTableUI.formatNumber
        : function (value) { return new Intl.NumberFormat('id-ID').format(Number(value || 0)); };
    const formatDateTime = (window.ServiceTableUI && typeof window.ServiceTableUI.formatDateTime === 'function')
        ? window.ServiceTableUI.formatDateTime
        : function (value) { return value || '-'; };
    const lastUpload = formatDateTime(summary.last_upload);
    const firstUpload = formatDateTime(summary.first_upload);
    const activePeriods = Number(summary.active_periods ?? 0);

    $('.js-total-file').text(formatNumber(totalFile));
    $('.js-total-detail').text(formatNumber(totalDetail));
    $('.js-total-instansi').text(formatNumber(totalInstansi));
    $('.js-last-upload').text(lastUpload);
    $('.js-first-upload').text(firstUpload);
    $('.js-active-periods').text(formatNumber(activePeriods));
}

let summaryXhr = null;

function loadSummary() {
    if (summaryXhr) {
        summaryXhr.abort();
    }
    
    summaryXhr = $.ajax({
        url: AppConfig.initGlobal + 'fetch/summary-uploaders',
        type: 'POST',
        dataType: 'json',
        data: {
            layanan: $('#layanan_id').val(),
            bulan: selectedMonths,
            doc_category: getDocCategory()
        },
        success: function (response) {
            if (response && response.status === 'success') {
                setSummary(response.summary || {});
            }
        },
        error: function (xhr, status) {
            if (status !== 'abort') {
                setSummary({});
            }
        },
        complete: function () {
            summaryXhr = null;
        }
    });
}

function initPond() {
    const inputElement = document.querySelector('.basic-filepond');
    if (!inputElement || typeof FilePond === 'undefined') return null;

    return FilePond.create(inputElement, {
        credits: false,
        instantUpload: false,
        allowMultiple: false,
        acceptedFileTypes: [
            'application/vnd.ms-excel',
            'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet'
        ],
        labelIdle: 'Hanya file Excel (.xls, .xlsx) <span class="filepond--label-action">Browse</span>',
        labelFileTypeNotAllowed: 'File hanya boleh Excel (.xls, .xlsx)',
        fileValidateTypeLabelExpectedTypes: 'Hanya file Excel (.xls, .xlsx) yang diperbolehkan',
        fileValidateTypeDetectType: (source, type) => new Promise((resolve) => resolve(type))
    });
}

function ynIcon(data) {
    return data === 'Y'
        ? '<i class="bi bi-check-circle-fill text-success"></i>'
        : '<i class="bi bi-exclamation-triangle-fill text-warning"></i>';
}

function initDetailTable() {
    const jenis = String($('#layanan_id').val() || '');

    const defaultColumns = [
        {
            data: 'logo',
            className: 'text-center',
            render: function (data) {
                if (data) return `<img src="apps/assets/images/instansi/${data}" alt="logo" style="height:20px;">`;
                return '<span class="text-muted">No Logo</span>';
            }
        },
        { data: 'nama', render: (dt, t, r) => `<strong><a href="#" class="text-break" data-id="${r.id}">${dt}</a></strong>` },
        { data: 'formasi', className: 'text-center' },
        { data: 'usul_masuk', className: 'text-center' },
        { data: 'ms', className: 'text-center' },
        { data: 'bts', className: 'text-center' },
        { data: 'tms', className: 'text-center' },
        { data: 'sudah_cetak', className: 'text-center' },
        { data: 'belum_cetak', className: 'text-center' },
        { data: 'sk_cpppk_proses', className: 'text-center', render: ynIcon },
        { data: 'sk_cpppk_done', className: 'text-center', render: ynIcon },
        { data: 'usul_input', className: 'text-center', render: ynIcon },
        { data: 'ni_proses', className: 'text-center', render: ynIcon },
        { data: 'ni_done', className: 'text-center', render: ynIcon },
        { data: 'sk_cetak_proses', className: 'text-center', render: ynIcon },
        { data: 'sk_cetak_done', className: 'text-center', render: ynIcon },
        { data: 'jadwal_wait', className: 'text-center', render: ynIcon },
        { data: 'sk_pppk_done', className: 'text-center', render: ynIcon }
    ];

    const specialColumns = [
        { data: 'nama', render: (dt, t, r) => `<strong><a href="#" class="text-break" data-id="${r.id}">${dt}</a></strong>` },
        { data: 'target_tahun', className: 'text-center' },
        { data: 'target_bulan', className: 'text-center' },
        { data: 'usul_masuk', className: 'text-center' },
        { data: 'ms', className: 'text-center' },
        { data: 'bts', className: 'text-center' },
        { data: 'tms', className: 'text-center' },
        { data: 'sisa', className: 'text-center' },
        { data: 'sla_bawah', className: 'text-center' },
        { data: 'sla_atas', className: 'text-center' },
        { data: 'created_by' },
        { data: 'created_at' }
    ];

    const selectedColumns = ['3', '4', '5', '6', '7', '8', '9', '10', '11', '12'].includes(jenis)
        ? specialColumns
        : defaultColumns;

    dtDetail = $('#dataTableDetail').DataTable({
        autoWidth: false,
        processing: true,
        serverSide: true,
        order: [[1, 'asc']],
        dom: 'Bfrtip',
        buttons: ['copy', 'excel', 'pdf', 'print'],
        ajax: {
            url: AppConfig.initGlobal + 'fetch/data-detail',
            type: 'POST',
            data: function (d) {
                d.key = currentKey;
            },
            error: function () {
                swlErrorHandler('Gagal memuat detail data.');
            }
        },
        columns: selectedColumns,
        language: {
            emptyTable: emptyLottie,
            zeroRecords: emptyLottie.replace('untuk saat ini', 'sesuai filter'),
            processing: processingState
        },
        drawCallback: function () {
            dtDetail.columns.adjust().responsive.recalc();
        }
    });
}

$(document).ready(function () {
    initMonthPicker();
    pond = initPond();
    const layananId = String($('#layanan_id').val() || '').trim();
    const serviceKey = `snp${layananId || 'module'}`;

    const table = $('#dataTable').DataTable({
        responsive: {
            details: {
                type: 'column',
                target: 'td.dtr-control'
            }
        },
        processing: true,
        serverSide: true,
        order: [[1, 'asc']],
        dom: 'Bfrtip',
        buttons: ['copy', 'excel', 'pdf', 'print'],
        ajax: {
            url: AppConfig.initGlobal + 'fetch/data',
            type: 'POST',
            data: function (d) {
                d.layanan = $('#layanan_id').val();
                d.bulan = selectedMonths;
                d.doc_category = getDocCategory();
            },
            error: function (xhr) {
                const message = xhr.responseJSON && xhr.responseJSON.error
                    ? xhr.responseJSON.error
                    : 'Gagal memuat data.';
                swlErrorHandler(message);
            }
        },
        columnDefs: [{
            className: 'dtr-control',
            orderable: false,
            targets: 0
        }],
        columns: [
            { data: null, defaultContent: '' },
            {
                data: 'file_name',
                render: function (data, type, row) {
                    return `<strong><a href="#" class="btn-detail" data-id="${row.id}">${data}</a></strong>`;
                }
            },
            { data: 'period' },
            { data: 'period_date' },
            { data: 'created_at' },
            { data: 'created_by' },
            {
                data: null,
                orderable: false,
                searchable: false,
                render: function (data, type, row) {
                    return `<button class="btn btn-sm btn-danger btn-remove" data-id="${row.id}"><i class='bi bi-trash'></i></button>`;
                }
            }
        ],
        language: {
            emptyTable: emptyLottie,
            zeroRecords: emptyLottie.replace('untuk saat ini', 'hasil pencarian'),
            processing: processingState
        }
    });
    if (window.ServiceTableUI) {
        window.ServiceTableUI.setup({
            key: serviceKey,
            table,
            loadSummary,
            reloadSummaryOnClick: false,
            disableRecap: true,
            processingText: 'Memuat data...'
        });
    }
    table.on('xhr.dt', function () {
        loadSummary();
    });

    $(document).on('change', '.module-month-check', function () {
        const checked = $('.module-month-check:checked');
        if (checked.length > MODULE_MAX_MONTH) {
            this.checked = false;
            swlErrorHandler('Maksimal 2 bulan.');
        }
    });

    $('#applyMonth').on('click', function () {
        selectedMonths = $('.module-month-check:checked').map(function () {
            return this.value;
        }).get();

        if (selectedMonths.length > MODULE_MAX_MONTH) {
            swlErrorHandler('Silakan pilih maksimal 2 bulan saja.');
            return;
        }

        if (selectedMonths.length > 0) {
            const names = MODULE_MONTHS
                .filter((m) => selectedMonths.includes(m.val))
                .map((m) => m.text.substring(0, 3));
            $('#monthDropdownBtn').text(names.join(', '));
        } else {
            $('#monthDropdownBtn').text('Pilih Bulan');
        }

        table.ajax.reload(null, false);
        if (typeof updateActiveFilterLabels === 'function') updateActiveFilterLabels();
    });

    $('#docCategoryPicker').on('change', function () {
        $('#doc_category').val($(this).val() || '');
        table.ajax.reload(null, false);
        if (typeof updateActiveFilterLabels === 'function') updateActiveFilterLabels();
    });

    $('.sbmt').on('click', function (e) {
        e.preventDefault();
        const form = document.getElementById('UploadData');
        const submitBtn = $(this);

        if (pond && pond.getFiles().length === 0) {
            swlErrorHandler('Silakan pilih file Excel terlebih dahulu.');
            return;
        }

        if ($('#docCategoryPicker').length && !getDocCategory()) {
            swlErrorHandler('Silakan pilih kategori data terlebih dahulu.');
            return;
        }

        const fd = new FormData(form);
        fd.set('doc_category', getDocCategory());

        if (pond) {
            pond.getFiles().forEach((item) => {
                fd.append('file', item.file, item.file.name);
            });
        }

        $('#uploadModal').modal('hide');
        swlwaitProsessing();
        submitBtn.prop('disabled', true);

        $.ajax({
            url: AppConfig.initGlobal + 'store/import-excel',
            type: 'POST',
            data: fd,
            processData: false,
            contentType: false,
            success: function (response) {
                if (response.status === 'error') {
                    swlErrorHandler(response.message);
                    return;
                }

                table.ajax.reload(null, false);
                if (pond) pond.removeFiles();
                form.reset();
                if ($('#docCategoryPicker').length) {
                    const defaultCategory = getDefaultDocCategory();
                    $('#docCategoryPicker').val(defaultCategory).trigger('change');
                }
                swlSuccess();
            },
            error: function (xhr) {
                const message = xhr.responseJSON && xhr.responseJSON.message
                    ? xhr.responseJSON.message
                    : 'Upload data gagal diproses.';
                swlErrorHandler(message);
            },
            complete: function () {
                submitBtn.prop('disabled', false);
            }
        });
    });

    $('#dataTable tbody').on('click', 'tr td .btn-remove', function () {
        const key = $(this).attr('data-id');
        Swal.fire({
            text: 'Apa anda yakin akan mengahapus data ini ?',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d63031',
            confirmButtonText: 'Ya',
            cancelButtonText: 'Tidak'
        }).then((result) => {
            if (!result.isConfirmed) return;

            $.ajax({
                type: 'POST',
                url: AppConfig.initGlobal + 'kill/data-uploaders',
                data: { key: key },
                success: function (response) {
                    if (response) {
                        table.ajax.reload(null, false);
                        swlSuccess();
                    }
                },
                error: function () {
                    swlErrorHandler('Gagal menghapus data.');
                }
            });
        });
    });

    $('#dataTable tbody').on('click', '.btn-detail', function (e) {
        e.preventDefault();
        currentKey = $(this).data('id');
        const modalEl = document.getElementById('fileDetailModal');
        bootstrap.Modal.getOrCreateInstance(modalEl).show();

        if (!dtDetail) {
            initDetailTable();
            return;
        }
        dtDetail.ajax.reload(null, false);
    });

    $('#fileDetailModal').on('hidden.bs.modal', function () {
        if (dtDetail) dtDetail.columns.adjust();
    });

    if ($('#docCategoryPicker').length) {
        const defaultCategory = getDefaultDocCategory();
        $('#docCategoryPicker').val(defaultCategory);
        $('#doc_category').val(defaultCategory);
    }

    setDetailHeaderByLayanan($('#layanan_id').val());
    
    if (typeof updateActiveFilterLabels === 'function') {
        updateActiveFilterLabels();
    }
});
