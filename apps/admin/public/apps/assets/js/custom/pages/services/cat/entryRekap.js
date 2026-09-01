// =========================================================================
// Fasilitasi CAT - Entry Multi-Baris Rekap Sesi & Instansi (entryRekap.js)
// =========================================================================

$(document).ready(function () {
    const tbody = $('#usulanTableBody');
    const addRowBtn = $('#addRowBtn');
    const rekapModal = $('#DataModal');
    const selectNewInstansi = $('#selectNewInstansi');

    // Inisialisasi Select2 untuk Pilih Instansi di Modal (Standard Simojang Pattern)
    function initInstansiSelect(select, modal) {
        if (!select || !select.length) return;

        if (select.hasClass('select2-hidden-accessible')) {
            select.select2('destroy');
        }

        select.select2({
            theme: 'bootstrap-5',
            width: '100%',
            dropdownParent: modal,
            placeholder: 'Cari dan pilih instansi...',
            allowClear: true,
            minimumInputLength: 0,
            ajax: {
                url: AppConfig.initGlobal + 'instansi-list',
                type: 'POST',
                dataType: 'json',
                delay: 300,
                data: function (params) {
                    return {
                        search: params.term || ''
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
    }

    // Modal Event Bindings - Trigger Select2 saat modal ditampilkan
    $(document).on('shown.bs.modal', function (e) {
        const modal = $(e.target);
        modal.find('.select-instansi').each(function () {
            initInstansiSelect($(this), modal);
        });
    });

    // Modal Event Bindings - Destroy & Reset saat modal ditutup
    $('#DataModal, #DataModalDetail').on('hidden.bs.modal', function (e) {
        const modal = $(e.target);
        modal.find('.select-instansi').each(function () {
            if ($(this).hasClass('select2-hidden-accessible')) {
                $(this).select2('destroy');
            }
            $(this).val(null).trigger('change');
        });

        if (modal.is('#DataModal')) {
            tbody.empty();
            $('#form-usulan')[0].reset();
        } else if (modal.is('#DataModalDetail')) {
            const form = $('#form-usulan-edit');
            if (form.length) form[0].reset();
        }
    });

    // Helper: Menambahkan baris sesi baru
    function addRow() {
        const lastRow = tbody.find('tr:last');
        let refTanggal = '';
        let nextSesi = 1;

        if (lastRow.length) {
            refTanggal = lastRow.find('input[name="tanggal[]"]').val() || '';
            const lastSesiVal = parseInt(lastRow.find('input[name="sesi[]"]').val());
            if (!isNaN(lastSesiVal) && lastSesiVal > 0) {
                nextSesi = lastSesiVal + 1;
            }
        } else {
            // Default tanggal hari ini jika belum ada
            const today = new Date().toISOString().split('T')[0];
            refTanggal = today;
        }

        let instansiIdValue = '';
        if (window.CatDetailState && window.CatDetailState.rekapModalMode === 'active_instansi') {
            instansiIdValue = window.CatDetailState.activeInstansi.id || '';
        } else {
            instansiIdValue = selectNewInstansi.val() || '';
        }

        const newRow = $(`
            <tr>
                <td>
                    <input type="hidden" name="instansi[]" class="input-row-instansi" value="${instansiIdValue}">
                    <input type="date" name="tanggal[]" class="form-control form-control-sm" value="${refTanggal}" required>
                </td>
                <td>
                    <input type="number" name="sesi[]" class="form-control form-control-sm text-center" value="${nextSesi}" min="1" placeholder="1" required>
                </td>
                <td>
                    <input type="number" name="nilai_min[]" class="form-control form-control-sm" placeholder="0" min="0">
                </td>
                <td>
                    <input type="number" name="nilai_max[]" class="form-control form-control-sm" placeholder="0" min="0">
                </td>
                <td>
                    <input type="number" name="hadir[]" class="form-control form-control-sm text-center" placeholder="0" min="0">
                </td>
                <td>
                    <input type="number" name="tidak_hadir[]" class="form-control form-control-sm text-center" placeholder="0" min="0">
                </td>
                <td>
                    <input type="number" name="reschedule[]" class="form-control form-control-sm text-center" placeholder="0" min="0">
                </td>
                <td>
                    <input type="number" name="memenuhi[]" class="form-control form-control-sm text-center" placeholder="0" min="0">
                </td>
                <td>
                    <input type="number" name="tidak_memenuhi[]" class="form-control form-control-sm text-center" placeholder="0" min="0">
                </td>
                <td class="text-center">
                    <button type="button" class="btn btn-sm btn-outline-danger btn-delete-row" title="Hapus Baris">
                        <i class="bi bi-trash"></i>
                    </button>
                </td>
            </tr>
        `);

        tbody.append(newRow);
    }

    // Helper: Sinkronisasi Info Event & Titik Lokasi pada Header Modal
    function syncModalHeaderInfo() {
        const eventName = (window.CatDetailState && window.CatDetailState.tilokMeta && window.CatDetailState.tilokMeta.nama_seleksi) 
            || $('#catDetailEvent').text() 
            || '-';
        const tilokName = (window.CatDetailState && window.CatDetailState.tilokMeta && window.CatDetailState.tilokMeta.nama_tilok) 
            || $('#catDetailTilok').text() 
            || '-';

        $('#modalInfoEvent').text(eventName).attr('title', eventName);
        $('#modalInfoTilok').text(tilokName).attr('title', tilokName);
    }

    // Trigger Open Modal - Mode: Tambah Instansi Baru (Level 1)
    $('#btnOpenTambahInstansi').on('click', function () {
        if (!window.CatDetailState) return;
        window.CatDetailState.rekapModalMode = 'new_instansi';

        $('#instansiSelectorWrap').removeClass('d-none');
        $('#activeInstansiAlert').addClass('d-none');
        $('#DataModalLabelCreate').text('Entri Rekap - Instansi Baru');
        syncModalHeaderInfo();

        tbody.empty();
        addRow(); // Mulai dengan 1 baris
        rekapModal.modal('show');
    });

    // Trigger Open Modal - Mode: Tambah Sesi Rekap (Level 2)
    $('#btnOpenTambahSesi').on('click', function () {
        if (!window.CatDetailState) return;
        window.CatDetailState.rekapModalMode = 'active_instansi';

        const activeNama = window.CatDetailState.activeInstansi.nama || 'Instansi Terpilih';
        $('#instansiSelectorWrap').addClass('d-none');
        $('#activeInstansiAlert').removeClass('d-none');
        $('#activeInstansiLabel').text(activeNama);
        $('#DataModalLabelCreate').text(`Tambah Sesi Rekap - ${activeNama}`);
        syncModalHeaderInfo();

        tbody.empty();
        addRow(); // Mulai dengan 1 baris
        rekapModal.modal('show');
    });

    // Update row hidden instansi values when select2 changes in new_instansi mode
    $(document).on('change', '#selectNewInstansi', function () {
        const chosen = $(this).val() || '';
        tbody.find('.input-row-instansi').val(chosen);
    });

    // Button Tambah Baris
    addRowBtn.on('click', function () {
        addRow();
    });

    // Button Hapus Baris
    tbody.on('click', '.btn-delete-row', function () {
        $(this).closest('tr').remove();
        if (tbody.find('tr').length === 0) {
            addRow();
        }
    });

    // Submit Multi-Row Form
    $(document).on('click', '.btn-submit-form', function () {
        $('#form-usulan').submit();
    });

    $('#form-usulan').on('submit', function (e) {
        e.preventDefault();

        let chosenInstansiId = '';
        if (window.CatDetailState && window.CatDetailState.rekapModalMode === 'active_instansi') {
            chosenInstansiId = window.CatDetailState.activeInstansi.id || '';
        } else {
            chosenInstansiId = $('#selectNewInstansi').val() || '';
        }

        if (!chosenInstansiId) {
            if (typeof swlErrorHandler === 'function') {
                swlErrorHandler('Silakan pilih instansi terlebih dahulu sebelum menyimpan data rekap.');
            } else {
                alert('Silakan pilih instansi terlebih dahulu.');
            }
            return;
        }

        // Sinkronisasi nilai instansi ke semua input hidden
        tbody.find('.input-row-instansi').val(chosenInstansiId);

        const rowCount = tbody.find('tr').length;
        if (rowCount === 0) {
            if (typeof swlErrorHandler === 'function') {
                swlErrorHandler('Minimal harus ada 1 baris data rekap sesi.');
            }
            return;
        }

        // Validasi kolom tanggal dan sesi
        let isValid = true;
        tbody.find('tr').each(function (idx) {
            const tgl = $(this).find('input[name="tanggal[]"]').val();
            const sesi = $(this).find('input[name="sesi[]"]').val();
            if (!tgl || !sesi) {
                isValid = false;
                if (typeof swlErrorHandler === 'function') {
                    swlErrorHandler(`Baris ke-${idx + 1} belum lengkap. Tanggal dan sesi wajib diisi.`);
                }
                return false;
            }
        });

        if (!isValid) return;

        rekapModal.modal('hide');
        if (typeof swlwaitProsessing === 'function') swlwaitProsessing();

        $.ajax({
            url: AppConfig.initGlobal + 'store/save-data-tilok-rekap',
            method: 'POST',
            data: $(this).serialize(),
            dataType: 'json',
            success: function (response) {
                if (response && response.status === 'success') {
                    tbody.empty();
                    $('#form-usulan')[0].reset();

                    // Refresh Views
                    if (window.loadInstansiList) {
                        window.loadInstansiList();
                    }
                    if ($('#dataTable').length && $.fn.DataTable.isDataTable('#dataTable')) {
                        $('#dataTable').DataTable().ajax.reload();
                    }
                    if (window.loadSummaryDetailTilok) {
                        window.loadSummaryDetailTilok();
                    }

                    if (typeof swlSuccess === 'function') {
                        swlSuccess('Data rekap sesi berhasil disimpan.');
                    }
                } else {
                    const msg = response && response.message ? response.message : 'Gagal menyimpan data rekap.';
                    if (typeof swlErrorHandler === 'function') {
                        swlErrorHandler(msg);
                    }
                }
            },
            error: function () {
                if (typeof swlErrorHandler === 'function') {
                    swlErrorHandler('Terjadi kesalahan pada server saat menyimpan data.');
                }
            }
        });
    });
});
