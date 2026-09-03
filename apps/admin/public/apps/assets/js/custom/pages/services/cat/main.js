$(document).ready(function() {
    const CAT_EVENT_API = (window.AppConfig ? AppConfig.initGlobal : (typeof base_url !== 'undefined' ? base_url + '/' : '')) + 'api/apps-cat/events';
    let catEventRows = [];

    function escapeHtml(value) {
        return String(value || '')
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;')
            .replace(/'/g, '&#039;');
    }

    // Function to trigger staggered animations on visible cards
    function triggerStaggeredAnimation() {
        $('.seleksi-item:not(.d-none)').each(function(index) {
            var $card = $(this).find('.card');
            $card.css('animation', 'none');
            void this.offsetWidth; // Force reflow
            $card.css({
                'animation': 'twSlideFadeUp 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.275) forwards',
                'animation-delay': (index * 0.05) + 's',
                'opacity': '0'
            });
        });
    }

    // Filter & Sort Logic (Search + Tahun + Sort A-Z / Data Terupdate / Data Belum Update)
    function applyFilters() {
        var searchValue = ($('#searchInput').val() || '').toString().toLowerCase().trim();
        var yearValue = ($('#filterTahun').val() || '').toString().trim();
        var sortValue = ($('#catSort').val() || 'default').toString().trim();

        var $items = $('.seleksi-item');
        if (!$items.length) return;

        // 1. Filter visibility
        var visibleCount = 0;
        $items.each(function() {
            var $el = $(this);
            var name = ($el.attr('data-name') || $el.data('name') || '').toString().toLowerCase();
            var kode = ($el.attr('data-kode') || $el.data('kode') || '').toString().toLowerCase();
            var periode = ($el.attr('data-periode') || $el.data('periode') || '').toString().trim();
            
            var matchesSearch = (searchValue === '' || name.includes(searchValue) || kode.includes(searchValue));
            var matchesYear = (yearValue === '' || periode === yearValue);

            if (matchesSearch && matchesYear) {
                $el.removeClass('d-none');
                visibleCount++;
            } else {
                $el.addClass('d-none');
            }
        });

        // 2. Sorting items in DOM
        var itemArray = $items.toArray();
        itemArray.sort(function(a, b) {
            var $a = $(a);
            var $b = $(b);
            var nameA = ($a.attr('data-name') || $a.data('name') || '').toString();
            var nameB = ($b.attr('data-name') || $b.data('name') || '').toString();
            var updatedA = Number($a.attr('data-updated') || $a.data('updated') || 0);
            var updatedB = Number($b.attr('data-updated') || $b.data('updated') || 0);
            var ongoingA = Number($a.attr('data-ongoing') || $a.data('ongoing') || 0);
            var ongoingB = Number($b.attr('data-ongoing') || $b.data('ongoing') || 0);
            var hasRekapA = Number($a.attr('data-has-rekap') || $a.data('has-rekap') || 0);
            var hasRekapB = Number($b.attr('data-has-rekap') || $b.data('has-rekap') || 0);

            if (sortValue === 'name_asc') {
                return nameA.localeCompare(nameB, 'id');
            } else if (sortValue === 'updated_desc') {
                if (updatedB !== updatedA) return updatedB - updatedA;
                return nameA.localeCompare(nameB, 'id');
            } else if (sortValue === 'pending_first') {
                if (hasRekapA !== hasRekapB) return hasRekapA - hasRekapB;
                if (updatedA !== updatedB) return updatedA - updatedB;
                return nameA.localeCompare(nameB, 'id');
            } else {
                // Default: Ongoing first -> Updated desc -> Name A-Z
                if (ongoingB !== ongoingA) return ongoingB - ongoingA;
                if (updatedB !== updatedA) return updatedB - updatedA;
                return nameA.localeCompare(nameB, 'id');
            }
        });

        // Re-append sorted elements
        $('#seleksiList').append(itemArray);

        // Trigger animation for the newly filtered visible items
        triggerStaggeredAnimation();

        // Tampilkan info jika tidak ada hasil
        if (visibleCount === 0) {
            if ($('#noSearchInfo').length === 0) {
                var emptyImg = (window.AppConfig ? AppConfig.initGlobal : (typeof base_url !== 'undefined' ? base_url + '/' : '')) + 'apps/assets/images/empty-content-profile.png';
                var emptyHtml = '<div class="col-12" id="noSearchInfo">' +
                                '<div class="d-flex flex-column align-items-center justify-content-center text-center mt-5 mb-5 pb-4 tw-animate-entry">' +
                                '<img src="' + emptyImg + '" alt="Tidak Ditemukan" class="cat-empty-img">' +
                                '<h5 class="fw-bold cat-empty-title">Pencarian Tidak Ditemukan</h5>' +
                                '<p class="text-muted mb-0 cat-empty-desc">Tidak ada jenis tes yang cocok dengan filter tahun atau kata kunci Anda.</p>' +
                                '</div></div>';
                $('#seleksiList').append(emptyHtml);
            }
        } else {
            $('#noSearchInfo').remove();
        }
    }

    $('#searchInput').on('keyup input change', applyFilters);
    $('#filterTahun').on('change input', applyFilters);
    $('#catSort').on('change', applyFilters);

    // Initial Filter Execution (defaults to selected year)
    applyFilters();

    // Reload list dynamically
    function reloadSeleksiList() {
        $.get(window.location.href, function(data) {
            var newContent = $(data).find('#seleksiList').html();
            $('#seleksiList').html(newContent);
            applyFilters();
        });
    }

    // Bind Edit and Delete button clicks
    $(document).on('click', '.twx-edit-btn', function(e) {
        e.preventDefault();
        e.stopPropagation();
        var uid = $(this).data('uid');
        var name = $(this).data('nama') || $(this).data('name');
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

    // Form Submit Seleksi
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
        
        var postUrl = (window.AppConfig ? AppConfig.initGlobal : (typeof base_url !== 'undefined' ? base_url + '/' : '')) + 'store/save-data-seleksi-cat';
        $.ajax({
            url: postUrl,
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
                        reloadSeleksiList();
                    });
                } else {
                    Swal.fire('Error', response.message || 'Terjadi kesalahan', 'error');
                }
            },
            error: function(xhr) {
                var message = xhr.responseJSON && xhr.responseJSON.message
                    ? xhr.responseJSON.message
                    : 'Terjadi kesalahan sistem';
                Swal.fire('Error', message, 'error');
            }
        });
    });

    $('#SeleksiModal').on('show.bs.modal', function () {
        if (!$('#seleksi_key').val()) {
            var activeFilterYear = $('#filterTahun').val();
            var defaultYear = activeFilterYear || new Date().getFullYear();
            $('#periodeSeleksi').val(defaultYear);
        }
    });

    $('#SeleksiModal').on('hidden.bs.modal', function () {
        $('#formSeleksi')[0].reset();
        $('#seleksi_key').val('');
        $('#periodeSeleksi').val(new Date().getFullYear());
        $('#SeleksiModalLabel').text('Tambah Jenis Tes CAT');
        $('#btnSaveSeleksi').text('Simpan Jenis Tes');
        
        if ($('#jenisEventPicker').hasClass('select2-hidden-accessible')) {
            $('#jenisEventPicker').val('').trigger('change');
        }
    });
});

window.editSeleksi = function(uid, nama, jenis_id, periode) {
    $('#seleksi_key').val(uid);
    $('#periodeSeleksi').val(periode);
    $('#jenisEventPicker').val(jenis_id);
    
    if ($('#jenisEventPicker').hasClass('select2-hidden-accessible')) {
        $('#jenisEventPicker').trigger('change');
    }
    
    $('#SeleksiModalLabel').text('Edit Jenis Tes CAT');
    $('#btnSaveSeleksi').text('Perbarui Jenis Tes');
    var modalEl = document.getElementById('SeleksiModal');
    var myModal = bootstrap.Modal.getInstance(modalEl) || new bootstrap.Modal(modalEl);
    myModal.show();
};

window.deleteSeleksi = function(uid, name) {
    Swal.fire({
        title: 'Hapus Jenis Tes?',
        text: "Anda yakin ingin menghapus jenis tes " + name + "?",
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
            var delUrl = (window.AppConfig ? AppConfig.initGlobal : (typeof base_url !== 'undefined' ? base_url + '/' : '')) + 'kill/data-seleksi-cat';
            $.ajax({
                url: delUrl,
                type: 'POST',
                data: { key: uid },
                dataType: 'json',
                success: function(response) {
                    if (response.status) {
                        Swal.fire({
                            icon: 'success',
                            title: 'Terhapus!',
                            text: response.message,
                            timer: 1500,
                            showConfirmButton: false
                        }).then(() => {
                            $.get(window.location.href, function(data) {
                                var newContent = $(data).find('#seleksiList').html();
                                $('#seleksiList').html(newContent);
                                $('#searchInput').trigger('input');
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


