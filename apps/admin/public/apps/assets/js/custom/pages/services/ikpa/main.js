$(document).on('click', '.selectType', function (e) {
    e.preventDefault();
    var tipe = $(this).data('type');
    $('.doc_type').val(tipe);
    console.log("Doc type terpilih:", tipe);
});

let selectedFile = null;

$(document).ready(function () {
    const dropzoneArea = document.getElementById('dropzoneArea');
    const excelUpload = document.getElementById('excelUpload');
    const btnBrowse = document.getElementById('btnBrowse');
    const filePreview = document.getElementById('filePreview');
    const fileNameDisplay = document.getElementById('fileName');
    const fileSizeDisplay = document.getElementById('fileSize');
    const btnRemoveFile = document.getElementById('btnRemoveFile');
    const form = document.getElementById('UploadData');

    // Prevent default drag behaviors
    ['dragenter', 'dragover', 'dragleave', 'drop'].forEach(eventName => {
        dropzoneArea.addEventListener(eventName, preventDefaults, false);
        document.body.addEventListener(eventName, preventDefaults, false);
    });

    function preventDefaults(e) {
        e.preventDefault();
        e.stopPropagation();
    }

    // Highlight dropzone when item is dragged over it
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
        dropzoneArea.style.borderColor = '#1040c1';
    }

    // Handle dropped files
    dropzoneArea.addEventListener('drop', handleDrop, false);

    function handleDrop(e) {
        const dt = e.dataTransfer;
        const files = dt.files;
        handleFiles(files);
    }

    // Handle browse button click
    btnBrowse.addEventListener('click', () => {
        excelUpload.click();
    });

    // Handle file input change
    excelUpload.addEventListener('change', function() {
        handleFiles(this.files);
    });

    function handleFiles(files) {
        if (files.length === 0) return;
        
        const file = files[0];
        const validExtensions = ['application/vnd.ms-excel', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet'];
        const validNames = /\.(xls|xlsx)$/i;

        if (!validExtensions.includes(file.type) && !validNames.test(file.name)) {
            swlErrorHandler('Hanya file Excel (.xls, .xlsx) yang diperbolehkan');
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

    function resetDropzone() {
        selectedFile = null;
        excelUpload.value = '';
        filePreview.classList.add('d-none');
        btnBrowse.style.display = 'inline-block';
        dropzoneArea.querySelector('h5').style.display = 'block';
        dropzoneArea.querySelectorAll('p.text-muted').forEach(p => p.style.display = 'block');
    }

    $('.sbmt').on('click', function (e) {
        e.preventDefault();
        
        if (!selectedFile) {
            swlErrorHandler('Silakan pilih file Excel terlebih dahulu.');
            return;
        }

        const fd = new FormData(form);
        fd.append('file', selectedFile, selectedFile.name);

        $('#DataModal').modal('hide');
        swlwaitProsessing();

        $.ajax({
            url: AppConfig.initGlobal + 'store/import-ikpa-data', 
            type: 'POST',
            data: fd,
            processData: false,
            contentType: false,
            success: function (response) {
                if (response.status == 'error') {
                    swlErrorHandler(response.message);
                } else {
                    if (response) {
                        $('#dataTable').DataTable().ajax.reload(null, false);
                        if (typeof loadSummaryIKPA === 'function') { loadSummaryIKPA(); }
                    }
                    resetDropzone();
                    form.reset();
                    swlSuccess();
                }
            },
            error: function () {
                swlErrorHandler('Terjadi kesalahan pada server saat memproses file.');
            }
        });
    });

    $('#DataModal').on('hidden.bs.modal', function () {
        form.reset();
        resetDropzone();
        $('.modal-backdrop').remove();
        $('body').removeClass('modal-open').css('overflow', '');
    });
});
