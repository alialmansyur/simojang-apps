$(document).ready(function () {
    const categoryUid = $('#category_uid').val();
    let selectedPhotoFile = null;

    function escapeHtml(text) {
        if (!text) return '';
        return String(text)
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;')
            .replace(/'/g, '&#039;');
    }

    function formatNumberIndo(value, decimals = 2) {
        const n = Number(value || 0);
        return new Intl.NumberFormat('id-ID', {
            minimumFractionDigits: 0,
            maximumFractionDigits: decimals
        }).format(n);
    }

    function formatRupiah(value) {
        const n = Number(value || 0);
        return 'Rp ' + new Intl.NumberFormat('id-ID', {
            minimumFractionDigits: 0,
            maximumFractionDigits: 0
        }).format(n);
    }

    function formatRupiahInput(val) {
        if (val === null || val === undefined || val === '') return '';
        const numberString = String(val).replace(/[^0-9]/g, '');
        if (!numberString) return '';
        return new Intl.NumberFormat('id-ID').format(Number(numberString));
    }

    function unformatRupiah(val) {
        if (!val) return 0;
        const clean = String(val).replace(/[^0-9]/g, '');
        return clean ? parseInt(clean, 10) : 0;
    }

    function formatBytes(bytes, decimals = 2) {
        if (bytes === 0) return '0 Bytes';
        const k = 1024;
        const dm = decimals < 0 ? 0 : decimals;
        const sizes = ['Bytes', 'KB', 'MB', 'GB'];
        const i = Math.floor(Math.log(bytes) / Math.log(k));
        return parseFloat((bytes / Math.pow(k, i)).toFixed(dm)) + ' ' + sizes[i];
    }

    // Real-time Rupiah formatter pada input form modal
    $('#nilai_perolehan').on('input', function () {
        this.value = formatRupiahInput(this.value);
    });

    // ----------------------------------------------------
    //  SUMMARY RECAP LOADER
    // ----------------------------------------------------
    function loadSummaryAssets() {
        $.ajax({
            url: AppConfig.initGlobal + 'fetch/summary-manage-assets',
            type: 'POST',
            dataType: 'json',
            data: { category_uid: categoryUid },
            success: function (response) {
                const s = response?.summary || {};
                const totalData = Number(s.total_data || 0);
                const totalQty = Number(s.total_qty || 0);
                const totalNilai = Number(s.total_nilai || 0);

                $('#asset-total-data').text(formatNumberIndo(totalData, 0));
                $('#asset-total-qty').text(formatNumberIndo(totalQty, 2));
                $('#asset-total-nilai').text(formatRupiah(totalNilai)).attr('title', formatRupiah(totalNilai));

                if (s.last_update) {
                    if (window.ServiceTableUI && ServiceTableUI.formatDateTime) {
                        $('#asset-last-update').text(ServiceTableUI.formatDateTime(s.last_update));
                    } else {
                        $('#asset-last-update').text(s.last_update);
                    }
                } else {
                    $('#asset-last-update').text('-');
                }
            }
        });
    }

    // ----------------------------------------------------
    //  DROPZONE UPLOAD FOTO (ALA /apps-ikpa)
    // ----------------------------------------------------
    const dropzoneArea = document.getElementById('assetDropzoneArea');
    const fotoUpload = document.getElementById('fotoUpload');
    const btnBrowsePhoto = document.getElementById('btnBrowsePhoto');
    const dropzonePrompt = document.getElementById('dropzonePrompt');
    const photoPreviewContainer = document.getElementById('photoPreviewContainer');
    const photoPreviewImgModal = document.getElementById('photoPreviewImgModal');
    const photoPreviewName = document.getElementById('photoPreviewName');
    const btnRemovePhoto = document.getElementById('btnRemovePhoto');
    const removeFotoInput = document.getElementById('removeFoto');

    if (dropzoneArea) {
        // Prevent default drag behaviors
        ['dragenter', 'dragover', 'dragleave', 'drop'].forEach(eventName => {
            dropzoneArea.addEventListener(eventName, preventDefaults, false);
            document.body.addEventListener(eventName, preventDefaults, false);
        });

        function preventDefaults(e) {
            e.preventDefault();
            e.stopPropagation();
        }

        // Highlight dropzone saat file di-drag
        ['dragenter', 'dragover'].forEach(eventName => {
            dropzoneArea.addEventListener(eventName, () => {
                dropzoneArea.classList.add('dragover');
            }, false);
        });

        ['dragleave', 'drop'].forEach(eventName => {
            dropzoneArea.addEventListener(eventName, () => {
                dropzoneArea.classList.remove('dragover');
            }, false);
        });

        // Handle drop event
        dropzoneArea.addEventListener('drop', (e) => {
            const dt = e.dataTransfer;
            if (dt && dt.files && dt.files.length > 0) {
                handlePhotoSelection(dt.files[0]);
            }
        }, false);

        // Handle browse click
        if (btnBrowsePhoto) {
            btnBrowsePhoto.addEventListener('click', (e) => {
                e.stopPropagation();
                fotoUpload.click();
            });
        }

        dropzoneArea.addEventListener('click', (e) => {
            if (e.target !== btnRemovePhoto && !btnRemovePhoto.contains(e.target)) {
                if (selectedPhotoFile === null && photoPreviewContainer.classList.contains('d-none')) {
                    fotoUpload.click();
                }
            }
        });

        // Handle input file change
        fotoUpload.addEventListener('change', function () {
            if (this.files && this.files.length > 0) {
                handlePhotoSelection(this.files[0]);
            }
        });

        // Handle remove photo button
        btnRemovePhoto.addEventListener('click', (e) => {
            e.stopPropagation();
            resetDropzone();
            removeFotoInput.value = '1'; // Flag bahwa foto dihapus
        });
    }

    function handlePhotoSelection(file) {
        if (!file) return;

        const validTypes = ['image/jpeg', 'image/png', 'image/webp', 'image/jpg'];
        const validExtensions = /\.(jpe?g|png|webp)$/i;

        if (!validTypes.includes(file.type) && !validExtensions.test(file.name)) {
            Swal.fire('Format Tidak Sesuai', 'Hanya file foto berekstensi JPG, PNG, atau WEBP yang diperbolehkan.', 'warning');
            return;
        }

        // Maksimal 3MB
        if (file.size > 3 * 1024 * 1024) {
            Swal.fire('Ukuran Terlalu Besar', 'Ukuran foto maksimal adalah 3 MB.', 'warning');
            return;
        }

        selectedPhotoFile = file;
        removeFotoInput.value = '0';

        const reader = new FileReader();
        reader.onload = function (e) {
            photoPreviewImgModal.src = e.target.result;
            photoPreviewName.textContent = file.name + ' (' + formatBytes(file.size) + ')';
            dropzonePrompt.classList.add('d-none');
            photoPreviewContainer.classList.remove('d-none');
        };
        reader.readAsDataURL(file);
    }

    function setExistingPhotoPreview(url, fileName) {
        selectedPhotoFile = null;
        removeFotoInput.value = '0';
        if (url) {
            photoPreviewImgModal.src = url;
            photoPreviewName.textContent = fileName || 'Foto Terdaftar';
            dropzonePrompt.classList.add('d-none');
            photoPreviewContainer.classList.remove('d-none');
        } else {
            resetDropzone();
        }
    }

    function resetDropzone() {
        selectedPhotoFile = null;
        if (fotoUpload) fotoUpload.value = '';
        if (photoPreviewImgModal) photoPreviewImgModal.src = '';
        if (photoPreviewName) photoPreviewName.textContent = '';
        if (dropzonePrompt) dropzonePrompt.classList.remove('d-none');
        if (photoPreviewContainer) photoPreviewContainer.classList.add('d-none');
    }

    // ----------------------------------------------------
    //  DATATABLE INITIALIZATION
    // ----------------------------------------------------
    const table = $('#dataTable').DataTable({
        responsive: true,
        processing: true,
        serverSide: true,
        order: [[2, 'asc']], // Order by kode
        ajax: {
            url: AppConfig.initGlobal + 'fetch/data-manage-assets',
            type: 'POST',
            data: function (d) {
                d.category_uid = categoryUid;
            }
        },
        columns: [
            {
                data: 'DT_RowIndex',
                name: 'DT_RowIndex',
                className: 'text-center align-middle',
                orderable: false,
                searchable: false,
                render: function (data, type, row, meta) {
                    return data || (meta.row + meta.settings._iDisplayStart + 1);
                }
            },
            {
                data: 'foto',
                className: 'text-center align-middle',
                orderable: false,
                searchable: false,
                render: function (data, type, row) {
                    if (data) {
                        const baseUrl = window.ASSET_UPLOAD_URL || (AppConfig.initGlobal + 'apps/assets/uploads/assets/');
                        const fullUrl = baseUrl + data;
                        return `
                            <div class="d-flex align-items-center justify-content-center mx-auto" style="width: 44px; height: 44px;">
                                <img src="${fullUrl}" class="asset-thumb-preview" alt="Foto Asset" title="Klik untuk memperbesar" onclick="window.previewAssetPhoto('${fullUrl}', '${escapeHtml(row.uraian)}')">
                            </div>
                        `;
                    }
                    return `
                        <div class="d-flex align-items-center justify-content-center mx-auto text-muted rounded" style="width: 42px; height: 42px; background-color: #f1f5f9; border: 1px dashed #cbd5e1;" title="Belum ada foto">
                            <i class="bi bi-image" style="font-size: 1.15rem; color: #94a3b8; line-height: 1;"></i>
                        </div>
                    `;
                }
            },
            {
                data: 'kode',
                className: 'align-middle',
                render: function (data, type, row) {
                    let html = `<div class="fw-bold" style="color: #1e293b;">${escapeHtml(data)}</div>`;
                    if (row.subcode) {
                        html += `<small class="text-muted"><i class="bi bi-tag me-1"></i>${escapeHtml(row.subcode)}</small>`;
                    }
                    return html;
                }
            },
            {
                data: 'uraian',
                className: 'align-middle',
                render: function (data, type, row) {
                    let html = `<div class="fw-semibold" style="color: #0f172a;">${escapeHtml(data)}</div>`;
                    const metaParts = [];
                    if (row.area) {
                        metaParts.push(`<span class="badge" style="background: #e0f2fe; color: #0369a1; border: 1px solid #bae6fd; font-weight: 600; font-size: 0.75rem;"><i class="bi bi-geo-alt-fill me-1"></i>${escapeHtml(row.area)}</span>`);
                    }
                    if (row.lokasi) {
                        metaParts.push(`<span class="text-secondary"><i class="bi bi-geo-alt text-primary me-1"></i>${escapeHtml(row.lokasi)}</span>`);
                    }
                    if (row.tahun_perolehan) {
                        metaParts.push(`<span class="text-secondary"><i class="bi bi-calendar-event text-secondary me-1"></i>Th. ${escapeHtml(row.tahun_perolehan)}</span>`);
                    }
                    if (metaParts.length > 0) {
                        html += `<div class="d-flex flex-wrap align-items-center gap-1 mt-1" style="font-size: 0.8rem;">${metaParts.join('<span class="text-muted mx-1">&bull;</span>')}</div>`;
                    }
                    return html;
                }
            },
            {
                data: 'satuan',
                className: 'text-center align-middle',
                render: function (data) {
                    return `<span class="badge" style="background: #f1f5f9; color: #475569; border: 1px solid #e2e8f0; font-weight: 600; padding: 0.35rem 0.6rem;">${escapeHtml(data)}</span>`;
                }
            },
            {
                data: 'qty',
                className: 'text-end align-middle fw-bold text-primary',
                render: function (data) {
                    return formatNumberIndo(data, 2);
                }
            },
            {
                data: 'nilai_perolehan',
                className: 'text-end align-middle',
                render: function (data, type, row) {
                    const nilai = Number(data || 0);
                    const qty = Number(row.qty || 0);
                    let html = `<div class="fw-bold" style="color: #0f172a;">${formatRupiah(nilai)}</div>`;
                    if (qty > 1 && nilai > 0) {
                        html += `<small class="text-success fw-semibold" title="Total Nilai Kuantitas">Sub: ${formatRupiah(nilai * qty)}</small>`;
                    }
                    return html;
                }
            },
            {
                data: 'kondisi',
                className: 'text-center align-middle',
                render: function (data) {
                    const val = data || 'Baik';
                    if (val === 'Baik') {
                        return `<span class="badge bg-success-subtle text-success border border-success-subtle fw-semibold px-2 py-1"><i class="bi bi-check-circle me-1"></i>Baik</span>`;
                    } else if (val === 'Rusak Ringan') {
                        return `<span class="badge bg-warning-subtle text-warning border border-warning-subtle fw-semibold px-2 py-1"><i class="bi bi-exclamation-triangle me-1"></i>Rusak Ringan</span>`;
                    } else {
                        return `<span class="badge bg-danger-subtle text-danger border border-danger-subtle fw-semibold px-2 py-1"><i class="bi bi-x-circle me-1"></i>Rusak Berat</span>`;
                    }
                }
            },
            {
                data: null,
                orderable: false,
                searchable: false,
                className: 'text-center align-middle text-nowrap',
                render: function (data, type, row) {
                    return `
                        <button type="button" class="btn btn-sm p-2 lh-1 btn-edit-hover me-1 btn-edit" title="Edit Asset" data-id="${row.id}" style="background: #f0f9ff; border: 1px solid #bae6fd; border-radius: 8px; transition: all 0.2s;">
                            <i class="bi bi-pencil" style="font-size: 0.95rem;"></i>
                        </button>
                        <button type="button" class="btn btn-sm p-2 lh-1 btn-trash-hover btn-delete" title="Hapus Asset" data-id="${row.id}" data-uraian="${escapeHtml(row.uraian)}" style="background: #fff0f2; border: 1px solid #ffe4e6; border-radius: 8px; transition: all 0.2s;">
                            <i class="bi bi-trash3" style="font-size: 0.95rem;"></i>
                        </button>
                    `;
                }
            }
        ],
        language: {
            emptyTable: (window.ServiceTableUI && ServiceTableUI.createEmptyState) ? ServiceTableUI.createEmptyState() : '<div class="text-center py-4 text-muted">Belum ada rincian asset untuk kategori ini.</div>',
            zeroRecords: (window.ServiceTableUI && ServiceTableUI.createEmptyState) ? ServiceTableUI.createEmptyState() : '<div class="text-center py-4 text-muted">Data asset tidak ditemukan.</div>',
            processing: '<div class="service-ui-processing"><span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> <span>Memuat data asset...</span></div>'
        },
        initComplete: function () {
            loadSummaryAssets();
        }
    });

    // Handle Muat Ulang action
    $('.js-service-reload').on('click', function () {
        table.ajax.reload(null, false);
        loadSummaryAssets();
    });

    // ----------------------------------------------------
    //  GLOBAL PHOTO PREVIEW MODAL
    // ----------------------------------------------------
    window.previewAssetPhoto = function (url, title) {
        if (!url) return;
        $('#photoPreviewFullImg').attr('src', url);
        $('#photoPreviewTitle').text(title || 'Foto Fisik Asset');
        $('#PhotoPreviewModal').modal('show');
    };

    // ----------------------------------------------------
    //  TAMBAH DATA BUTTON
    // ----------------------------------------------------
    $('#btnTambahData').on('click', function () {
        $('#formAssetDetail')[0].reset();
        $('#assetId').val('');
        $('#satuan').val('');
        $('#nilai_perolehan').val('');
        $('#kondisi').val('Baik');
        $('#area').val('Kantor Regional III BKN');
        removeFotoInput.value = '0';
        resetDropzone();
        $('#DataModalLabel').text('Tambah Data Asset');
        $('#formAssetDetail').attr('action', AppConfig.initGlobal + 'apps-manage-assets-detail/store');
        $('#btnSubmitForm').prop('disabled', false).text('Simpan Data');
    });

    // ----------------------------------------------------
    //  EDIT DATA EVENT
    // ----------------------------------------------------
    $('#dataTable tbody').on('click', '.btn-edit', function (e) {
        e.preventDefault();
        const id = $(this).data('id');
        
        $.ajax({
            url: AppConfig.initGlobal + 'apps-manage-assets-detail/get-data',
            type: 'POST',
            dataType: 'json',
            data: { id: id },
            success: function (res) {
                if (res.status === 'success') {
                    const data = res.data;
                    $('#assetId').val(data.id);
                    $('#kode').val(data.kode);
                    $('#subcode').val(data.subcode || '');
                    $('#uraian').val(data.uraian);
                    
                    // Satuan dropdown handling (jika custom value belum ada di options, append secara aman)
                    if (data.satuan) {
                        if ($('#satuan option[value="' + data.satuan + '"]').length === 0) {
                            $('#satuan').append(new Option(data.satuan, data.satuan, true, true));
                        }
                        $('#satuan').val(data.satuan);
                    } else {
                        $('#satuan').val('');
                    }

                    $('#qty').val(data.qty);
                    
                    // Nilai perolehan format rupiah
                    if (data.nilai_perolehan && Number(data.nilai_perolehan) > 0) {
                        $('#nilai_perolehan').val(formatRupiahInput(Math.round(Number(data.nilai_perolehan))));
                    } else {
                        $('#nilai_perolehan').val('');
                    }

                    $('#kondisi').val(data.kondisi || 'Baik');
                    $('#area').val(data.area || 'Kantor Regional III BKN');
                    $('#lokasi').val(data.lokasi || '');
                    $('#tahun_perolehan').val(data.tahun_perolehan || '');
                    $('#keterangan').val(data.keterangan || '');
                    
                    // Set foto preview jika ada
                    if (data.foto_url) {
                        setExistingPhotoPreview(data.foto_url, data.foto);
                    } else {
                        resetDropzone();
                    }

                    $('#DataModalLabel').text('Edit Data Asset');
                    $('#formAssetDetail').attr('action', AppConfig.initGlobal + 'apps-manage-assets-detail/update');
                    $('#btnSubmitForm').prop('disabled', false).text('Perbarui Data');
                    $('#DataModal').modal('show');
                } else {
                    Swal.fire('Error', res.message || 'Gagal memuat data asset.', 'error');
                }
            },
            error: function () {
                Swal.fire('Error', 'Terjadi kesalahan pada server saat memuat data.', 'error');
            }
        });
    });

    // ----------------------------------------------------
    //  FORM SUBMIT (MULTIPART AJAX / DROPZONE SUPPORT)
    // ----------------------------------------------------
    $('#formAssetDetail').on('submit', function (e) {
        e.preventDefault();
        const form = document.getElementById('formAssetDetail');
        const url = $('#formAssetDetail').attr('action');
        const btnSubmit = $('#btnSubmitForm');
        const originalText = btnSubmit.text();

        const formData = new FormData(form);

        // Jika user memilih file lewat dropzone
        if (selectedPhotoFile) {
            formData.set('foto', selectedPhotoFile, selectedPhotoFile.name);
        }

        // Convert nilai_perolehan kembali ke bentuk numeric integer murni saat dikirim
        const rawNilai = $('#nilai_perolehan').val();
        formData.set('nilai_perolehan', unformatRupiah(rawNilai));

        btnSubmit.prop('disabled', true).html('<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Menyimpan...');

        $.ajax({
            url: url,
            type: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            dataType: 'json',
            success: function (res) {
                btnSubmit.prop('disabled', false).text(originalText);
                if (res.status === 'success') {
                    $('#DataModal').modal('hide');
                    Swal.fire({
                        icon: 'success',
                        title: 'Berhasil',
                        text: res.message,
                        timer: 1500,
                        showConfirmButton: false
                    });
                    table.ajax.reload(null, false);
                    loadSummaryAssets();
                } else {
                    Swal.fire('Gagal', res.message || 'Gagal menyimpan data.', 'error');
                }
            },
            error: function (xhr) {
                btnSubmit.prop('disabled', false).text(originalText);
                const msg = xhr.responseJSON?.message || 'Terjadi kesalahan sistem pada server.';
                Swal.fire('Error', msg, 'error');
            }
        });
    });

    // ----------------------------------------------------
    //  DELETE EVENT
    // ----------------------------------------------------
    $('#dataTable tbody').on('click', '.btn-delete', function (e) {
        e.preventDefault();
        const id = $(this).data('id');
        const uraian = $(this).data('uraian') || 'asset ini';

        Swal.fire({
            title: 'Hapus Data Asset?',
            text: 'Anda yakin ingin menghapus data "' + uraian + '"? Foto dan data terkait akan dihapus secara permanen.',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            cancelButtonColor: '#6e7d88',
            confirmButtonText: 'Ya, Hapus!',
            cancelButtonText: 'Batal'
        }).then((result) => {
            if (result.isConfirmed) {
                Swal.fire({
                    title: 'Menghapus...',
                    allowOutsideClick: false,
                    didOpen: () => { Swal.showLoading(); }
                });

                $.ajax({
                    url: AppConfig.initGlobal + 'apps-manage-assets-detail/delete',
                    type: 'POST',
                    dataType: 'json',
                    data: { id: id },
                    success: function (res) {
                        if (res.status === 'success') {
                            Swal.fire('Terhapus!', res.message, 'success');
                            table.ajax.reload(null, false);
                            loadSummaryAssets();
                        } else {
                            Swal.fire('Gagal', res.message || 'Gagal menghapus data asset.', 'error');
                        }
                    },
                    error: function () {
                        Swal.fire('Error', 'Terjadi kesalahan sistem saat menghapus data.', 'error');
                    }
                });
            }
        });
    });

    // ----------------------------------------------------
    //  RESET MODAL ON CLOSE
    // ----------------------------------------------------
    $('#DataModal').on('hidden.bs.modal', function () {
        $('#formAssetDetail')[0].reset();
        $('#assetId').val('');
        $('#satuan').val('');
        $('#nilai_perolehan').val('');
        $('#kondisi').val('Baik');
        $('#area').val('Kantor Regional III BKN');
        removeFotoInput.value = '0';
        resetDropzone();
        $('#btnSubmitForm').prop('disabled', false).text('Simpan Data');
        $('.modal-backdrop').remove();
        $('body').removeClass('modal-open').css('overflow', '');
    });
});
