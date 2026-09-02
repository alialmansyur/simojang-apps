/**
 * PNBP Document Detail Editor Controller
 */

function escapeHtml(value) {
    return String(value || '')
        .replace(/&/g, '&amp;')
        .replace(/</g, '&lt;')
        .replace(/>/g, '&gt;')
        .replace(/"/g, '&quot;')
        .replace(/'/g, '&#039;');
}

function initPegawaiSelect2() {
    const el = $('#pegawaiLookupSelect');
    if (!el.length) return;

    if (el.hasClass('select2-hidden-accessible')) {
        el.select2('destroy');
    }

    el.select2({
        dropdownParent: $('#pnbpPersonelModal'),
        placeholder: 'Ketik nama atau NIP pegawai...',
        allowClear: true,
        minimumInputLength: 2,
        ajax: {
            url: AppConfig.initGlobal + 'fetch/pnbp-options-pegawai',
            type: 'POST',
            dataType: 'json',
            delay: 250,
            data: function (params) {
                return {
                    q: params.term
                };
            },
            processResults: function (data) {
                return {
                    results: (data.items || []).map(function(item) {
                        return {
                            id: item.nip || item.nama,
                            text: item.nama + ' (' + (item.nip || '-') + ')',
                            raw: item
                        };
                    })
                };
            },
            cache: true
        }
    });

    el.off('select2:select').on('select2:select', function(e) {
        const item = e.params.data.raw;
        if (item) {
            $('#personel_nama').val(item.nama || '');
            $('#personel_nip').val(item.nip || '');
            $('#personel_pangkat_gol').val(item.pangkat_gol || '');
            $('#personel_jabatan').val(item.jabatan || '');
            $('#personel_no_rekening').val(item.no_rekening || '');
        }
    });
}

$(document).ready(function() {

    // PDF Actions
    $('#btnGeneratePdfDetail').on('click', function() {
        if (typeof generateAndPreviewPdf === 'function') {
            generateAndPreviewPdf(DOC_UID, function() {
                setTimeout(() => window.location.reload(), 1500);
            });
        }
    });

    $('#btnPreviewPdfDetail').on('click', function() {
        if (typeof openPdfPreviewModal === 'function') {
            openPdfPreviewModal(DOC_UID);
        }
    });

    // Copy Link TTD
    $('.btn-copy-link').on('click', function() {
        const url = $(this).data('url');
        if (url) {
            navigator.clipboard.writeText(url).then(function() {
                if (typeof swlSuccess === 'function') {
                    swlSuccess('Tautan tanda tangan berhasil disalin.');
                } else {
                    alert('Tautan tanda tangan berhasil disalin.');
                }
            });
        }
    });

    // =========================================================================
    // PERSONEL TIM CRUD
    // =========================================================================
    $('#btnAddPersonel').on('click', function() {
        $('#pnbpPersonelForm')[0].reset();
        $('#personel_id').val('');
        $('#personelModalTitle').text('Tambah Personel Tim');
        initPegawaiSelect2();
        $('#pnbpPersonelModal').modal('show');
    });

    $('.btn-edit-personel').on('click', function() {
        const data = $(this).data('json');
        if (!data) return;

        $('#personel_id').val(data.id);
        $('#personel_nama').val(data.nama);
        $('#personel_nip').val(data.nip || '');
        $('#personel_pangkat_gol').val(data.pangkat_gol || '');
        $('#personel_jabatan').val(data.jabatan || '');
        $('#personel_peran').val(data.peran || '');
        $('#personel_jumlah_hari').val(data.jumlah_hari || 1);
        $('#personel_uang_harian').val(data.uang_harian || 0);
        $('#personel_transport').val(data.transport || 0);
        $('#personel_no_rekening').val(data.no_rekening || '');

        $('#personelModalTitle').text('Ubah Data Personel');
        initPegawaiSelect2();
        $('#pnbpPersonelModal').modal('show');
    });

    $('#btnSavePersonel').on('click', function() {
        $('#pnbpPersonelForm').submit();
    });

    $('#pnbpPersonelForm').on('submit', function(e) {
        e.preventDefault();
        $('#pnbpPersonelModal').modal('hide');

        if (typeof swlwaitProsessing === 'function') swlwaitProsessing('Menyimpan personel...');

        $.ajax({
            url: AppConfig.initGlobal + 'store/save-pnbp-personel',
            type: 'POST',
            data: $(this).serialize(),
            dataType: 'json',
            success: function(res) {
                if (res && res.status === 'success') {
                    window.location.reload();
                } else {
                    if (typeof swlErrorHandler === 'function') swlErrorHandler(res && res.message ? res.message : 'Gagal menyimpan.');
                }
            },
            error: function(xhr) {
                const msg = xhr.responseJSON && xhr.responseJSON.message ? xhr.responseJSON.message : 'Gagal menyimpan personel.';
                if (typeof swlErrorHandler === 'function') swlErrorHandler(msg);
            }
        });
    });

    // =========================================================================
    // NOMINATIF: HEADER & MULTIPLE INSTANSI EDITOR
    // =========================================================================
    function getSelectedInstansiNames() {
        const selectedOptions = $('#headerInstansiSelect option:selected');
        const names = [];
        selectedOptions.each(function() {
            const nama = $(this).data('nama') || $(this).text();
            if (nama) names.push(nama.trim());
        });
        return names;
    }

    function generateKalimatBakuNominatif() {
        const names = getSelectedInstansiNames();
        const instansiText = names.length > 0 ? names.join(', ') : 'Pemerintah Daerah Terkait';
        const tanggalText = $('#headerTanggalKegiatan').val().trim() || '........................';

        return 'Honorarium Tim Panitia dalam rangka Fasilitasi Seleksi Pengembangan Karier dengan metode CAT BKN di Lingkungan Instansi ' + instansiText + ' di Kanreg III BKN, pada tanggal ' + tanggalText + '.';
    }

    if ($('#headerInstansiSelect').length) {
        $('#headerInstansiSelect').select2({
            placeholder: 'Pilih satu atau beberapa instansi...',
            allowClear: true,
            closeOnSelect: false
        });

        $('#headerInstansiSelect').on('change', function() {
            const currentVal = $('#headerKeteranganText').val().trim();
            // Jika kosong atau merupakan kalimat baku sebelumnya, otomatis perbarui
            $('#headerKeteranganText').val(generateKalimatBakuNominatif());
        });

        $('#headerTanggalKegiatan').on('input change', function() {
            $('#headerKeteranganText').val(generateKalimatBakuNominatif());
        });

        $('#btnResetKalimatBaku').on('click', function() {
            $('#headerKeteranganText').val(generateKalimatBakuNominatif());
            if (typeof swlSuccess === 'function') {
                swlSuccess('Redaksi berhasil direset ke kalimat baku.');
            }
        });

        $('#btnSaveHeader').on('click', function() {
            const instansiIds = $('#headerInstansiSelect').val() || [];
            const instansiNames = getSelectedInstansiNames();
            const tanggalKegiatan = $('#headerTanggalKegiatan').val().trim();
            const headerKeterangan = $('#headerKeteranganText').val().trim();

            if (typeof swlwaitProsessing === 'function') swlwaitProsessing('Menyimpan redaksi header...');

            $.ajax({
                url: AppConfig.initGlobal + 'store/save-pnbp-header',
                type: 'POST',
                data: {
                    document_uid: DOC_UID,
                    instansi_ids: instansiIds,
                    instansi_names: instansiNames,
                    tanggal_kegiatan: tanggalKegiatan,
                    header_keterangan: headerKeterangan
                },
                dataType: 'json',
                success: function(res) {
                    if (res && res.status === 'success') {
                        if (typeof swlSuccess === 'function') {
                            swlSuccess('Keterangan header berhasil disimpan.');
                        } else {
                            alert('Keterangan header berhasil disimpan.');
                        }
                    } else {
                        if (typeof swlErrorHandler === 'function') swlErrorHandler(res && res.message ? res.message : 'Gagal menyimpan.');
                    }
                },
                error: function(xhr) {
                    const msg = xhr.responseJSON && xhr.responseJSON.message ? xhr.responseJSON.message : 'Gagal menyimpan header.';
                    if (typeof swlErrorHandler === 'function') swlErrorHandler(msg);
                }
            });
        });
    }

    // =========================================================================
    // NOMINATIF: TABLE APPEND & AUTO TAX CALCULATION
    // =========================================================================
    function formatNumberId(num) {
        return new Intl.NumberFormat('id-ID').format(num || 0);
    }

    function calcAppendTax() {
        const jumlah = parseFloat($('#app_jumlah').val()) || 0;
        const pajakPersen = parseFloat($('#app_pajak_persen').val()) || 0;
        const pajakNominal = Math.round(jumlah * (pajakPersen / 100));
        const jumlahDiterima = jumlah - pajakNominal;

        $('#app_pajak_nominal_view').val(formatNumberId(pajakNominal));
        $('#app_jumlah_diterima_view').val(formatNumberId(jumlahDiterima));
    }

    function calcEditTax() {
        const jumlah = parseFloat($('#nom_edit_jumlah').val()) || 0;
        const pajakPersen = parseFloat($('#nom_edit_pajak_persen').val()) || 0;
        const pajakNominal = Math.round(jumlah * (pajakPersen / 100));
        const jumlahDiterima = jumlah - pajakNominal;

        $('#nom_edit_pajak_nominal_view').val(formatNumberId(pajakNominal));
        $('#nom_edit_jumlah_diterima_view').val(formatNumberId(jumlahDiterima));
    }

    $('#app_jumlah, #app_pajak_persen').on('input change keyup', function() {
        calcAppendTax();
    });

    $('#nom_edit_jumlah, #nom_edit_pajak_persen').on('input change keyup', function() {
        calcEditTax();
    });

    // Inisialisasi Select2 Pegawai Nominatif
    if ($('#selectPegawaiNominatif').length) {
        $('#selectPegawaiNominatif').select2({
            placeholder: '-- Ketik Nama / NIP Pegawai --',
            allowClear: true,
            minimumInputLength: 2,
            ajax: {
                url: AppConfig.initGlobal + 'fetch/pnbp-options-pegawai',
                type: 'POST',
                dataType: 'json',
                delay: 250,
                data: function (params) {
                    return { q: params.term };
                },
                processResults: function (data) {
                    return {
                        results: (data.items || []).map(function(item) {
                            return {
                                id: item.id || item.nip,
                                text: item.nama + ' (' + (item.nip || '-') + ') - ' + (item.gol || item.status_pegawai || 'PNS'),
                                raw: item
                            };
                        })
                    };
                },
                cache: true
            }
        });

        $('#selectPegawaiNominatif').on('select2:select', function(e) {
            const item = e.params.data.raw;
            if (!item) return;

            $('#app_nama').val(item.nama || '');
            $('#app_nip').val(item.nip || '');
            
            // Format Golongan sederhana (contoh: Penata Muda (III/a) -> III)
            let golRaw = item.gol || '';
            let golFormatted = golRaw;
            if (golRaw.indexOf('/') !== -1) {
                golFormatted = golRaw.split('/')[0].trim();
            } else if (golRaw.indexOf('(') !== -1) {
                const match = golRaw.match(/\((.*?)\)/);
                if (match) golFormatted = match[1].split('/')[0].trim();
            }
            $('#app_gol').val(golFormatted || 'III');
            
            $('#app_nik').val(item.nik || '');
            $('#app_bank_nama').val(item.bank_nama || 'BRI');
            $('#app_no_rek').val(item.no_rekening || '');
            $('#app_status_pegawai').val(item.status_pegawai || 'PNS');

            // Set Pajak Default Berdasarkan Status Pegawai
            const statusPeg = String(item.status_pegawai || 'PNS').toUpperCase();
            if (statusPeg.includes('PPPK') || statusPeg.includes('NON')) {
                $('#app_pajak_persen').val('0');
            } else {
                $('#app_pajak_persen').val('5');
            }

            calcAppendTax();
        });
    }

    // Submit Append Baris Pegawai Nominatif
    $('#btnAppendNominatif').on('click', function() {
        const nama = $('#app_nama').val().trim();
        const jumlah = parseFloat($('#app_jumlah').val()) || 0;

        if (!nama) {
            if (typeof swlErrorHandler === 'function') {
                swlErrorHandler('Pilih atau isi nama pegawai terlebih dahulu.');
            } else {
                alert('Pilih atau isi nama pegawai terlebih dahulu.');
            }
            return;
        }

        if (jumlah <= 0) {
            if (typeof swlErrorHandler === 'function') {
                swlErrorHandler('Jumlah honorarium harus lebih dari 0.');
            } else {
                alert('Jumlah honorarium harus lebih dari 0.');
            }
            return;
        }

        if (typeof swlwaitProsessing === 'function') swlwaitProsessing('Menambahkan data nominatif...');

        const formData = $('#appendNominatifForm').serialize();

        $.ajax({
            url: AppConfig.initGlobal + 'store/save-pnbp-personel',
            type: 'POST',
            data: formData,
            dataType: 'json',
            success: function(res) {
                if (res && res.status === 'success') {
                    // Reset Append form tapi pertahankan default amount
                    $('#selectPegawaiNominatif').val(null).trigger('change');
                    $('#app_nama').val('');
                    $('#app_nip').val('');
                    $('#app_gol').val('');
                    $('#app_nik').val('');
                    $('#app_no_rek').val('');
                    $('#app_jabatan').val('Anggota');
                    calcAppendTax();

                    // Reload page to refresh all tables & counters
                    window.location.reload();
                } else {
                    if (typeof swlErrorHandler === 'function') swlErrorHandler(res && res.message ? res.message : 'Gagal menambahkan pegawai.');
                }
            },
            error: function(xhr) {
                const msg = xhr.responseJSON && xhr.responseJSON.message ? xhr.responseJSON.message : 'Gagal menambahkan pegawai.';
                if (typeof swlErrorHandler === 'function') swlErrorHandler(msg);
            }
        });
    });

    // Edit Modal Trigger untuk Nominatif
    $(document).on('click', '.btn-edit-personel-nom', function() {
        const p = $(this).data('json');
        if (!p) return;

        $('#nom_edit_id').val(p.id);
        $('#nom_edit_nama').val(p.nama);
        $('#nom_edit_nip').val(p.nip || '');
        $('#nom_edit_gol').val(p.pangkat_gol || '');
        $('#nom_edit_nik').val(p.nik || '');
        $('#nom_edit_bank_nama').val(p.bank_nama || 'BRI');
        $('#nom_edit_no_rekening').val(p.no_rekening || '');
        $('#nom_edit_jabatan').val(p.jabatan || p.peran || 'Anggota');
        $('#nom_edit_status_pegawai').val(p.status_pegawai || 'PNS');

        const jumlahVal = parseFloat(p.jumlah > 0 ? p.jumlah : p.total_biaya) || 0;
        $('#nom_edit_jumlah').val(jumlahVal);
        
        const pajakPersenVal = parseFloat(p.pajak_persen) || 0;
        $('#nom_edit_pajak_persen').val(pajakPersenVal);

        calcEditTax();
        $('#pnbpNominatifEditModal').modal('show');
    });

    $('#btnSaveNominatifEdit').on('click', function() {
        const nama = $('#nom_edit_nama').val().trim();
        if (!nama) {
            if (typeof swlErrorHandler === 'function') swlErrorHandler('Nama pegawai wajib diisi.');
            return;
        }

        $('#pnbpNominatifEditModal').modal('hide');
        if (typeof swlwaitProsessing === 'function') swlwaitProsessing('Menyimpan perubahan pegawai...');

        $.ajax({
            url: AppConfig.initGlobal + 'store/save-pnbp-personel',
            type: 'POST',
            data: $('#pnbpNominatifEditForm').serialize(),
            dataType: 'json',
            success: function(res) {
                if (res && res.status === 'success') {
                    window.location.reload();
                } else {
                    if (typeof swlErrorHandler === 'function') swlErrorHandler(res && res.message ? res.message : 'Gagal menyimpan.');
                }
            },
            error: function(xhr) {
                const msg = xhr.responseJSON && xhr.responseJSON.message ? xhr.responseJSON.message : 'Gagal menyimpan pegawai.';
                if (typeof swlErrorHandler === 'function') swlErrorHandler(msg);
            }
        });
    });

    // Delete Personel Handler
    $(document).on('click', '.btn-delete-personel', function() {
        const id = $(this).data('id');
        Swal.fire({
            text: 'Hapus pegawai ini dari daftar nominatif?',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#ef4444',
            confirmButtonText: 'Ya, Hapus',
            cancelButtonText: 'Batal'
        }).then((result) => {
            if (!result.isConfirmed) return;

            $.ajax({
                url: AppConfig.initGlobal + 'kill/data-pnbp-personel',
                type: 'POST',
                data: { id: id },
                dataType: 'json',
                success: function(res) {
                    if (res && res.status === 'success') {
                        window.location.reload();
                    } else {
                        if (typeof swlErrorHandler === 'function') swlErrorHandler(res && res.message ? res.message : 'Gagal menghapus.');
                    }
                }
            });
        });
    });

    // =========================================================================
    // ITEM JAMUAN CRUD
    // =========================================================================
    $('#btnAddItem').on('click', function() {
        $('#pnbpItemForm')[0].reset();
        $('#item_id').val('');
        $('#itemModalTitle').text('Tambah Item Jamuan');
        $('#pnbpItemModal').modal('show');
    });

    $('.btn-edit-item').on('click', function() {
        const data = $(this).data('json');
        if (!data) return;

        $('#item_id').val(data.id);
        $('#item_name').val(data.item_name);
        $('#item_spesifikasi').val(data.spesifikasi || '');
        $('#item_quantity').val(data.quantity || 1);
        $('#item_satuan').val(data.satuan || 'Box');
        $('#item_harga_satuan').val(data.harga_satuan || 0);

        $('#itemModalTitle').text('Ubah Item Jamuan');
        $('#pnbpItemModal').modal('show');
    });

    $('#btnSaveItem').on('click', function() {
        $('#pnbpItemForm').submit();
    });

    $('#pnbpItemForm').on('submit', function(e) {
        e.preventDefault();
        $('#pnbpItemModal').modal('hide');

        if (typeof swlwaitProsessing === 'function') swlwaitProsessing('Menyimpan item jamuan...');

        $.ajax({
            url: AppConfig.initGlobal + 'store/save-pnbp-items',
            type: 'POST',
            data: $(this).serialize(),
            dataType: 'json',
            success: function(res) {
                if (res && res.status === 'success') {
                    window.location.reload();
                } else {
                    if (typeof swlErrorHandler === 'function') swlErrorHandler(res && res.message ? res.message : 'Gagal menyimpan.');
                }
            },
            error: function(xhr) {
                const msg = xhr.responseJSON && xhr.responseJSON.message ? xhr.responseJSON.message : 'Gagal menyimpan item jamuan.';
                if (typeof swlErrorHandler === 'function') swlErrorHandler(msg);
            }
        });
    });

    $('.btn-delete-item').on('click', function() {
        const id = $(this).data('id');
        Swal.fire({
            text: 'Hapus item jamuan ini dari rincian belanja?',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#ef4444',
            confirmButtonText: 'Ya, Hapus',
            cancelButtonText: 'Batal'
        }).then((result) => {
            if (!result.isConfirmed) return;

            $.ajax({
                url: AppConfig.initGlobal + 'kill/data-pnbp-item',
                type: 'POST',
                data: { id: id },
                dataType: 'json',
                success: function(res) {
                    if (res && res.status === 'success') {
                        window.location.reload();
                    } else {
                        if (typeof swlErrorHandler === 'function') swlErrorHandler(res && res.message ? res.message : 'Gagal menghapus.');
                    }
                }
            });
        });
    });

    // =========================================================================
    // PARAMETER SIGNER EDIT
    // =========================================================================
    $('.btn-edit-signer-param').on('click', function() {
        const sig = $(this).data('json');
        if (!sig) return;

        $('#sig_id').val(sig.id);
        $('#sig_sign_title').val(sig.sign_title || '');
        $('#sig_nama').val(sig.nama || '');
        $('#sig_nip').val(sig.nip || '');
        $('#sig_pangkat_gol').val(sig.pangkat_gol || '');
        $('#sig_jabatan').val(sig.jabatan || '');

        $('#pnbpSignerModal').modal('show');
    });

    $('#btnSaveSignerParam').on('click', function() {
        $('#pnbpSignerForm').submit();
    });

    $('#pnbpSignerForm').on('submit', function(e) {
        e.preventDefault();
        $('#pnbpSignerModal').modal('hide');

        if (typeof swlwaitProsessing === 'function') swlwaitProsessing('Menyimpan parameter penandatangan...');

        $.ajax({
            url: AppConfig.initGlobal + 'store/save-pnbp-signature-param',
            type: 'POST',
            data: $(this).serialize(),
            dataType: 'json',
            success: function(res) {
                if (res && res.status === 'success') {
                    window.location.reload();
                } else {
                    if (typeof swlErrorHandler === 'function') swlErrorHandler(res && res.message ? res.message : 'Gagal menyimpan.');
                }
            },
            error: function(xhr) {
                const msg = xhr.responseJSON && xhr.responseJSON.message ? xhr.responseJSON.message : 'Gagal menyimpan parameter penandatangan.';
                if (typeof swlErrorHandler === 'function') swlErrorHandler(msg);
            }
        });
    });

    function initPnbpDocModalSelect2() {
        const modal = $('#pnbpDocModal');
        if (!modal.length) return;

        ['#doc_type', '#doc_instansi_id', '#doc_seleksi_id', '#doc_tilok_id'].forEach(function(sel) {
            const el = $(sel);
            if (el.length && !el.data('select2')) {
                el.select2({
                    theme: 'bootstrap-5',
                    dropdownParent: modal,
                    width: '100%',
                    placeholder: el.find('option:first').text() || '-- Pilih --',
                    allowClear: sel !== '#doc_type'
                });
            }
        });
    }

    function loadTilokOptionsForModal(seleksiId, selectedTilokId) {
        const tilokSelect = $('#doc_tilok_id');
        if (!seleksiId) {
            tilokSelect.html('<option value="">-- Tanpa Event / Mandiri --</option>');
            tilokSelect.val('').trigger('change');
            return;
        }

        tilokSelect.html('<option value="">Memuat titik lokasi...</option>');
        tilokSelect.val('').trigger('change');

        $.ajax({
            url: AppConfig.initGlobal + 'fetch/pnbp-options-tilok',
            type: 'POST',
            data: { seleksi_id: seleksiId },
            dataType: 'json',
            success: function(res) {
                tilokSelect.empty();
                tilokSelect.append('<option value="">-- Pilih Titik Lokasi --</option>');
                if (res && res.data && res.data.length) {
                    res.data.forEach(t => {
                        const selected = selectedTilokId && String(selectedTilokId) === String(t.id) ? 'selected' : '';
                        tilokSelect.append(`<option value="${t.id}" ${selected}>${t.nama_tilok}</option>`);
                    });
                } else {
                    tilokSelect.append('<option value="">(Belum ada tilok di event ini)</option>');
                }
                tilokSelect.trigger('change');
            }
        });
    }

    $('#doc_seleksi_id').on('change', function() {
        loadTilokOptionsForModal($(this).val());
    });

    $('#doc_type').on('change', function() {
        const val = $(this).val();
        if (['kwitansi_jamuan', 'surat_jalan', 'faktur', 'hadir_jamuan'].includes(val)) {
            $('.jamuan-fields').removeClass('d-none');
        } else {
            $('.jamuan-fields').addClass('d-none');
        }
    });

    // Edit Header Modal Trigger
    $('#btnEditDocHeader').on('click', function() {
        if (!DOC_DATA) return;

        initPnbpDocModalSelect2();

        $('#doc_key').val(DOC_DATA.uid);
        $('#doc_date').val(DOC_DATA.doc_date);
        $('#doc_number').val(DOC_DATA.doc_number || '');
        $('#doc_title').val(DOC_DATA.title || '');

        $('#doc_type').val(DOC_DATA.doc_type).trigger('change');

        if (DOC_DATA.instansi_id) {
            const instansiSelect = $('#doc_instansi_id');
            const instansiText = DOC_DATA.instansi_nama ? DOC_DATA.instansi_nama : DOC_DATA.instansi_id;
            if (!instansiSelect.find(`option[value="${DOC_DATA.instansi_id}"]`).length) {
                const newOption = new Option(instansiText, DOC_DATA.instansi_id, true, true);
                instansiSelect.append(newOption);
            }
            instansiSelect.val(DOC_DATA.instansi_id).trigger('change');
        } else {
            $('#doc_instansi_id').val('').trigger('change');
        }

        if (DOC_DATA.seleksi_id) {
            $('#doc_seleksi_id').val(DOC_DATA.seleksi_id).trigger('change');
            loadTilokOptionsForModal(DOC_DATA.seleksi_id, DOC_DATA.tilok_id);
        } else {
            $('#doc_seleksi_id').val('').trigger('change');
            $('#doc_tilok_id').html('<option value="">-- Pilih Event Dulu / Opsional --</option>').val('').trigger('change');
        }

        const meta = DOC_DATA.meta_data || {};
        $('#doc_mak').val(meta.mak || '');
        $('#doc_notes').val(meta.notes || '');
        $('#doc_vendor_name').val(meta.vendor_name || '');
        $('#doc_vendor_npwp').val(meta.vendor_npwp || '');

        if (['kwitansi_jamuan', 'surat_jalan', 'faktur', 'hadir_jamuan'].includes(DOC_DATA.doc_type)) {
            $('.jamuan-fields').removeClass('d-none');
        } else {
            $('.jamuan-fields').addClass('d-none');
        }

        $('#pnbpDocModalLabel').html('<i class="bi bi-pencil-square text-primary me-2"></i> Ubah Header Dokumen');
    });

    $('#pnbpDocModal').on('show.bs.modal', function() {
        initPnbpDocModalSelect2();
    });

    // Save header from detail page
    $('#btnSaveDocument').on('click', function() {
        $('#pnbpDocForm').submit();
    });

    $('#pnbpDocForm').on('submit', function(e) {
        e.preventDefault();
        $('#pnbpDocModal').modal('hide');

        if (typeof swlwaitProsessing === 'function') swlwaitProsessing('Menyimpan header dokumen...');

        $.ajax({
            url: AppConfig.initGlobal + 'store/save-pnbp-document',
            type: 'POST',
            data: $(this).serialize(),
            dataType: 'json',
            success: function(res) {
                if (res && res.status === 'success') {
                    window.location.reload();
                } else {
                    if (typeof swlErrorHandler === 'function') swlErrorHandler(res && res.message ? res.message : 'Gagal menyimpan.');
                }
            },
            error: function(xhr) {
                const msg = xhr.responseJSON && xhr.responseJSON.message ? xhr.responseJSON.message : 'Gagal menyimpan.';
                if (typeof swlErrorHandler === 'function') swlErrorHandler(msg);
            }
        });
    });
});
