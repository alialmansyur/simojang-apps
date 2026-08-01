$(document).ready(function () {
    'use strict';

    const PKKApp = window.PKKApp || {};
    PKKApp.options = PKKApp.options || {};
    
    const now = new Date();
    const currentYear = now.getFullYear();
    const currentMonth = now.getMonth() + 1; // 1-12
    let t1 = currentMonth - 1;

    PKKApp.options.years = Array.isArray(PKKApp.options.years) && PKKApp.options.years.length > 0
        ? PKKApp.options.years 
        : [currentYear - 1, currentYear, currentYear + 1];
    
    PKKApp.selectedYear = PKKApp.selectedYear || '';
    
    PKKApp.selectedMonths = Array.isArray(PKKApp.selectedMonths) && PKKApp.selectedMonths.length > 0
        ? PKKApp.selectedMonths 
        : [];
        
    PKKApp.table = PKKApp.table || null;
    PKKApp.pond = null;
    window.PKKApp = PKKApp;

    const monthOptions = [
        { val: 1, text: 'Januari' },
        { val: 2, text: 'Februari' },
        { val: 3, text: 'Maret' },
        { val: 4, text: 'April' },
        { val: 5, text: 'Mei' },
        { val: 6, text: 'Juni' },
        { val: 7, text: 'Juli' },
        { val: 8, text: 'Agustus' },
        { val: 9, text: 'September' },
        { val: 10, text: 'Oktober' },
        { val: 11, text: 'November' },
        { val: 12, text: 'Desember' }
    ];

    function getCurrentDate() {
        const now = new Date();
        const y = now.getFullYear();
        const m = String(now.getMonth() + 1).padStart(2, '0');
        const d = String(now.getDate()).padStart(2, '0');
        return `${y}-${m}-${d}`;
    }

    function fillYearOptions() {
        const $year = $('#pkkYearFilter');
        const years = PKKApp.options.years;

        $year.html('<option value="">Pilih Tahun</option>');
        years.forEach((year) => {
            const selected = String(year) === String(PKKApp.selectedYear) ? 'selected' : '';
            $year.append(`<option value="${year}" ${selected}>Periode ${year}</option>`);
        });
    }

    function fillMonthOptions() {
        const $monthList = $('#pkkMonthList');
        if (!$monthList.length) return;

        $monthList.html('');
        monthOptions.forEach((item) => {
            const checked = PKKApp.selectedMonths.includes(item.val) ? 'checked' : '';
            $monthList.append(`
                <li>
                    <div class="form-check py-1">
                        <input class="form-check-input pkk-month-check" type="checkbox" value="${item.val}" id="pkkMonth${item.val}" ${checked}>
                        <label class="form-check-label fw-semibold" for="pkkMonth${item.val}">${item.text}</label>
                    </div>
                </li>
            `);
        });
    }

    function updateMonthButtonLabel() {
        const $btn = $('#pkkMonthDropdownBtn');
        if (!$btn.length) return;

        if (!PKKApp.selectedMonths.length) {
            $btn.text('Pilih Bulan');
            return;
        }

        const labels = monthOptions
            .filter((item) => PKKApp.selectedMonths.includes(item.val))
            .map((item) => item.text);
        $btn.text(labels.join(', '));
    }

    function updateActiveFilterLabels() {
        const $container = $('#activeFilterContainer');
        if (!$container.length) return;
        
        $container.find('.filter-badge').remove();

        let hasActiveFilters = false;

        if (PKKApp.selectedYear && $('#pkkYearFilter').val()) {
            const yearLabel = PKKApp.selectedYear;
            $container.append(`<span class="badge bg-light text-primary border border-primary me-1 mb-1 filter-badge" style="font-weight: 500;">${yearLabel}</span>`);
            hasActiveFilters = true;
        }

        if (PKKApp.selectedMonths && PKKApp.selectedMonths.length > 0) {
            const $monthCheckboxes = $('#pkkMonthList input.pkk-month-check:checked');
            if ($monthCheckboxes.length > 0) {
                const monthLabels = $monthCheckboxes.map(function() {
                    return $(this).next('label').text().trim();
                }).get().join(', ');
                $container.append(`<span class="badge bg-light text-primary border border-primary mb-1 filter-badge" style="font-weight: 500;">Bulan: ${monthLabels}</span>`);
                hasActiveFilters = true;
            }
        }

        if (hasActiveFilters) {
            $container.show();
            $container.addClass('d-flex');
        } else {
            $container.hide();
            $container.removeClass('d-flex');
        }
    }

    function loadOptions() {
        return $.ajax({
            url: AppConfig.initGlobal + 'fetch/options-pembinaan-kompetensi-karier',
            type: 'POST',
            dataType: 'json'
        }).done((response) => {
            if (response?.status !== 'success') return;
            PKKApp.options.years = Array.isArray(response.years) ? response.years : [];
            if (PKKApp.options.years.length && !PKKApp.selectedYear) {
                // leave it blank if no year is selected initially
            }
            fillYearOptions();
            fillMonthOptions();
            updateMonthButtonLabel();
            updateActiveFilterLabels();
        });
    }

    function attachFilterActions() {
        $('#pkkYearFilter').on('change', function () {
            PKKApp.selectedYear = $(this).val() || '';
            updateActiveFilterLabels();
            if (PKKApp.table) PKKApp.table.ajax.reload();
            if (typeof PKKApp.refreshSummary === 'function') PKKApp.refreshSummary();
        });

        $(document).on('change', '.pkk-month-check', function () {
            const checked = $('.pkk-month-check:checked');
            if (checked.length > 2) {
                this.checked = false;
                swlErrorHandler('Riwayat ditampilkan maksimal 2 bulan.');
            }
        });

        $('#pkkApplyMonth').on('click', function () {
            PKKApp.selectedMonths = $('.pkk-month-check:checked')
                .map(function () { return Number($(this).val() || 0); })
                .get()
                .filter((v) => v >= 1 && v <= 12);

            updateMonthButtonLabel();
            updateActiveFilterLabels();
            if (PKKApp.table) PKKApp.table.ajax.reload();
            if (typeof PKKApp.refreshSummary === 'function') PKKApp.refreshSummary();
        });
    }

    function syncPeriodFromDate() {
        const value = $('#pkkForm [name="tanggal_kegiatan"]').val();
        if (!value) return;
        const year = Number(String(value).slice(0, 4));
        if (year > 2000) {
            $('#pkkForm [name="period_year"]').val(year);
        }
    }

    function attachFormActions() {
        $('#pkkSubmitBtn').on('click', function () {
            $('#pkkForm').trigger('submit');
        });

        $('#pkkForm [name="tanggal_kegiatan"]').on('change', syncPeriodFromDate);

        $('#pkkForm').on('submit', function (event) {
            event.preventDefault();
            $('#pkkDataModal').modal('hide');
            swlwaitProsessing();

            $.ajax({
                url: AppConfig.initGlobal + 'store/save-data-pembinaan-kompetensi-karier',
                type: 'POST',
                data: $(this).serialize(),
                dataType: 'json'
            }).done(function (response) {
                if (response?.status !== 'success') {
                    swlErrorHandler(response?.message || 'Gagal menyimpan data.');
                    return;
                }

                swlSuccess();
                if (PKKApp.table) PKKApp.table.ajax.reload(null, false);
                if (typeof PKKApp.refreshSummary === 'function') PKKApp.refreshSummary();
            }).fail(function () {
                swlErrorHandler('Terjadi kendala saat menyimpan data.');
            });
        });

        $('#pkkDataModal').on('hidden.bs.modal', function () {
            const $form = $('#pkkForm');
            $form[0].reset();
            $form.find('[name="key"]').val('');
            $('#pkkDataModalLabel').text('Tambah Data Pengembangan Kompetensi');
            $form.find('[name="period_year"]').val(PKKApp.selectedYear);
            $form.find('[name="tanggal_kegiatan"]').val(getCurrentDate());
            $form.find('[name="metode"]').val('');
            
            $('.modal-backdrop').remove();
            $('body').removeClass('modal-open').css('overflow', '');
        });
        
        $('.btn-upload-submit').on('click', function (e) {
            e.preventDefault();
            const form = document.getElementById('UploadData');
            const submitBtn = $(this);

            if (PKKApp.pond && PKKApp.pond.getFiles().length === 0) {
                swlErrorHandler('Silakan pilih file Excel terlebih dahulu.');
                return;
            }

            const fd = new FormData(form);

            if (PKKApp.pond) {
                PKKApp.pond.getFiles().forEach((item) => {
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

                    if (PKKApp.table) PKKApp.table.ajax.reload(null, false);
                    if (typeof PKKApp.refreshSummary === 'function') PKKApp.refreshSummary();
                    if (PKKApp.pond) PKKApp.pond.removeFiles();
                    form.reset();
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

    PKKApp.openEditModal = function (row) {
        const $form = $('#pkkForm');
        $('#pkkDataModalLabel').text('Update Data Pengembangan Kompetensi');
        $form.find('[name="key"]').val(row.id || '');
        $form.find('[name="period_year"]').val(row.period_year || PKKApp.selectedYear);
        $form.find('[name="tanggal_kegiatan"]').val(row.tanggal_kegiatan || getCurrentDate());
        $form.find('[name="judul_kegiatan"]').val(row.judul_kegiatan || '');
        $form.find('[name="materi"]').val(row.materi || '');
        $form.find('[name="total_partisipan"]').val(row.total_partisipan || 0);
        $form.find('[name="metode"]').val(row.metode || '');
        $form.find('[name="lokasi"]').val(row.lokasi || '');
        $form.find('[name="penyelenggara"]').val(row.penyelenggara || '');
        $form.find('[name="eviden_link"]').val(row.eviden_link || '');
        $form.find('[name="catatan"]').val(row.catatan || '');
        $('#pkkDataModal').modal('show');
    };

    loadOptions().always(function () {
        attachFilterActions();
        attachFormActions();
        PKKApp.pond = initPond();
        $('#pkkForm [name="period_year"]').val(PKKApp.selectedYear);
        $('#pkkForm [name="tanggal_kegiatan"]').val(getCurrentDate());
    });
});
