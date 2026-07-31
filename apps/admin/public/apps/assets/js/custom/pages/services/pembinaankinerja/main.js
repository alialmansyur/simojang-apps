$(document).ready(function () {
    'use strict';

    const PKApp = window.PKApp || {};
    PKApp.options = PKApp.options || {};
    PKApp.options.kategori = Array.isArray(PKApp.options.kategori) ? PKApp.options.kategori : [];
    PKApp.options.years = Array.isArray(PKApp.options.years) ? PKApp.options.years : [];
    PKApp.selectedYear = Number(PKApp.selectedYear || new Date().getFullYear());
    PKApp.selectedCategory = Number(PKApp.selectedCategory || 0);
    
    PKApp.selectedMonths = Array.isArray(PKApp.selectedMonths) && PKApp.selectedMonths.length > 0 ? PKApp.selectedMonths : [];
    
    PKApp.table = PKApp.table || null;
    window.PKApp = PKApp;

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

    function fillCategoryOptions() {
        const $input = $('#pkKategoriInput');
        const $filter = $('#pkCategoryFilter');

        $input.html('');
        $filter.find('option:not([value="0"])').remove();

        PKApp.options.kategori.forEach((item) => {
            $input.append(`<option value="${item.id}" data-code="${item.code}">${item.nama}</option>`);
            $filter.append(`<option value="${item.id}">${item.nama}</option>`);
        });
    }

    function resolvePayloadCodes() {
        const kategoriId = Number($('#pkKategoriInput').val() || 0);
        const item = PKApp.options.kategori.find((x) => Number(x.id) === kategoriId);
        const kegiatan = item?.code || '';
        let aplikasi = 'EKINERJA_BKN';

        if (kegiatan === 'PELAPORAN_SKP') aplikasi = 'SIASN';
        if (kegiatan === 'PEMANFAATAN_EKINERJA') aplikasi = 'EKINERJA_MANDIRI';

        $('#pkForm [name="kegiatan_code"]').val(kegiatan);
        $('#pkForm [name="aplikasi_code"]').val(aplikasi);
    }

    function fillYearOptions() {
        const $year = $('#pkYearFilter');
        const currentYear = new Date().getFullYear();
        let years = [currentYear - 1, currentYear, currentYear + 1];

        $year.html('<option value="">Pilih Periode</option>');
        years.forEach((year) => {
            const selected = Number(year) === Number(PKApp.selectedYear) ? 'selected' : '';
            $year.append(`<option value="${year}" ${selected}>Periode ${year}</option>`);
        });
    }

    function fillMonthOptions() {
        const $monthList = $('#pkMonthList');
        if (!$monthList.length) return;

        $monthList.html('');
        monthOptions.forEach((item) => {
            const checked = PKApp.selectedMonths.includes(item.val) ? 'checked' : '';
            $monthList.append(`
                <li>
                    <div class="form-check py-1">
                        <input class="form-check-input pk-month-check" type="checkbox" value="${item.val}" id="pkMonth${item.val}" ${checked}>
                        <label class="form-check-label fw-semibold" for="pkMonth${item.val}">${item.text}</label>
                    </div>
                </li>
            `);
        });
    }

    function updateMonthButtonLabel() {
        const $btn = $('#pkMonthDropdownBtn');
        if (!$btn.length) return;

        if (!PKApp.selectedMonths.length) {
            $btn.text('Pilih Bulan');
            return;
        }

        const labels = monthOptions
            .filter((item) => PKApp.selectedMonths.includes(item.val))
            .map((item) => item.text.slice(0, 3));
        $btn.text(labels.join(', '));
    }

    function updateActiveFilterLabels() {
        const $container = $('#activeFiltersLabel');
        const $yearBadge = $('#filterYearBadge');
        const $kategoriBadge = $('#filterKategoriBadge');
        const $monthBadge = $('#filterMonthBadge');
    
        if (!$container.length) return;
    
        let hasFilter = false;
    
        // Update Year Filter
        if ($('#pkYearFilter').length) {
            const yearVal = $('#pkYearFilter').val();
            const yearText = $('#pkYearFilter option:selected').text().trim();
            if (yearVal && yearVal !== '') {
                $yearBadge.html(`<i class="bi bi-calendar me-1"></i>Tahun: ${yearText}`).show();
                hasFilter = true;
            } else {
                $yearBadge.hide();
            }
        } else {
            $yearBadge.hide();
        }

        // Update Kategori Filter
        if ($('#pkCategoryFilter').length) {
            const kategoriText = $('#pkCategoryFilter option:selected').text().trim();
            if (kategoriText && $('#pkCategoryFilter').val() !== '0') {
                $kategoriBadge.html(`<i class="bi bi-tag me-1"></i>Kategori: ${kategoriText}`).show();
                hasFilter = true;
            } else {
                $kategoriBadge.hide();
            }
        } else {
            $kategoriBadge.hide();
        }
    
        // Update Month Filter
        if (PKApp.selectedMonths && PKApp.selectedMonths.length > 0) {
            const labels = monthOptions
                .filter((item) => PKApp.selectedMonths.includes(item.val))
                .map((item) => item.text.slice(0, 3));
            
            $monthBadge.html(`<i class="bi bi-calendar me-1"></i>Bulan: ${labels.join(', ')}`).show();
            hasFilter = true;
        } else {
            $monthBadge.hide();
        }
    
        if (hasFilter) {
            $container.addClass('d-flex').show();
        } else {
            $container.removeClass('d-flex').hide();
        }
    }

    function loadOptions() {
        return $.ajax({
            url: AppConfig.initGlobal + 'fetch/options-pembinaan-kinerja',
            type: 'POST',
            dataType: 'json'
        }).done((response) => {
            if (response?.status !== 'success') return;
            PKApp.options.kategori = Array.isArray(response.kategori) ? response.kategori : [];
            PKApp.options.years = Array.isArray(response.years) ? response.years : [];
            fillCategoryOptions();
            fillYearOptions();
            fillMonthOptions();
            updateMonthButtonLabel();
        });
    }

    function initModalSelect2() {
        $(document).off('shown.bs.modal.pkinstansi');
        $(document).on('shown.bs.modal.pkinstansi', function (e) {
            const modal = $(e.target);
            if (modal.attr('id') !== 'pkDataModal') return;

            modal.find('.select-instansi').each(function () {
                if ($(this).hasClass('select2-hidden-accessible')) {
                    $(this).select2('destroy');
                }

                $(this).select2({
                    theme: 'bootstrap-5',
                    width: '100%',
                    dropdownParent: modal,
                    minimumInputLength: 0,
                    ajax: {
                        url: AppConfig.initGlobal + 'instansi-list',
                        type: 'POST',
                        dataType: 'json',
                        delay: 300,
                        data: function (params) {
                            return {
                                search: params.term
                            };
                        },
                        processResults: function (data) {
                            return {
                                results: data
                            };
                        },
                        cache: true
                    }
                });
            });
        });

        $('#pkDataModal').off('hidden.bs.modal.pkinstansi');
        $('#pkDataModal').on('hidden.bs.modal.pkinstansi', function () {
            const $form = $('#pkForm');
            $form[0].reset();
            $form.find('[name="key"]').val('');
            $('#pkDataModalLabel').text('Tambah Data Pembinaan Kinerja');
            $form.find('[name="period_year"]').val(PKApp.selectedYear);
            $form.find('[name="period_date"]').val(getCurrentDate());

            const $instansi = $form.find('.select-instansi');
            if ($instansi.hasClass('select2-hidden-accessible')) {
                $instansi.val(null).trigger('change');
                $instansi.select2('destroy');
            }
        });
    }

    function attachFormActions() {
        $('#pkSubmitBtn').on('click', function () {
            $('#pkForm').trigger('submit');
        });

        $('#pkForm').on('submit', function (event) {
            event.preventDefault();
            resolvePayloadCodes();
            $('#pkDataModal').modal('hide');
            swlwaitProsessing();

            $.ajax({
                url: AppConfig.initGlobal + 'store/save-data-pembinaan-kinerja',
                type: 'POST',
                data: $(this).serialize(),
                dataType: 'json'
            }).done(function (response) {
                if (response?.status !== 'success') {
                    swlErrorHandler(response?.message || 'Gagal menyimpan data.');
                    return;
                }

                swlSuccess();
                if (PKApp.table) PKApp.table.ajax.reload(null, false);
                if (typeof PKApp.refreshSummary === 'function') PKApp.refreshSummary();
            }).fail(function () {
                swlErrorHandler('Terjadi kendala saat menyimpan data.');
            });
        });
    }

    function attachFilterActions() {
        $('#pkYearFilter').on('change', function () {
            PKApp.selectedYear = Number($(this).val() || new Date().getFullYear());
            if (PKApp.table) PKApp.table.ajax.reload();
            if (typeof PKApp.refreshSummary === 'function') PKApp.refreshSummary();
            updateActiveFilterLabels();
        });

        $('#pkCategoryFilter').on('change', function () {
            PKApp.selectedCategory = Number($(this).val() || 0);
            if (PKApp.table) PKApp.table.ajax.reload();
            if (typeof PKApp.refreshSummary === 'function') PKApp.refreshSummary();
            updateActiveFilterLabels();
        });

        $(document).on('change', '.pk-month-check', function () {
            const checked = $('.pk-month-check:checked');
            if (checked.length > 2) {
                this.checked = false;
                swlErrorHandler('Riwayat ditampilkan maksimal 2 bulan.');
            }
        });

        $('#pkApplyMonth').on('click', function () {
            PKApp.selectedMonths = $('.pk-month-check:checked')
                .map(function () { return Number($(this).val() || 0); })
                .get()
                .filter((v) => v >= 1 && v <= 12);

            updateMonthButtonLabel();
            if (PKApp.table) PKApp.table.ajax.reload();
            if (typeof PKApp.refreshSummary === 'function') PKApp.refreshSummary();
            updateActiveFilterLabels();
        });
    }

    PKApp.openEditModal = function (row) {
        const $form = $('#pkForm');
        $('#pkDataModalLabel').text('Update Data Pembinaan Kinerja');
        $form.find('[name="key"]').val(row.id || '');
        $form.find('[name="kategori_id"]').val(row.kategori_id || '');
        $form.find('[name="period_year"]').val(row.period_year || PKApp.selectedYear);
        $form.find('[name="period_date"]').val(row.period_date || getCurrentDate());
        $form.find('[name="capaian_percent"]').val(row.capaian_percent || 0);
        $form.find('[name="pendampingan_date"]').val(row.pendampingan_date || '');
        $form.find('[name="catatan"]').val(row.catatan || '');
        resolvePayloadCodes();

        $('#pkDataModal').modal('show');
        $('#pkDataModal').one('shown.bs.modal', function () {
            const $instansi = $form.find('[name="instansi"]');
            const instansiId = row.instansi_id || '';
            const instansiName = row.instansi_name || '';
            if (instansiId) {
                const option = new Option(instansiName, instansiId, true, true);
                $instansi.append(option).trigger('change');
            }
        });
    };

    loadOptions().always(function () {
        attachFilterActions();
        initModalSelect2();
        attachFormActions();

        $('#pkForm').find('[name="period_year"]').val(PKApp.selectedYear);
        $('#pkForm').find('[name="period_date"]').val(getCurrentDate());
        $('#pkKategoriInput').on('change', resolvePayloadCodes);
        updateActiveFilterLabels();
    });
});
