$(document).ready(function() {
    // Format mata uang input
    $("input[data-type='currency']").on({
        keyup: function() {
            formatCurrency($(this));
        },
        blur: function() { 
            formatCurrency($(this), "blur");
        }
    });

    function formatNumber(n) {
        return n.replace(/\D/g, "").replace(/\B(?=(\d{3})+(?!\d))/g, ",");
    }

    function formatCurrency(input, blur) {
        var input_val = input.val();
        if (input_val === "") { return; }
        var original_len = input_val.length;
        var caret_pos = input.prop("selectionStart");
        if (input_val.indexOf(".") >= 0) {
            var decimal_pos = input_val.indexOf(".");
            var left_side = input_val.substring(0, decimal_pos);
            var right_side = input_val.substring(decimal_pos);
            left_side = formatNumber(left_side);
            right_side = formatNumber(right_side);
            if (blur === "blur") { right_side += "00"; }
            right_side = right_side.substring(0, 2);
            input_val = left_side + "." + right_side;
        } else {
            input_val = formatNumber(input_val);
            if (blur === "blur") { input_val += ".00"; }
        }
        input.val(input_val);
        var updated_len = input_val.length;
        caret_pos = updated_len - original_len + caret_pos;
        input[0].setSelectionRange(caret_pos, caret_pos);
    }

    // Dynamic Search
    $('#searchInput').on('keyup', function() {
        var value = $(this).val().toLowerCase();
        $('.project-item').each(function() {
            var name = $(this).data('name');
            var category = $(this).data('category');
            if (name.includes(value) || category.includes(value)) {
                $(this).show();
            } else {
                $(this).hide();
            }
        });

        // Tampilkan info jika tidak ada hasil
        if ($('.project-item:visible').length === 0) {
            if ($('#noSearchInfo').length === 0) {
                var emptyHtml = '<div class="col-12" id="noSearchInfo">' +
                                '<div class="d-flex flex-column align-items-center justify-content-center text-center mt-5 mb-5 pb-4 tw-animate-entry">' +
                                '<img src="' + window.location.origin + '/apps/assets/images/empty-content-profile.png" alt="Tidak Ditemukan" style="max-width: 320px; margin-bottom: 2rem;">' +
                                '<h5 class="fw-bold" style="color: #1a202c; font-size: 1.35rem;">Pencarian Tidak Ditemukan</h5>' +
                                '<p class="text-muted mb-0" style="font-size: 1.05rem; max-width: 450px; margin: 0 auto; line-height: 1.6;">Tidak ada proyek yang cocok dengan kata kunci tersebut.</p>' +
                                '</div></div>';
                $('#projectList').append(emptyHtml);
            }
        } else {
            $('#noSearchInfo').remove();
        }
    });

    // Form Submit
    $('#formProject').on('submit', function(e) {
        e.preventDefault();
        let btn = $('#btnSaveProject');
        btn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Menyimpan...');
        
        $.ajax({
            url: window.location.origin + '/store/save-project',
            type: 'POST',
            data: $(this).serialize(),
            dataType: 'json',
            success: function(response) {
                if(response.status == 'success'){
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
                    Swal.fire('Gagal!', response.message, 'error');
                    btn.prop('disabled', false).html('Simpan Proyek');
                }
            },
            error: function(xhr) {
                Swal.fire('Oops!', 'Terjadi kesalahan sistem.', 'error');
                btn.prop('disabled', false).html('Simpan Proyek');
            }
        });
    });
});

window.deleteProject = function(uid, name) {
    Swal.fire({
        title: 'Hapus Proyek?',
        text: "Anda yakin ingin menghapus proyek " + name + "? Data yang telah dihapus tidak dapat dikembalikan.",
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#d33',
        cancelButtonColor: '#6e7d88',
        confirmButtonText: 'Ya, Hapus!',
        cancelButtonText: 'Batal'
    }).then((result) => {
        if (result.isConfirmed) {
            $.ajax({
                url: window.location.origin + '/kill/data-project',
                type: 'POST',
                data: { uid: uid },
                dataType: 'json',
                success: function(response) {
                    if (response.status) {
                        Swal.fire('Terhapus!', response.message, 'success').then(() => {
                            window.location.reload();
                        });
                    } else {
                        Swal.fire('Gagal!', response.message, 'error');
                    }
                },
                error: function() {
                    Swal.fire('Oops!', 'Terjadi kesalahan saat menghapus data.', 'error');
                }
            });
        }
    });
};
