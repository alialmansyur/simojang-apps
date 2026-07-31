$(document).ready(function () {
    'use strict';

    const PDBCApp = window.PDBCApp || {};
    PDBCApp.options = PDBCApp.options || {};
    PDBCApp.options.kategori = Array.isArray(PDBCApp.options.kategori) ? PDBCApp.options.kategori : [];
    PDBCApp.options.years = Array.isArray(PDBCApp.options.years) ? PDBCApp.options.years : [];
    PDBCApp.selectedYear = Number(PDBCApp.selectedYear || 2025);
    PDBCApp.selectedCategory = Number(PDBCApp.selectedCategory || 0);
    PDBCApp.selectedJenis = String(PDBCApp.selectedJenis || 'ALL').toUpperCase();
    PDBCApp.selectedMonths = Array.isArray(PDBCApp.selectedMonths) ? PDBCApp.selectedMonths : [];
    PDBCApp.table = PDBCApp.table || null;
    window.PDBCApp = PDBCApp;

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

    function getFilteredCategories(forModalInput = false) {
        const activeOnly = forModalInput;
        return PDBCApp.options.kategori.filter((item) => {
            const isActive = Number(item.is_active || 0) === 1;
            const jenis = String(item.jenis_layanan || '').toUpperCase();
            const jenisMatch = PDBCApp.selectedJenis === 'ALL' ? true : (jenis === PDBCApp.selectedJenis);
            if (activeOnly && !isActive) return false;
            return jenisMatch;
        });
    }

    function fillCategoryOptions() {
        const $input = $('#pdbcKategoriInput');

        $input.html('');

        const listForInput = getFilteredCategories(true);

        listForInput.forEach((item) => {
            $input.append(`<option value="${item.id}">${item.nama}</option>`);
        });

        if (!$input.find('option').length) {
            $input.append('<option value="">Kategori belum tersedia</option>');
        }
    }

    function getSelectedCategory() {
        const kategoriId = Number($('#pdbcKategoriInput').val() || 0);
        return PDBCApp.options.kategori.find((item) => Number(item.id) === kategoriId) || null;
    }

    function syncFormByKategori() {
        const kategori = getSelectedCategory();
        const jenis = String(kategori?.jenis_layanan || '').toUpperCase();
        const isKonsultasi = jenis === 'KONSULTASI';

        $('.pdbc-konsultasi-only').toggleClass('d-none', !isKonsultasi);
        $('.pdbc-kegiatan-only').toggleClass('d-none', isKonsultasi);

        const $source = $('#pdbcForm [name="source_konsultasi"]');
        const $tempat = $('#pdbcForm [name="tempat_kegiatan"]');
        const $judul = $('#pdbcForm [name="judul_kegiatan"]');
        const $surat = $('#pdbcForm [name="no_surat_kegiatan"]');
        const $pegawai = $('#pdbcForm [name="pegawai_ids[]"]');

        $source.prop('required', isKonsultasi);
        $tempat.prop('required', !isKonsultasi);
        $judul.prop('required', !isKonsultasi);
        $surat.prop('required', !isKonsultasi);
        $pegawai.prop('required', !isKonsultasi);

        if (isKonsultasi) {
            $tempat.val('');
            $judul.val('');
            $surat.val('');
            if ($pegawai.hasClass('select2-hidden-accessible')) {
                $pegawai.val(null).trigger('change');
            } else {
                $pegawai.val([]);
            }
        } else {
            $source.val('');
        }
    }

    function syncSelectedYearFromOptions() {
        const years = PDBCApp.options.years.length ? PDBCApp.options.years : [new Date().getFullYear()];
        const selectedYear = Number(years[0] || new Date().getFullYear());
        if (!PDBCApp.selectedYear || years.indexOf(PDBCApp.selectedYear) === -1) {
            PDBCApp.selectedYear = selectedYear;
        }
    }

    function fillMonthOptions() {
        const $monthList = $('#pdbcMonthList');
        if (!$monthList.length) return;

        $monthList.html('');
        monthOptions.forEach((item) => {
            const checked = PDBCApp.selectedMonths.includes(item.val) ? 'checked' : '';
            $monthList.append(`
                <li>
                    <div class="form-check py-1">
                        <input class="form-check-input pdbc-month-check" type="checkbox" value="${item.val}" id="pdbcMonth${item.val}" ${checked}>
                        <label class="form-check-label fw-semibold" for="pdbcMonth${item.val}">${item.text}</label>
                    </div>
                </li>
            `);
        });
    }

    function updateMonthButtonLabel() {
        const $btn = $('#pdbcMonthDropdownBtn');
        if (!$btn.length) return;

        if (!PDBCApp.selectedMonths.length) {
            $btn.text('Pilih Bulan');
            return;
        }

        const labels = monthOptions
            .filter((item) => PDBCApp.selectedMonths.includes(item.val))
            .map((item) => item.text.slice(0, 3));
        $btn.text(labels.join(', '));
    }

    function updateActiveFilterLabels() {
        const $container = $('#activeFiltersLabel');
        const $jenisBadge = $('#filterJenisBadge');
        const $monthBadge = $('#filterMonthBadge');
    
        if (!$container.length) return;
    
        let hasFilter = false;
    
        // Update Jenis Filter
        if ($('#pdbcJenisFilter').length) {
            const jenisText = $('#pdbcJenisFilter option:selected').text().trim();
            if (jenisText && $('#pdbcJenisFilter').val() !== 'ALL') {
                $jenisBadge.html(`<i class="bi bi-tag me-1"></i>Kategori: ${jenisText}`).show();
                hasFilter = true;
            } else {
                $jenisBadge.hide();
            }
        } else {
            $jenisBadge.hide();
        }
    
        // Update Month Filter
        if (PDBCApp.selectedMonths && PDBCApp.selectedMonths.length > 0) {
            const monthOptions = [
                { val: 1, text: 'Januari' }, { val: 2, text: 'Februari' }, { val: 3, text: 'Maret' },
                { val: 4, text: 'April' }, { val: 5, text: 'Mei' }, { val: 6, text: 'Juni' },
                { val: 7, text: 'Juli' }, { val: 8, text: 'Agustus' }, { val: 9, text: 'September' },
                { val: 10, text: 'Oktober' }, { val: 11, text: 'November' }, { val: 12, text: 'Desember' }
            ];
            const labels = monthOptions
                .filter((item) => PDBCApp.selectedMonths.includes(item.val))
                .map((item) => item.text.slice(0, 3));
            
            $monthBadge.html(`<i class="bi bi-calendar me-1"></i>Bulan: ${labels.join(', ')}`).show();
            hasFilter = true;
        } else {
            $monthBadge.hide();
        }
    
        if (hasFilter) {
            $container.show();
        } else {
            $container.hide();
        }
    }

    function loadOptions() {
        return $.ajax({
            url: AppConfig.initGlobal + 'fetch/options-pembinaan-disiplin-budaya-citra',
            type: 'POST',
            dataType: 'json'
        }).done((response) => {
            if (response?.status !== 'success') return;
            PDBCApp.options.kategori = Array.isArray(response.kategori) ? response.kategori : [];
            PDBCApp.options.years = Array.isArray(response.years) ? response.years.map((x) => Number(x)) : [];
            syncSelectedYearFromOptions();
            fillMonthOptions();
            updateMonthButtonLabel();
            fillCategoryOptions();
        });
    }

    function initModalSelect2() {
        $(document).off('shown.bs.modal.pdbcinstansi');
        $(document).on('shown.bs.modal.pdbcinstansi', function (e) {
            const modal = $(e.target);
            if (modal.attr('id') !== 'pdbcDataModal') return;

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
                            return { search: params.term };
                        },
                        processResults: function (data) {
                            return { results: data };
                        },
                        cache: true
                    }
                });
            });

            modal.find('.select-pegawai').each(function () {
                if ($(this).hasClass('select2-hidden-accessible')) {
                    $(this).select2('destroy');
                }

                $(this).select2({
                    theme: 'bootstrap-5',
                    width: '100%',
                    dropdownParent: modal,
                    minimumInputLength: 0,
                    ajax: {
                        url: AppConfig.initGlobal + 'select2/list',
                        type: 'POST',
                        dataType: 'json',
                        delay: 300,
                        data: function (params) {
                            return {
                                search: params.term,
                                source: 'data_pegawai'
                            };
                        },
                        processResults: function (data) {
                            return { results: data };
                        },
                        cache: true
                    }
                });
            });

            syncFormByKategori();
        });

        $('#pdbcDataModal').off('hidden.bs.modal.pdbcinstansi');
        $('#pdbcDataModal').on('hidden.bs.modal.pdbcinstansi', function () {
            const $form = $('#pdbcForm');
            $form[0].reset();
            $form.find('[name="key"]').val('');
            $('#pdbcDataModalLabel').text('Tambah Data Pembinaan Disiplin');
            $form.find('[name="period_year"]').val(PDBCApp.selectedYear);
            $form.find('[name="period_date"]').val(getCurrentDate());

            const $instansi = $form.find('.select-instansi');
            if ($instansi.hasClass('select2-hidden-accessible')) {
                $instansi.val(null).trigger('change');
                $instansi.select2('destroy');
            }

            const $pegawai = $form.find('.select-pegawai');
            if ($pegawai.hasClass('select2-hidden-accessible')) {
                $pegawai.val(null).trigger('change');
                $pegawai.select2('destroy');
            }
        });
    }

    function attachFormActions() {
        $('#pdbcSubmitBtn').on('click', function () {
            $('#pdbcForm').trigger('submit');
        });

        $('#pdbcForm').on('submit', function (event) {
            event.preventDefault();
            $('#pdbcDataModal').modal('hide');
            swlwaitProsessing();

            $.ajax({
                url: AppConfig.initGlobal + 'store/save-data-pembinaan-disiplin-budaya-citra',
                type: 'POST',
                data: $(this).serialize(),
                dataType: 'json'
            }).done(function (response) {
                if (response?.status !== 'success') {
                    swlErrorHandler(response?.message || 'Gagal menyimpan data.');
                    return;
                }

                swlSuccess();
                if (PDBCApp.table) PDBCApp.table.ajax.reload(null, false);
                if (typeof PDBCApp.refreshSummary === 'function') PDBCApp.refreshSummary();
            }).fail(function () {
                swlErrorHandler('Terjadi kendala saat menyimpan data.');
            });
        });
    }

    function attachFilterActions() {
        $('#pdbcJenisFilter').on('change', function () {
            PDBCApp.selectedJenis = String($(this).val() || 'ALL').toUpperCase();
            fillCategoryOptions();
            syncFormByKategori();
            if (PDBCApp.table) PDBCApp.table.ajax.reload();
            if (typeof PDBCApp.refreshSummary === 'function') PDBCApp.refreshSummary();
            updateActiveFilterLabels();
        });

        $(document).on('change', '.pdbc-month-check', function () {
            const checked = $('.pdbc-month-check:checked');
            if (checked.length > 2) {
                this.checked = false;
                swlErrorHandler('Riwayat ditampilkan maksimal 2 bulan.');
            }
        });

        $('#pdbcApplyMonth').on('click', function () {
            PDBCApp.selectedMonths = $('.pdbc-month-check:checked')
                .map(function () { return Number($(this).val() || 0); })
                .get()
                .filter((v) => v >= 1 && v <= 12);

            updateMonthButtonLabel();
            if (PDBCApp.table) PDBCApp.table.ajax.reload();
            if (typeof PDBCApp.refreshSummary === 'function') PDBCApp.refreshSummary();
            updateActiveFilterLabels();
        });
    }

    PDBCApp.openEditModal = function (row) {
        const $form = $('#pdbcForm');
        $('#pdbcDataModalLabel').text('Update Data Pembinaan Disiplin');
        $form.find('[name="key"]').val(row.id || '');
        $form.find('[name="kategori_id"]').val(row.kategori_id || '');
        $form.find('[name="period_year"]').val(row.period_year || PDBCApp.selectedYear);
        $form.find('[name="period_date"]').val(row.period_date || getCurrentDate());
        $form.find('[name="source_konsultasi"]').val(row.source_konsultasi || '');
        $form.find('[name="tempat_kegiatan"]').val(row.tempat_kegiatan || '');
        $form.find('[name="judul_kegiatan"]').val(row.judul_kegiatan || '');
        $form.find('[name="no_surat_kegiatan"]').val(row.no_surat_kegiatan || '');
        $form.find('[name="catatan"]').val(row.catatan || '');
        syncFormByKategori();

        $('#pdbcDataModal').modal('show');
        $('#pdbcDataModal').one('shown.bs.modal', function () {
            const $instansi = $form.find('[name="instansi"]');
            const instansiId = row.instansi_id || '';
            const instansiName = row.instansi_name || '';
            if (instansiId) {
                const option = new Option(instansiName, instansiId, true, true);
                $instansi.append(option).trigger('change');
            }

            const ids = String(row.pegawai_ids || '').split('||').filter((x) => x && x !== '0');
            const names = String(row.pegawai_names || '').split('||').filter((x) => x);
            const $pegawai = $form.find('[name="pegawai_ids[]"]');
            if (ids.length && $pegawai.length) {
                ids.forEach((id, idx) => {
                    const label = names[idx] || `Pegawai ${id}`;
                    const option = new Option(label, id, true, true);
                    $pegawai.append(option);
                });
                $pegawai.trigger('change');
            }
        });
    };

    loadOptions().always(function () {
        attachFilterActions();
        initModalSelect2();
        attachFormActions();

        $('#pdbcForm').find('[name="period_year"]').val(PDBCApp.selectedYear);
        $('#pdbcForm').find('[name="period_date"]').val(getCurrentDate());
        $('#pdbcJenisFilter').val(PDBCApp.selectedJenis);
        $('#pdbcKategoriInput').on('change', syncFormByKategori);
        syncFormByKategori();
        updateActiveFilterLabels();
    });
});
