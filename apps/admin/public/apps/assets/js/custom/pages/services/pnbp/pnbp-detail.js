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

    $('.btn-delete-personel').on('click', function() {
        const id = $(this).data('id');
        Swal.fire({
            text: 'Hapus personel ini dari daftar tim?',
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

    // Edit Header Modal Trigger
    $('#btnEditDocHeader').on('click', function() {
        if (!DOC_DATA) return;

        $('#doc_key').val(DOC_DATA.uid);
        $('#doc_type').val(DOC_DATA.doc_type);
        $('#doc_date').val(DOC_DATA.doc_date);
        $('#doc_number').val(DOC_DATA.doc_number || '');
        $('#doc_title').val(DOC_DATA.title || '');
        $('#doc_seleksi_id').val(DOC_DATA.seleksi_id || '');
        $('#doc_instansi_id').val(DOC_DATA.instansi_id || '');

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
        
        if (DOC_DATA.seleksi_id) {
            const tilokSelect = $('#doc_tilok_id');
            tilokSelect.html('<option value="">Memuat titik lokasi...</option>');
            $.ajax({
                url: AppConfig.initGlobal + 'fetch/pnbp-options-tilok',
                type: 'POST',
                data: { seleksi_id: DOC_DATA.seleksi_id },
                dataType: 'json',
                success: function(res) {
                    tilokSelect.empty();
                    tilokSelect.append('<option value="">-- Pilih Titik Lokasi --</option>');
                    if (res && res.data && res.data.length) {
                        res.data.forEach(t => {
                            const selected = DOC_DATA.tilok_id && String(DOC_DATA.tilok_id) === String(t.id) ? 'selected' : '';
                            tilokSelect.append(`<option value="${t.id}" ${selected}>${t.nama_tilok}</option>`);
                        });
                    }
                }
            });
        }
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
