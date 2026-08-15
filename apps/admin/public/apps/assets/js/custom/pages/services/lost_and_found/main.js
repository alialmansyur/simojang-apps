$(document).ready(function () {
    const processingState = (window.ServiceTableUI && ServiceTableUI.createProcessingState)
        ? ServiceTableUI.createProcessingState('Memuat data...')
        : '<div class="text-center text-muted py-4">Memuat data...</div>';

    const bulanList = [
        { val: '01', text: 'Januari' }, { val: '02', text: 'Februari' }, { val: '03', text: 'Maret' },
        { val: '04', text: 'April' }, { val: '05', text: 'Mei' }, { val: '06', text: 'Juni' },
        { val: '07', text: 'Juli' }, { val: '08', text: 'Agustus' }, { val: '09', text: 'September' },
        { val: '10', text: 'Oktober' }, { val: '11', text: 'November' }, { val: '12', text: 'Desember' }
    ];

    const bulanContainer = document.getElementById('bulanList');
    if (bulanContainer) {
        bulanContainer.innerHTML = '';
        bulanList.forEach((bulan) => {
            bulanContainer.insertAdjacentHTML('beforeend', `
                <li>
                    <div class="form-check py-1">
                        <input class="form-check-input bulan-check" type="checkbox" value="${bulan.val}" id="bulan${bulan.val}">
                        <label class="form-check-label fw-semibold" for="bulan${bulan.val}">${bulan.text}</label>
                    </div>
                </li>
            `);
        });
    }

    window.previewImage = function(url) {
        $('#previewModalImage').attr('src', url);
        $('#ImagePreviewModal').modal('show');
    };

    let isProcessing = false;
    let selectedBulan = [];

    function updateShownLostAndFound() {
        const info = table.page.info();
        $('#laf-data-shown').text(ServiceTableUI.formatNumber((info && info.recordsDisplay) || 0));
    }

    function loadSummaryLostAndFound() {
        $.ajax({
            url: AppConfig.initGlobal + 'fetch/summary-lost-and-found',
            type: 'POST',
            dataType: 'json',
            data: { bulan: selectedBulan },
            success: function (response) {
                const s = response?.summary || {};
                $('#laf-total-data').text(ServiceTableUI.formatNumber(s.total_data || 0));
                $('#laf-sudah-diserahkan').text(ServiceTableUI.formatNumber(s.total_diserahkan || 0));
                $('#laf-belum-diserahkan').text(ServiceTableUI.formatNumber(s.total_belum || 0));
                $('#laf-bulan-dipilih').text(ServiceTableUI.formatNumber(selectedBulan.length || 0));
                $('#laf-last-update').text(ServiceTableUI.formatDateTime(s.last_update));
            }
        });
    }

    // DataTable Initialization
    const table = $('#dataTable').DataTable({
        responsive: { details: { type: 'column', target: 'td.dtr-control' } },
        processing: true,
        serverSide: true,
        order: [[0, 'desc']], // order by ID desc
        buttons: ['copy', 'excel', 'pdf', 'print'],
        ajax: {
            url: AppConfig.initGlobal + 'fetch/data-lost-and-found',
            type: 'POST',
            data: function (d) {
                d.bulan = selectedBulan;
                return d;
            }
        },
        columns: [
            { data: 'id', className: 'text-center' },
            { 
                data: 'nama_barang',
                render: function(data, type, row) {
                    let text = `<strong>${data}</strong>`;
                    if (row.gambar) {
                        const imgUrl = AppConfig.initGlobal.replace('/index.php/', '/') + row.gambar;
                        text += `<br><a href="javascript:void(0)" onclick="previewImage('${imgUrl}')" class="badge bg-primary text-decoration-none mt-1"><i class="bi bi-image"></i> Lihat Foto</a>`;
                    }
                    return text;
                }
            },
            { data: 'tanggal_ditemukan', className: 'text-center', defaultContent: '-' },
            { data: 'lokasi_ditemukan', defaultContent: '-' },
            { 
                data: 'status_penyerahan', 
                className: 'text-center',
                render: function(data, type, row) {
                    if (data === 'Diserahkan') {
                        return '<span class="badge bg-success">Diserahkan</span>';
                    }
                    return '<span class="badge bg-warning text-dark">Belum Diserahkan</span>';
                }
            },
            { data: 'tanggal_diserahkan', className: 'text-center', defaultContent: '-' },
            { data: 'penerima', defaultContent: '-' },
            { data: 'keterangan', defaultContent: '-' },
            {
                data: null,
                orderable: false,
                searchable: false,
                render: function (_, __, row) {
                    return `
                        <button class="btn btn-sm btn-primary btn-update" data-id="${row.id}"><i class='bi bi-pencil'></i></button>
                        <button class="btn btn-sm btn-danger btn-remove" data-id="${row.id}"><i class='bi bi-trash'></i></button>
                    `;
                }
            }
        ],
        language: {
            emptyTable: (window.ServiceTableUI ? ServiceTableUI.createEmptyState() : 'Tidak ada data'),
            zeroRecords: (window.ServiceTableUI ? ServiceTableUI.createEmptyState() : 'Tidak ada data'),
            processing: processingState
        },
        initComplete: function () {
            if (window.ServiceTableUI) {
                ServiceTableUI.setup({
                    key: 'laf',
                    table,

                    loadSummary: loadSummaryLostAndFound,
                    cards: [
                        { id: 'total-data', label: 'Total Barang', value: '0' },
                        { id: 'belum-diserahkan', label: 'Belum Diserahkan', value: '0' },
                        { id: 'sudah-diserahkan', label: 'Sudah Diserahkan', value: '0' },
                        { id: 'bulan-dipilih', label: 'Bulan Dipilih', value: '0' },
                        { id: 'data-shown', label: 'Data Ditampilkan', value: '0' },
                        { id: 'last-update', label: 'Update Terakhir', value: '-' }
                    ]
                });
            }
            updateShownLostAndFound();
            loadSummaryLostAndFound();
        }
    });

    table.on('draw.dt', updateShownLostAndFound);

    const MAX_BULAN = 2;
    $(document).on('change', '.bulan-check', function () {
        const checked = $('.bulan-check:checked');
        if (checked.length > MAX_BULAN) {
            this.checked = false;
            swlErrorHandler('Riwayat ditampilkan maksimal 2 bulan.');
        }
    });

    $('#applyBulan').on('click', function () {
        selectedBulan = $('.bulan-check:checked').map(function () { return this.value; }).get();
        if (selectedBulan.length > 2) {
            swlErrorHandler('Silakan pilih maksimal 2 bulan saja.');
            return;
        }

        if (selectedBulan.length) {
            const namaBulan = bulanList.filter((b) => selectedBulan.includes(b.val)).map((b) => b.text.substring(0, 3));
            $('#dropdownBulan').text(namaBulan.join(', '));
        } else {
            $('#dropdownBulan').text('Pilih Bulan');
        }

        updateActiveFiltersLabel();
        table.ajax.reload();
        loadSummaryLostAndFound();
    });

    function updateActiveFiltersLabel() {
        const $container = $('#activeFilterContainer');
        const $list = $container.find('.active-filters-list');
        $list.empty();
        
        let hasFilters = false;

        if (selectedBulan.length > 0) {
            hasFilters = true;
            const labels = bulanList
                .filter(b => selectedBulan.includes(b.val))
                .map(b => b.text);
            
            $list.append(`<span class="badge bg-light text-primary border border-primary mb-1 filter-badge" style="font-weight: 500;">Bulan: ${labels.join(', ')}</span>`);
        }

        if (hasFilters) {
            $container.addClass('d-flex').show();
        } else {
            $container.removeClass('d-flex').hide();
        }
    }

    let selectedFile = null;



    $('.js-service-reload').on('click', function() {
        table.ajax.reload(null, false);
    });

    $('#btnAddData').on('click', function() {
        const form = $('#form-lostfound');
        form[0].reset();
        form.find('[name="key"]').val('');
        resetDropzone();
        $('#DataModalLabel').text('Tambah Barang Hilang');
        $('#status_penyerahan').trigger('change');
        $('#DataModal').modal('show');
    });

    // Toggle fields based on status
    $('#status_penyerahan').on('change', function() {
        if ($(this).val() === 'Diserahkan') {
            $('.section-diserahkan').show();
            $('#tanggal_diserahkan').prop('required', true);
            $('#penerima').prop('required', true);
        } else {
            $('.section-diserahkan').hide();
            $('#tanggal_diserahkan').prop('required', false).val('');
            $('#penerima').prop('required', false).val('');
        }
    });

    // Drag & Drop Implementation
    const dropzoneArea = document.getElementById('dropzoneArea');
    const gambarUpload = document.getElementById('gambarUpload');
    const btnBrowse = document.getElementById('btnBrowse');
    const filePreview = document.getElementById('filePreview');
    const fileNameDisplay = document.getElementById('fileName');
    const fileSizeDisplay = document.getElementById('fileSize');
    const btnRemoveFile = document.getElementById('btnRemoveFile');

    if (dropzoneArea) {
        ['dragenter', 'dragover', 'dragleave', 'drop'].forEach(eventName => {
            dropzoneArea.addEventListener(eventName, preventDefaults, false);
            document.body.addEventListener(eventName, preventDefaults, false);
        });

        function preventDefaults(e) {
            e.preventDefault();
            e.stopPropagation();
        }

        ['dragenter', 'dragover'].forEach(eventName => {
            dropzoneArea.addEventListener(eventName, highlight, false);
        });

        ['dragleave', 'drop'].forEach(eventName => {
            dropzoneArea.addEventListener(eventName, unhighlight, false);
        });

        function highlight(e) {
            dropzoneArea.style.backgroundColor = '#f8f9fa';
            dropzoneArea.style.borderColor = '#0a58ca';
        }

        function unhighlight(e) {
            dropzoneArea.style.backgroundColor = 'transparent';
            dropzoneArea.style.borderColor = '#0d6efd';
        }

        dropzoneArea.addEventListener('drop', handleDrop, false);

        function handleDrop(e) {
            const dt = e.dataTransfer;
            const files = dt.files;
            handleFiles(files);
        }

        btnBrowse.addEventListener('click', () => {
            gambarUpload.click();
        });

        gambarUpload.addEventListener('change', function() {
            handleFiles(this.files);
        });

        function handleFiles(files) {
            if (files.length === 0) return;
            
            const file = files[0];
            const validTypes = ['image/jpeg', 'image/png', 'image/webp'];
            
            if (!validTypes.includes(file.type)) {
                swlErrorHandler('Hanya file gambar (.jpg, .png, .webp) yang diperbolehkan');
                return;
            }

            if (file.size > 2 * 1024 * 1024) {
                swlErrorHandler('Ukuran gambar maksimal 2MB.');
                return;
            }

            selectedFile = file;
            showFilePreview(file);
        }

        function showFilePreview(file) {
            fileNameDisplay.textContent = file.name;
            fileSizeDisplay.textContent = formatBytes(file.size);
            filePreview.classList.remove('d-none');
            btnBrowse.style.display = 'none';
            dropzoneArea.querySelector('h5').style.display = 'none';
            dropzoneArea.querySelector('p').style.display = 'none';
            dropzoneArea.querySelectorAll('p.text-muted').forEach(p => p.style.display = 'none');
        }

        function formatBytes(bytes, decimals = 2) {
            if (bytes === 0) return '0 Bytes';
            const k = 1024;
            const dm = decimals < 0 ? 0 : decimals;
            const sizes = ['Bytes', 'KB', 'MB', 'GB', 'TB', 'PB', 'EB', 'ZB', 'YB'];
            const i = Math.floor(Math.log(bytes) / Math.log(k));
            return parseFloat((bytes / Math.pow(k, i)).toFixed(dm)) + ' ' + sizes[i];
        }

        btnRemoveFile.addEventListener('click', (e) => {
            e.stopPropagation();
            resetDropzone();
        });
    }

    function resetDropzone() {
        if (!dropzoneArea) return;
        selectedFile = null;
        gambarUpload.value = '';
        filePreview.classList.add('d-none');
        btnBrowse.style.display = 'inline-block';
        dropzoneArea.querySelector('h5').style.display = 'block';
        dropzoneArea.querySelectorAll('p.text-muted').forEach(p => p.style.display = 'block');
    }

    $('#btnSubmitForm').on('click', function () {
        $('#form-lostfound').submit();
    });

    $('#form-lostfound').on('submit', function (e) {
        e.preventDefault();
        
        const btnSubmit = $('#btnSubmitForm');
        btnSubmit.prop('disabled', true);
        
        isProcessing = true;
        $('#DataModal').modal('hide');
        swlwaitProsessing();
        
        // Use FormData for file upload support
        const formData = new FormData(this);
        if (selectedFile) {
            formData.set('gambar', selectedFile, selectedFile.name);
        }

        $.ajax({
            url: AppConfig.initGlobal + 'store/save-data-lost-and-found', 
            type: 'POST',
            data: formData,
            contentType: false,
            processData: false,
            success: function (response) {
                isProcessing = false;
                btnSubmit.prop('disabled', false);
                if (response.status == 'error') {
                    swlErrorHandler(response.message);
                    $('#DataModal').modal('show');
                } else {
                    if (response) {
                        swlSuccess();
                        table.ajax.reload(null, false);
                        // Trigger manual reset since we skipped it in hidden.bs.modal
                        const form = $('#form-lostfound');
                        form[0].reset();
                        form.find('[name="key"]').val('');
                        resetDropzone();
                        $('.modal-backdrop').remove();
                        $('body').removeClass('modal-open').css('overflow', '');
                    }
                }
            },
            error: function() {
                isProcessing = false;
                btnSubmit.prop('disabled', false);
                swlErrorHandler('Terjadi kesalahan saat memproses data.');
                $('#DataModal').modal('show');
            }
        });
    });

    $('#dataTable tbody').on('click', '.btn-remove', function () {
        const key = $(this).data('id');

        Swal.fire({
            text: 'Apa anda yakin akan menghapus data ini?',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d63031',
            confirmButtonText: 'Ya',
            cancelButtonText: 'Tidak'
        }).then((result) => {
            if (!result.isConfirmed) return;

            $.ajax({
                type: 'POST',
                url: AppConfig.initGlobal + 'kill/data-lost-and-found',
                data: { key },
                dataType: 'json',
                success: function (res) {
                    if (!res?.status) return;
                    swlSuccess();
                    table.ajax.reload(null, false);
                }
            });
        });
    });

    $('#dataTable tbody').on('click', '.btn-update', function () {
        let tr = $(this).closest('tr');
        if (tr.hasClass('child')) tr = tr.prev('.parent');

        const row = table.row(tr).data();
        if (!row) return;

        const form = $('#form-lostfound');
        form[0].reset(); // clear file input
        form.find('[name="key"]').val('');
        resetDropzone();
        $('#DataModalLabel').text('Update Data Barang');

        form.find('[name="key"]').val(row.id);
        form.find('[name="nama_barang"]').val(row.nama_barang || '');
        form.find('[name="lokasi_ditemukan"]').val(row.lokasi_ditemukan || '');
        form.find('[name="tanggal_ditemukan"]').val(row.tanggal_ditemukan || '');
        form.find('[name="status_penyerahan"]').val(row.status_penyerahan || 'Belum Diserahkan');
        form.find('[name="tanggal_diserahkan"]').val(row.tanggal_diserahkan || '');
        form.find('[name="penerima"]').val(row.penerima || '');
        form.find('[name="keterangan"]').val(row.keterangan || '');

        $('#status_penyerahan').trigger('change');
        $('#DataModal').modal('show');
    });

    $('#DataModal').on('hidden.bs.modal', function () {
        if (isProcessing) return;
        
        const form = $('#form-lostfound');
        form[0].reset();
        form.find('[name="key"]').val('');
        resetDropzone();
        $('.modal-backdrop').remove();
        $('body').removeClass('modal-open').css('overflow', '');
    });
});
