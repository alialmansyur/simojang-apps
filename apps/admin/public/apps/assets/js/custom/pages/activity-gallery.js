$(document).ready(function () {
    showSkeleton();
    loadGalleryData(true);

    // Event Listener untuk Filter & Search (Interactive/Real-time)
    $('#searchGallery').on('keyup', function () {
        loadGalleryData();
    });

    $('#filterTimKerja, #filterBulan').on('change', function () {
        loadGalleryData();
    });

    // Upload Photo Preview (Form Tambah Galeri)
    $('#inputFoto').on('change', function (e) {
        const file = e.target.files[0];
        if (file) {
            // Validasi ukuran (2MB max)
            if (file.size > 2 * 1024 * 1024) {
                alert('Ukuran file maksimal 2MB.');
                $(this).val('');
                return;
            }

            const reader = new FileReader();
            reader.onload = function(e) {
                $('#uploadPlaceholder').addClass('d-none');
                $('#uploadPreview').removeClass('d-none');
                $('#uploadPreview img').attr('src', e.target.result);
            }
            reader.readAsDataURL(file);
        }
    });

    // Hapus Photo Preview
    $('#btnRemovePhoto').on('click', function (e) {
        e.stopPropagation(); // Prevent triggering file input click
        $('#inputFoto').val('');
        $('#uploadPreview').addClass('d-none');
        $('#uploadPlaceholder').removeClass('d-none');
        $('#uploadPreview img').attr('src', '');
    });

    // Drag and drop UI feedback
    const uploadArea = $('#uploadArea');
    uploadArea.on('dragover', function(e) {
        e.preventDefault();
        e.preventDefault();
        e.stopPropagation();
        $inputFoto.val('');
        $preview.addClass('d-none');
        $placeholder.removeClass('d-none');
        $uploadArea.removeClass('border-primary').addClass('border-dashed');
    });

    // Reset Modal on Close
    $('#modalAddGallery').on('hidden.bs.modal', function () {
        $('#formAddGallery')[0].reset();
        $('#btnRemovePhoto').click();
        $('#modalAddGalleryLabel').text('Tambah Dokumentasi Kegiatan');
        $('#btnSaveGallery').text('Simpan Data');
        $('#formAddGallery').removeData('edit-id');
        $('#inputFoto').prop('required', true); // Require photo on new add
    });

    // Event Save Modal (Real AJAX)
    $('#btnSaveGallery').on('click', function () {
        const form = $('#formAddGallery')[0];
        if (!form.checkValidity()) {
            form.reportValidity();
            return;
        }

        const formData = new FormData(form);
        const editId = $('#formAddGallery').data('edit-id');
        if (editId) {
            formData.append('id', editId);
        }

        $('#modalAddGallery').modal('hide');

        if (typeof swlwaitProsessing === 'function') {
            swlwaitProsessing();
        } else {
            Swal.fire({
                title: 'Memproses...',
                text: 'Mohon tunggu sebentar',
                allowOutsideClick: false,
                didOpen: () => { Swal.showLoading() }
            });
        }

        $.ajax({
            url: window.location.origin + '/activity-gallery/store',
            type: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            dataType: 'json',
            success: function(response) {
                if(response.status === 'success') {
                    Swal.fire({
                        icon: 'success',
                        title: 'Berhasil',
                        text: response.message,
                        timer: 1500,
                        showConfirmButton: false
                    }).then(() => {
                        form.reset();
                        $('#btnRemovePhoto').click();
                        loadGalleryData();
                    });
                } else {
                    Swal.fire('Error', response.message, 'error');
                }
            },
            error: function() {
                Swal.fire('Error', 'Terjadi kesalahan sistem saat menyimpan data', 'error');
            }
        });
    });

    // Event View Gallery
    $(document).on('click', '.view-gallery-btn', function (e) {
        if($(e.target).closest('button').length) return; // ignore if clicking action buttons

        const img = $(this).data('img');
        const title = $(this).data('title');
        const team = $(this).data('team');
        const date = $(this).data('date');
        const desc = $(this).data('desc');

        $('#viewGalleryImg').attr('src', img);
        $('#viewGalleryTitle').text(title);
        $('#viewGalleryTeam').text(team);
        $('#viewGalleryDate').html(`<i class="bi bi-calendar3"></i> ${date}`);
        $('#viewGalleryDesc').text(desc);

        $('#modalViewGallery').modal('show');
    });

    // Event Delete Gallery (Real AJAX)
    $(document).on('click', '.btn-delete-gallery', function(e) {
        e.preventDefault();
        e.stopPropagation();
        
        const id = $(this).data('id');
        
        Swal.fire({
            title: 'Hapus Kegiatan?',
            text: "Anda yakin ingin menghapus dokumentasi kegiatan ini?",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            cancelButtonColor: '#6e7d88',
            confirmButtonText: 'Ya, Hapus!',
            cancelButtonText: 'Batal'
        }).then((result) => {
            if (result.isConfirmed) {
                if (typeof swlwaitProsessing === 'function') swlwaitProsessing();
                else Swal.fire({ title: 'Memproses...', allowOutsideClick: false, didOpen: () => { Swal.showLoading() } });
                
                $.ajax({
                    url: window.location.origin + '/activity-gallery/delete',
                    type: 'POST',
                    data: { id: id },
                    dataType: 'json',
                    success: function(response) {
                        if(response.status === 'success'){
                            Swal.fire('Terhapus!', response.message, 'success').then(() => {
                                loadGalleryData();
                            });
                        } else {
                            Swal.fire('Error', response.message, 'error');
                        }
                    },
                    error: function() {
                        Swal.fire('Error', 'Gagal menghapus data', 'error');
                    }
                });
            }
        });
    });

    // Event Edit Gallery (Real AJAX Prep)
    $(document).on('click', '.btn-edit-gallery', function(e) {
        e.preventDefault();
        e.stopPropagation();
        
        const id = $(this).data('id');
        const card = $(this).closest('.gallery-card');
        
        $('#inputTimKerja').val(card.data('team-id'));
        $('#inputTanggal').val(card.data('raw-date'));
        $('#inputJudul').val(card.data('title'));
        $('#inputDeskripsi').val(card.data('desc'));
        
        // Remove required attribute from photo input for editing
        $('#inputFoto').prop('required', false);

        $('#modalAddGalleryLabel').text('Edit Dokumentasi Kegiatan');
        $('#btnSaveGallery').text('Simpan Perubahan');
        
        // Save mode state
        $('#formAddGallery').data('edit-id', id);

        $('#modalAddGallery').modal('show');
    });
});

function triggerStaggeredAnimation() {
    $('.gallery-item-wrapper:visible').each(function(index) {
        var $card = $(this).find('.gallery-card');
        $card.css('animation', 'none');
        void this.offsetWidth; // Force reflow
        $card.css({
            'animation': 'twSlideFadeUp 0.5s cubic-bezier(0.175, 0.885, 0.32, 1.275) forwards',
            'animation-delay': (index * 0.06) + 's',
            'opacity': '0'
        });
    });
}

function showSkeleton() {
    if (typeof showLoading === 'function') {
        showLoading('Memproses data...');
    }
}

let searchTimer;
function loadGalleryData(isInitial = false) {
    clearTimeout(searchTimer);
    
    if (!isInitial) {
        $('.tw-search-spinner').removeClass('d-none');
    }
    
    // Use debounce for searching
    searchTimer = setTimeout(() => {
        if (isInitial) {
            showSkeleton();
        }

        // Get filter values
        const searchVal = ($('#searchGallery').val() || '').toLowerCase();
        const filterTeam = $('#filterTimKerja').val();
        const filterMonth = $('#filterBulan').val(); // format: YYYY-MM

        $.ajax({
            url: window.location.origin + '/activity-gallery/get-data',
            type: 'POST',
            data: {
                search: searchVal,
                timkerja: filterTeam,
                bulan: filterMonth
            },
            dataType: 'json',
            success: function(response) {
                $('.tw-search-spinner').addClass('d-none');
                if (isInitial && typeof hideLoading === 'function') hideLoading();
                
                if (response.status === 'success') {
                    renderGallery(response.data);
                } else {
                    Swal.fire('Error', 'Gagal memuat data galeri', 'error');
                }
            },
            error: function() {
                $('.tw-search-spinner').addClass('d-none');
                if (isInitial && typeof hideLoading === 'function') hideLoading();
                
                // Ignore error visual handling for seamless experience, just render empty or keep old
                renderGallery([]);
            }
        });

    }, 300); // 300ms debounce
}

function renderGallery(data) {
    const $loading = $('#galleryLoadingState');
    const $grid = $('#galleryGrid');
    const $empty = $('#galleryEmptyState');
    const $carousel = $('#galleryCarousel');

    $loading.addClass('d-none');
    $grid.empty();

    if (!data || data.length === 0) {
        $empty.removeClass('d-none');
        $carousel.addClass('d-none');
        return;
    }

    $empty.addClass('d-none');
    $carousel.removeClass('d-none');
    $grid.removeClass('d-none');

    data.forEach((item) => {
        const html = `
            <div class="col-12 col-md-6 col-lg-4 col-xl-3 gallery-item-wrapper" data-id="${item.id}">
                <div class="gallery-card view-gallery-btn" 
                     data-img="${item.img}" 
                     data-title="${item.title}" 
                     data-team="${item.team_name}" 
                     data-team-id="${item.team_id}" 
                     data-date="${item.date_formatted}"
                     data-raw-date="${item.date}"
                     data-desc="${item.desc}">
                     
                    <div class="gallery-card-img-wrapper">
                        <img src="${item.img}" alt="${item.title}" class="gallery-card-img" loading="lazy">
                        <div class="gallery-card-overlay">
                            <i class="bi bi-zoom-in gallery-card-overlay-icon"></i>
                        </div>
                    </div>
                    
                    <div class="gallery-card-body">
                        <div class="d-flex justify-content-between align-items-start mb-1">
                            <span class="gallery-card-team">${item.team_name}</span>
                            <div class="d-flex gap-2 position-relative z-3" style="margin-top:-2px;">
                                <button type="button" class="btn btn-sm btn-link p-0 text-decoration-none btn-edit-gallery" style="color: #94a3b8;" data-id="${item.id}" title="Edit"><i class="bi bi-pencil-square" style="font-size: 1.05rem;"></i></button>
                                <button type="button" class="btn btn-sm btn-link p-0 text-decoration-none btn-delete-gallery" style="color: #94a3b8;" data-id="${item.id}" title="Hapus"><i class="bi bi-trash3" style="font-size: 1.05rem;"></i></button>
                            </div>
                        </div>
                        <h5 class="gallery-card-title">${item.title}</h5>
                        
                        <div class="gallery-card-footer mt-auto">
                            <div class="gallery-card-date">
                                <i class="bi bi-calendar-event"></i>
                                <span>${item.date_formatted}</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        `;
        $grid.append(html);
    });

    triggerStaggeredAnimation();
}
