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
    $('#DataModal, #DataModalDetail, #ModalTambahInstansi').on('hidden.bs.modal', function (e) {
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
            const today = new Date().toISOString().split('T')[0];
            refTanggal = today;
        }

        let instansiIdValue = '';
        if (window.CatDetailState && window.CatDetailState.rekapModalMode === 'active_instansi') {
            instansiIdValue = (window.CatDetailState.activeInstansi && window.CatDetailState.activeInstansi.id) || '';
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
                    <input type="number" name="hadir[]" class="form-control form-control-sm text-center row-hadir" placeholder="0" min="0">
                </td>
                <td>
                    <input type="number" name="tidak_hadir[]" class="form-control form-control-sm text-center row-tidak-hadir" placeholder="0" min="0">
                </td>
                <td>
                    <input type="number" name="reschedule[]" class="form-control form-control-sm text-center" placeholder="0" min="0">
                </td>
                <td class="text-center align-middle">
                    <span class="badge bg-light text-dark border px-2 py-1 row-total-peserta">0</span>
                </td>
                <td>
                    <input type="number" name="memenuhi[]" class="form-control form-control-sm text-center" placeholder="0" min="0">
                </td>
                <td>
                    <input type="number" name="tidak_memenuhi[]" class="form-control form-control-sm text-center" placeholder="0" min="0">
                </td>
                <td class="text-center">
                    <div class="d-inline-flex gap-1">
                        <button type="button" class="btn btn-sm btn-outline-primary btn-duplicate-row" title="Duplikasi Baris">
                            <i class="bi bi-copy"></i>
                        </button>
                        <button type="button" class="btn btn-sm btn-outline-danger btn-delete-row" title="Hapus Baris">
                            <i class="bi bi-trash"></i>
                        </button>
                    </div>
                </td>
            </tr>
        `);

        tbody.append(newRow);
    }

    // Auto-calculate Total Peserta realtime on row input
    $(document).on('input', '#usulanTableBody .row-hadir, #usulanTableBody .row-tidak-hadir', function () {
        const row = $(this).closest('tr');
        const hadir = parseInt(row.find('.row-hadir').val()) || 0;
        const tidakHadir = parseInt(row.find('.row-tidak-hadir').val()) || 0;
        const total = hadir + tidakHadir;
        row.find('.row-total-peserta').text(total);
    });

    // Duplicate Row Event
    $(document).on('click', '.btn-duplicate-row', function () {
        const row = $(this).closest('tr');
        const tanggal = row.find('input[name="tanggal[]"]').val() || '';
        const currentSesi = parseInt(row.find('input[name="sesi[]"]').val()) || 1;
        const nextSesi = currentSesi + 1;
        const nilaiMin = row.find('input[name="nilai_min[]"]').val() || '';
        const nilaiMax = row.find('input[name="nilai_max[]"]').val() || '';
        const hadir = row.find('input[name="hadir[]"]').val() || '';
        const tidakHadir = row.find('input[name="tidak_hadir[]"]').val() || '';
        const reschedule = row.find('input[name="reschedule[]"]').val() || '';
        const memenuhi = row.find('input[name="memenuhi[]"]').val() || '';
        const tidakMemenuhi = row.find('input[name="tidak_memenuhi[]"]').val() || '';
        const instansiVal = row.find('.input-row-instansi').val() || '';

        const totalPeserta = (parseInt(hadir) || 0) + (parseInt(tidakHadir) || 0);

        const dupRow = $(`
            <tr>
                <td>
                    <input type="hidden" name="instansi[]" class="input-row-instansi" value="${instansiVal}">
                    <input type="date" name="tanggal[]" class="form-control form-control-sm" value="${tanggal}" required>
                </td>
                <td>
                    <input type="number" name="sesi[]" class="form-control form-control-sm text-center" value="${nextSesi}" min="1" placeholder="1" required>
                </td>
                <td>
                    <input type="number" name="nilai_min[]" class="form-control form-control-sm" value="${nilaiMin}" placeholder="0" min="0">
                </td>
                <td>
                    <input type="number" name="nilai_max[]" class="form-control form-control-sm" value="${nilaiMax}" placeholder="0" min="0">
                </td>
                <td>
                    <input type="number" name="hadir[]" class="form-control form-control-sm text-center row-hadir" value="${hadir}" placeholder="0" min="0">
                </td>
                <td>
                    <input type="number" name="tidak_hadir[]" class="form-control form-control-sm text-center row-tidak-hadir" value="${tidakHadir}" placeholder="0" min="0">
                </td>
                <td>
                    <input type="number" name="reschedule[]" class="form-control form-control-sm text-center" value="${reschedule}" placeholder="0" min="0">
                </td>
                <td class="text-center align-middle">
                    <span class="badge bg-light text-dark border px-2 py-1 row-total-peserta">${totalPeserta}</span>
                </td>
                <td>
                    <input type="number" name="memenuhi[]" class="form-control form-control-sm text-center" value="${memenuhi}" placeholder="0" min="0">
                </td>
                <td>
                    <input type="number" name="tidak_memenuhi[]" class="form-control form-control-sm text-center" value="${tidakMemenuhi}" placeholder="0" min="0">
                </td>
                <td class="text-center">
                    <div class="d-inline-flex gap-1">
                        <button type="button" class="btn btn-sm btn-outline-primary btn-duplicate-row" title="Duplikasi Baris">
                            <i class="bi bi-copy"></i>
                        </button>
                        <button type="button" class="btn btn-sm btn-outline-danger btn-delete-row" title="Hapus Baris">
                            <i class="bi bi-trash"></i>
                        </button>
                    </div>
                </td>
            </tr>
        `);

        row.after(dupRow);
    });

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
        $('#ModalTambahInstansi').modal('show');
    });

    // Trigger Open Modal - Mode: Tambah Event Baru (Level 2)
    $('#btnOpenTambahEvent').on('click', function () {
        $('#ModalTambahEvent').modal('show');
    });

    // Trigger Open Modal - Mode: Tambah Sesi Rekap (Level 3)
    $('#btnOpenTambahSesi').on('click', function () {
        if (!window.CatDetailState) return;
        window.CatDetailState.rekapModalMode = 'active_instansi';

        const activeNama = (window.CatDetailState.activeInstansi && window.CatDetailState.activeInstansi.nama) || 'Instansi Terpilih';
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

    // Submit Multi-Row Form Sesi Rekap
    $(document).on('click', '.btn-submit-form', function () {
        $('#form-usulan').submit();
    });

    $('#form-usulan').on('submit', function (e) {
        e.preventDefault();

        let chosenInstansiId = '';
        if (window.CatDetailState && window.CatDetailState.rekapModalMode === 'active_instansi') {
            chosenInstansiId = (window.CatDetailState.activeInstansi && window.CatDetailState.activeInstansi.id) || '';
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

        tbody.find('.input-row-instansi').val(chosenInstansiId);

        const rowCount = tbody.find('tr').length;
        if (rowCount === 0) {
            if (typeof swlErrorHandler === 'function') {
                swlErrorHandler('Minimal harus ada 1 baris data rekap sesi.');
            }
            return;
        }

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

        let formData = $(this).serialize();
        if (window.CatDetailState && window.CatDetailState.rekapModalMode === 'active_instansi') {
            if (window.CatDetailState.activeEvent) {
                formData += '&seleksi_id=' + encodeURIComponent(window.CatDetailState.activeEvent.id);
            }
            if (window.CatDetailState.tilokMeta && window.CatDetailState.tilokMeta.jenis_tes_id) {
                formData += '&jenis_tes_id=' + encodeURIComponent(window.CatDetailState.tilokMeta.jenis_tes_id);
            }
        }

        $.ajax({
            url: AppConfig.initGlobal + 'store/save-data-tilok-rekap',
            method: 'POST',
            data: formData,
            dataType: 'json',
            success: function (response) {
                if (response && response.status === 'success') {
                    tbody.empty();
                    $('#form-usulan')[0].reset();

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

    // Handle Submit Instansi Baru Modal (#ModalTambahInstansi)
    $('#btnSubmitTambahInstansi').on('click', function () {
        const instansiId = $('#selectNewInstansi').val();
        const selectData = $('#selectNewInstansi').select2('data');
        if (!instansiId || !selectData || selectData.length === 0) {
            if (typeof swlErrorHandler === 'function') swlErrorHandler('Silakan cari dan pilih instansi terlebih dahulu.');
            return;
        }

        const chosenNama = selectData[0].text || instansiId;
        const tilokKey = (window.CatDetailState && (window.CatDetailState.tilokMeta?.id || window.CatDetailState.paramKey)) 
            || (typeof TILOK_UID !== 'undefined' ? TILOK_UID : '');

        if (!tilokKey) {
            if (typeof swlErrorHandler === 'function') swlErrorHandler('Data titik lokasi tidak valid.');
            return;
        }

        if (typeof swlwaitProsessing === 'function') swlwaitProsessing();

        $.ajax({
            url: AppConfig.initGlobal + 'store/save-data-tilok-instansi',
            type: 'POST',
            data: {
                tilok_id: tilokKey,
                instansi_id: instansiId
            },
            dataType: 'json',
            success: function (res) {
                if (res && (res.status === 'success' || res.status === true)) {
                    $('#ModalTambahInstansi').modal('hide');

                    if (typeof swlSuccess === 'function') {
                        swlSuccess('Instansi berhasil ditambahkan ke titik lokasi.');
                    }

                    // Reload list instansi di Level 1
                    if (window.loadInstansiList) {
                        window.loadInstansiList();
                    }

                    // Buka langsung tampilan Level 2 (Daftar Event) untuk instansi yang dipilih
                    if (window.openInstansiEventView) {
                        window.openInstansiEventView(instansiId, chosenNama, '');
                    }
                } else {
                    const msg = (res && res.message) ? res.message : 'Gagal menyimpan data instansi.';
                    if (typeof swlErrorHandler === 'function') swlErrorHandler(msg);
                }
            },
            error: function () {
                if (typeof swlErrorHandler === 'function') swlErrorHandler('Terjadi kesalahan pada server saat menambahkan instansi.');
            }
        });
    });

    // State Modal Event (Tambah vs Edit)
    let currentEventModalMode = 'create'; // 'create' | 'edit'
    let editingOldSeleksiId = null;

    // Helper: Load & Populate Options pada Modal Pilih Event (#ModalTambahEvent)
    function loadEventOptions(selectedValue, callback) {
        const selectEvent = $('#selectNewEvent');
        selectEvent.html('<option value="">Memuat daftar event...</option>');

        const jenisTesId = (window.CatDetailState && window.CatDetailState.tilokMeta) 
            ? (window.CatDetailState.tilokMeta.jenis_tes_id || '') 
            : '';

        $.ajax({
            url: AppConfig.initGlobal + 'fetch/seleksi-options-cat',
            type: 'POST',
            data: {
                search: '',
                jenis_tes_id: jenisTesId
            },
            dataType: 'json',
            success: function (res) {
                const items = Array.isArray(res?.data) ? res.data : (Array.isArray(res) ? res : []);
                let optionsHtml = '<option value=""></option>';
                items.forEach(function (item) {
                    const label = item.nama_seleksi + (item.periode ? ' (' + item.periode + ')' : '');
                    optionsHtml += `<option value="${item.id}" data-uid="${item.uid || ''}" data-jenis="${item.jenis_tes_kode || ''}">${escapeHtml(label)}</option>`;
                });
                selectEvent.html(optionsHtml);

                if (selectEvent.hasClass('select2-hidden-accessible')) {
                    selectEvent.select2('destroy');
                }

                selectEvent.select2({
                    theme: 'bootstrap-5',
                    dropdownParent: $('#ModalTambahEvent'),
                    placeholder: 'Cari dan pilih event seleksi...',
                    allowClear: true,
                    width: '100%'
                });

                if (selectedValue) {
                    selectEvent.val(String(selectedValue)).trigger('change');
                }

                if (typeof callback === 'function') {
                    callback();
                }
            },
            error: function () {
                selectEvent.html('<option value="">Gagal memuat event seleksi</option>');
            }
        });
    }

    // Trigger Open Modal - Mode: Tambah Event Baru (Level 2)
    $('#btnOpenTambahEvent').on('click', function () {
        currentEventModalMode = 'create';
        editingOldSeleksiId = null;
        $('#ModalTambahEventLabel').text('Pilih Event Seleksi');
        $('#btnSubmitTambahEvent').text('Pilih & Lanjutkan');
        $('#ModalTambahEvent').modal('show');
    });

    // Trigger Open Modal - Mode: Edit Event (Level 2)
    $(document).on('click', '.btn-edit-event', function (e) {
        e.stopPropagation();
        e.preventDefault();

        const seleksiId = $(this).data('seleksi-id');
        currentEventModalMode = 'edit';
        editingOldSeleksiId = seleksiId;

        $('#ModalTambahEventLabel').text('Ubah Event Seleksi');
        $('#btnSubmitTambahEvent').text('Simpan Perubahan');

        $('#ModalTambahEvent').modal('show');
    });

    // Inisialisasi Options untuk #ModalTambahEvent saat modal dibuka
    $('#ModalTambahEvent').on('shown.bs.modal', function () {
        loadEventOptions(editingOldSeleksiId);
    });

    // Reset Select2 saat #ModalTambahEvent ditutup
    $('#ModalTambahEvent').on('hidden.bs.modal', function () {
        const selectEvent = $('#selectNewEvent');
        if (selectEvent.hasClass('select2-hidden-accessible')) {
            selectEvent.select2('destroy');
        }
        selectEvent.val(null).trigger('change');
        currentEventModalMode = 'create';
        editingOldSeleksiId = null;
        $('#ModalTambahEventLabel').text('Pilih Event Seleksi');
        $('#btnSubmitTambahEvent').text('Pilih & Lanjutkan');
    });

    // Handle Submit Event Modal (#ModalTambahEvent)
    $('#btnSubmitTambahEvent').on('click', function () {
        const seleksiId = $('#selectNewEvent').val();
        const selectedOpt = $('#selectNewEvent').find('option:selected');
        if (!seleksiId || !selectedOpt.length || !seleksiId.trim()) {
            if (typeof swlErrorHandler === 'function') swlErrorHandler('Silakan cari dan pilih event seleksi terlebih dahulu.');
            return;
        }

        // =========================================================================
        // MODE EDIT: Update Event Seleksi pada Data Rekap Instansi
        // =========================================================================
        if (currentEventModalMode === 'edit') {
            if (String(seleksiId) === String(editingOldSeleksiId)) {
                $('#ModalTambahEvent').modal('hide');
                return;
            }

            // Cek apakah seleksi baru sudah ada di daftar event instansi ini
            const existingEvents = (window.CatDetailState && Array.isArray(window.CatDetailState.allEvents)) 
                ? window.CatDetailState.allEvents 
                : [];

            const isDuplicate = existingEvents.some(function (ev) {
                return (String(ev.seleksi_id) === String(seleksiId) || String(ev.id) === String(seleksiId)) 
                    && String(ev.seleksi_id) !== String(editingOldSeleksiId);
            });

            if (isDuplicate) {
                const namaEvent = selectedOpt.text().trim();
                if (typeof Swal !== 'undefined' && typeof Swal.fire === 'function') {
                    Swal.fire({
                        icon: 'warning',
                        title: 'Event Sudah Terdaftar',
                        text: `Event seleksi "${namaEvent}" sudah ada dalam daftar instansi ini. Silakan pilih event lain.`,
                        confirmButtonText: 'Mengerti',
                        confirmButtonColor: '#0d6efd'
                    });
                } else if (typeof swlErrorHandler === 'function') {
                    swlErrorHandler(`Event seleksi "${namaEvent}" sudah terdaftar pada instansi ini.`);
                }
                return;
            }

            // Kirim request update ke server
            $('#ModalTambahEvent').modal('hide');
            if (typeof swlwaitProsessing === 'function') swlwaitProsessing();

            $.ajax({
                url: AppConfig.initGlobal + 'store/update-event-cat',
                type: 'POST',
                dataType: 'json',
                data: {
                    tilok_uid: (window.CatDetailState && window.CatDetailState.paramKey) ? window.CatDetailState.paramKey : '',
                    instansi_id: (window.CatDetailState && window.CatDetailState.activeInstansi) ? window.CatDetailState.activeInstansi.id : '',
                    old_seleksi_id: editingOldSeleksiId,
                    new_seleksi_id: seleksiId
                },
                success: function (res) {
                    if (res && res.status === 'success') {
                        if (typeof swlSuccess === 'function') {
                            swlSuccess('Event seleksi berhasil diperbarui.');
                        }
                        if (typeof window.loadEventList === 'function') {
                            window.loadEventList();
                        }
                        if (typeof window.loadInstansiList === 'function') {
                            window.loadInstansiList();
                        }
                        if (typeof window.loadSummaryDetailTilok === 'function') {
                            window.loadSummaryDetailTilok();
                        }
                    } else {
                        if (typeof swlErrorHandler === 'function') {
                            swlErrorHandler(res?.message || 'Gagal mengubah event seleksi.');
                        }
                    }
                },
                error: function (xhr) {
                    const message = xhr.responseJSON && xhr.responseJSON.message 
                        ? xhr.responseJSON.message 
                        : 'Terjadi kesalahan saat memperbarui event seleksi.';
                    if (typeof swlErrorHandler === 'function') {
                        swlErrorHandler(message);
                    }
                }
            });
            return;
        }

        // =========================================================================
        // MODE TAMBAH (CREATE): Masuk ke level 3 rekap sesi
        // =========================================================================
        const existingEvents = (window.CatDetailState && Array.isArray(window.CatDetailState.allEvents)) 
            ? window.CatDetailState.allEvents 
            : [];

        const isDuplicate = existingEvents.some(function (ev) {
            return String(ev.seleksi_id) === String(seleksiId) || String(ev.id) === String(seleksiId);
        });

        if (isDuplicate) {
            const namaEvent = selectedOpt.text().trim();
            if (typeof Swal !== 'undefined' && typeof Swal.fire === 'function') {
                Swal.fire({
                    icon: 'warning',
                    title: 'Event Sudah Terdaftar',
                    text: `Event seleksi "${namaEvent}" sudah ada dalam daftar instansi ini. Silakan pilih event lain atau buka event tersebut dari daftar.`,
                    confirmButtonText: 'Mengerti',
                    confirmButtonColor: '#0d6efd'
                });
            } else if (typeof swlErrorHandler === 'function') {
                swlErrorHandler(`Event seleksi "${namaEvent}" sudah terdaftar pada instansi ini.`);
            }
            return;
        }

        const eventObj = {
            id: seleksiId,
            uid: selectedOpt.data('uid') || '',
            nama_seleksi: selectedOpt.text().trim(),
            jenis_tes: selectedOpt.data('jenis') || '-',
            total_sesi: 0
        };

        $('#ModalTambahEvent').modal('hide');

        if (window.openInstansiRekapView) {
            window.openInstansiRekapView(eventObj.id, eventObj);
        }
    });
});
