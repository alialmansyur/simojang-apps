$(document).ready(function() {
    // Dynamic Search Filter
    $('#searchInput').on('keyup', function() {
        var value = $(this).val().toLowerCase().trim();
        $('.category-item').each(function() {
            var name = String($(this).data('name') || '');
            if (name.includes(value)) {
                $(this).show();
            } else {
                $(this).hide();
            }
        });

        // Tampilkan info jika tidak ada hasil pencarian
        if ($('.category-item:visible').length === 0) {
            if ($('#noSearchInfo').length === 0) {
                var emptyHtml = '<div class="col-12" id="noSearchInfo">' +
                                '<div class="d-flex flex-column align-items-center justify-content-center text-center mt-5 mb-5 pb-4 tw-animate-entry">' +
                                '<img src="' + AppConfig.initGlobal + 'apps/assets/images/empty-content-profile.png" alt="Tidak Ditemukan" style="max-width: 320px; margin-bottom: 2rem;">' +
                                '<h5 class="fw-bold" style="color: #1a202c; font-size: 1.35rem;">Pencarian Tidak Ditemukan</h5>' +
                                '<p class="text-muted mb-0" style="font-size: 1.05rem; max-width: 450px; margin: 0 auto; line-height: 1.6;">Tidak ada kategori asset yang cocok dengan kata kunci tersebut.</p>' +
                                '</div></div>';
                $('#categoryList').append(emptyHtml);
            }
        } else {
            $('#noSearchInfo').remove();
        }
    });

    // Form Tambah Kategori Submit
    $('#formCategory').on('submit', function(e) {
        e.preventDefault();
        var form = $(this);
        var btn = $('#btnSaveCategory');
        btn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Menyimpan...');

        $.ajax({
            url: AppConfig.initGlobal + 'store/save-asset-category',
            type: 'POST',
            data: form.serialize(),
            dataType: 'json',
            success: function(response) {
                if (response.status === 'success') {
                    $('#CategoryModal').modal('hide');
                    Swal.fire({
                        icon: 'success',
                        title: 'Berhasil',
                        text: response.message,
                        timer: 1500,
                        showConfirmButton: false
                    }).then(() => {
                        window.location.reload();
                    });
                } else {
                    Swal.fire('Gagal!', response.message || 'Gagal menyimpan kategori.', 'error');
                    btn.prop('disabled', false).html('Simpan Kategori');
                }
            },
            error: function() {
                Swal.fire('Oops!', 'Terjadi kesalahan sistem.', 'error');
                btn.prop('disabled', false).html('Simpan Kategori');
            }
        });
    });

    // Form Edit Kategori Submit
    $('#formEditCategory').on('submit', function(e) {
        e.preventDefault();
        var form = $(this);
        var btn = $('#btnUpdateCategory');
        btn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Menyimpan...');

        $.ajax({
            url: AppConfig.initGlobal + 'store/update-asset-category',
            type: 'POST',
            data: form.serialize(),
            dataType: 'json',
            success: function(response) {
                if (response.status === 'success') {
                    $('#EditCategoryModal').modal('hide');
                    Swal.fire({
                        icon: 'success',
                        title: 'Berhasil',
                        text: response.message,
                        timer: 1500,
                        showConfirmButton: false
                    }).then(() => {
                        window.location.reload();
                    });
                } else {
                    Swal.fire('Gagal!', response.message || 'Gagal memperbarui kategori.', 'error');
                    btn.prop('disabled', false).html('Perbarui Kategori');
                }
            },
            error: function() {
                Swal.fire('Oops!', 'Terjadi kesalahan sistem.', 'error');
                btn.prop('disabled', false).html('Perbarui Kategori');
            }
        });
    });

    // Reset Modal on Close (Clean Lifecycle)
    $('#CategoryModal').on('hidden.bs.modal', function() {
        $('#formCategory')[0].reset();
        $('#btnSaveCategory').prop('disabled', false).html('Simpan Kategori');
    });

    $('#EditCategoryModal').on('hidden.bs.modal', function() {
        $('#formEditCategory')[0].reset();
        $('#btnUpdateCategory').prop('disabled', false).html('Perbarui Kategori');
    });
});

// Window-scoped Edit Handler
window.editCategory = function(uid, name) {
    $('#editCategoryUid').val(uid);
    $('#editCategoryName').val(name);
    $('#btnUpdateCategory').prop('disabled', false).html('Perbarui Kategori');
    $('#EditCategoryModal').modal('show');
};

// Window-scoped Delete Handler
window.deleteCategory = function(uid, name, totalAssets) {
    if (totalAssets > 0) {
        Swal.fire({
            icon: 'warning',
            title: 'Kategori Tidak Dapat Dihapus',
            text: 'Kategori "' + name + '" masih memuat ' + totalAssets + ' item asset terdaftar. Hapus seluruh item asset terlebih dahulu sebelum menghapus kategori ini.',
            confirmButtonColor: '#3085d6',
            confirmButtonText: 'Mengerti'
        });
        return;
    }

    Swal.fire({
        title: 'Hapus Kategori?',
        text: 'Anda yakin ingin menghapus kategori "' + name + '"? Tindakan ini tidak dapat dibatalkan.',
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
                url: AppConfig.initGlobal + 'kill/asset-category',
                type: 'POST',
                data: { uid: uid },
                dataType: 'json',
                success: function(response) {
                    if (response.status === 'success') {
                        Swal.fire('Terhapus!', response.message, 'success').then(() => {
                            window.location.reload();
                        });
                    } else {
                        Swal.fire('Gagal!', response.message || 'Gagal menghapus kategori.', 'error');
                    }
                },
                error: function() {
                    Swal.fire('Oops!', 'Terjadi kesalahan saat menghapus data.', 'error');
                }
            });
        }
    });
};
