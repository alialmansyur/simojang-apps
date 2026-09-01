$(document).ready(function() {
    // Function to trigger staggered animations on visible cards
    function triggerStaggeredAnimation() {
        $('.seleksi-item:visible').each(function(index) {
            var $card = $(this).find('.card');
            $card.css('animation', 'none');
            void this.offsetWidth; // Force reflow
            $card.css({
                'animation': 'twSlideFadeUp 0.5s cubic-bezier(0.175, 0.885, 0.32, 1.275) forwards',
                'animation-delay': (index * 0.06) + 's',
                'opacity': '0'
            });
        });
    }

    // Filter Logic (Search + Tahun + Event)
    function applyFilters() {
        var searchValue = $('#searchInput').val().toLowerCase();
        var yearValue = $('#filterTahun').val(); // '2025', '2026', or ''
        var eventValue = $('#filterEvent').val(); // 'skd cpns', etc or ''

        $('.seleksi-item').each(function() {
            var name = $(this).data('name') || '';
            var event = $(this).data('event') || '';
            var periode = $(this).data('periode') || '';
            
            var matchesSearch = (name.includes(searchValue) || event.includes(searchValue));
            var matchesYear = (yearValue === '' || periode.toString().startsWith(yearValue));
            var matchesEvent = (eventValue === '' || event === eventValue);

            if (matchesSearch && matchesYear && matchesEvent) {
                $(this).show();
            } else {
                $(this).hide();
            }
        });

        // Trigger animation for the newly filtered visible items
        triggerStaggeredAnimation();

        // Tampilkan info jika tidak ada hasil
        if ($('.seleksi-item:visible').length === 0) {
            if ($('#noSearchInfo').length === 0) {
                var emptyHtml = '<div class="col-12" id="noSearchInfo">' +
                                '<div class="d-flex flex-column align-items-center justify-content-center text-center mt-5 mb-5 pb-4 tw-animate-entry">' +
                                '<img src="' + AppConfig.initGlobal + 'apps/assets/images/empty-content-profile.png" alt="Tidak Ditemukan" style="max-width: 320px; margin-bottom: 2rem;">' +
                                '<h5 class="fw-bold" style="color: #1a202c; font-size: 1.35rem;">Pencarian Tidak Ditemukan</h5>' +
                                '<p class="text-muted mb-0" style="font-size: 1.05rem; max-width: 450px; margin: 0 auto; line-height: 1.6;">Tidak ada data seleksi yang cocok dengan filter Anda.</p>' +
                                '</div></div>';
                $('#seleksiList').append(emptyHtml);
            }
        } else {
            $('#noSearchInfo').remove();
        }
    }

    $('#searchInput').on('keyup', applyFilters);
    $('#filterTahun').on('change', applyFilters);
    $('#filterEvent').on('change', applyFilters);

    // Initial Filter Execution (defaults to selected year)
    applyFilters();

    // Bind Edit and Delete button clicks (delegated to document for dynamically shown cards)
    $(document).on('click', '.twx-edit-btn', function(e) {
        e.preventDefault();
        e.stopPropagation();
        var uid = $(this).data('uid');
        var name = $(this).data('name');
        var jenis = $(this).data('jenis');
        var periode = $(this).data('periode');
        editSeleksi(uid, name, jenis, periode);
    });

    $(document).on('click', '.twx-delete-btn', function(e) {
        e.preventDefault();
        e.stopPropagation();
        var uid = $(this).data('uid');
        var name = $(this).data('name');
        deleteSeleksi(uid, name);
    });

    // Form Submit
    $('#formSeleksi').on('submit', function(e) {
        e.preventDefault();
        $('#SeleksiModal').modal('hide');
        if (typeof swlwaitProsessing === 'function') swlwaitProsessing();
        else {
            Swal.fire({
                title: 'Memproses...',
                text: 'Mohon tunggu sebentar',
                allowOutsideClick: false,
                didOpen: () => { Swal.showLoading() }
            });
        }
        
        $.ajax({
            url: AppConfig.initGlobal + 'store/save-data-seleksi-cat',
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
                        $.get(window.location.href, function(data) {
                            var newContent = $(data).find('#seleksiList').html();
                            $('#seleksiList').html(newContent);
                            $('#searchInput').trigger('keyup');
                        });
                    });
                } else {
                    if(response.status == 'error'){
                        Swal.fire('Error', response.message, 'error');
                    }
                }
            },
            error: function() {
                Swal.fire('Error', 'Terjadi kesalahan sistem', 'error');
            }
        });
    });

    $('#SeleksiModal').on('hidden.bs.modal', function () {
        $('#formSeleksi')[0].reset();
        $('#seleksi_key').val('');
        $('#SeleksiModalLabel').text('Tambah Nama Seleksi');
        
        // Reset select picker if using select2
        if ($('#jenisEventPicker').hasClass('select2-hidden-accessible')) {
            $('#jenisEventPicker').val('').trigger('change');
        }
    });
});

window.editSeleksi = function(uid, nama, jenis_id, periode) {
    $('#seleksi_key').val(uid);
    $('#namaSeleksi').val(nama);
    $('#periodeSeleksi').val(periode);
    $('#jenisEventPicker').val(jenis_id);
    
    // trigger change for select2 if present
    if ($('#jenisEventPicker').hasClass('select2-hidden-accessible')) {
        $('#jenisEventPicker').trigger('change');
    }
    
    $('#SeleksiModalLabel').text('Edit Nama Seleksi');
    var myModal = new bootstrap.Modal(document.getElementById('SeleksiModal'));
    myModal.show();
};

window.deleteSeleksi = function(uid, name) {
    Swal.fire({
        title: 'Hapus Seleksi?',
        text: "Anda yakin ingin menghapus seleksi " + name + "?",
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#d33',
        cancelButtonColor: '#6e7d88',
        confirmButtonText: 'Ya, Hapus!',
        cancelButtonText: 'Batal'
    }).then((result) => {
        if (result.isConfirmed) {
            if (typeof swlwaitProsessing === 'function') swlwaitProsessing();
            else {
                Swal.fire({
                    title: 'Memproses...',
                    text: 'Mohon tunggu sebentar',
                    allowOutsideClick: false,
                    didOpen: () => { Swal.showLoading() }
                });
            }
            $.ajax({
                url: AppConfig.initGlobal + 'kill/data-seleksi-cat',
                type: 'POST',
                data: { key: uid },
                dataType: 'json',
                success: function(response) {
                    if (response.status) {
                        Swal.fire('Terhapus!', response.message, 'success').then(() => {
                        $.get(window.location.href, function(data) {
                            var newContent = $(data).find('#seleksiList').html();
                            $('#seleksiList').html(newContent);
                            $('#searchInput').trigger('keyup');
                        });
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
