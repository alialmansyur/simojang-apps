$(document).on('click', '.selectType', function (e) {
    e.preventDefault();
    var tipe = $(this).data('type');
    $('.doc_type').val(tipe);
    console.log("Doc type terpilih:", tipe);
});

const inputElement = document.querySelector('.basic-filepond');
const pond = FilePond.create(inputElement, {
    credits: false,
    instantUpload: false,  
    allowMultiple: false,
    acceptedFileTypes: [
        'application/vnd.ms-excel',
        'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet'
    ],
    labelIdle: 'Hanya file Excel (.xls, .xlsx) <span class="filepond--label-action">Browse</span>',
    labelFileTypeNotAllowed: 'File hanya boleh Excel (.xls, .xlsx)',
    fileValidateTypeLabelExpectedTypes: 'Hanya file Excel (.xls, .xlsx) yang diperbolehkan',
    fileValidateTypeDetectType: (source, type) => new Promise((resolve) => resolve(type))
});

$(document).ready(function () {
    $('.sbmt').on('click', function (e) {
        e.preventDefault();
        const form = document.getElementById('UploadData');
        if (pond.getFiles().length === 0) {
            swlErrorHandler('Silakan pilih file Excel terlebih dahulu.');
            return;
        }

        const fd = new FormData(form);
        pond.getFiles().forEach((item, idx) => {
            fd.append('file', item.file, item.file.name);
        });

        swlwaitProsessing();

        $.ajax({
            url: AppConfig.initGlobal + 'store/import-takah-data', 
            type: 'POST',
            data: fd,
            processData: false,
            contentType: false,
            success: function (response) {
                if (response.status == 'error') {
                    swlErrorHandler(response.message);
                }else{
                    if (response) {
                        $('#dataTable').DataTable().ajax.reload();
                    }
                    pond.removeFiles();
                    form.reset();
                    $('#DataModal').modal('hide');
                    swlSuccess();
                }
            }
        });
    });

    $('#DataModal').on('hidden.bs.modal', function () {
        $('#UploadData')[0].reset();
        $('.modal-backdrop').remove();
        $('body').removeClass('modal-open').css('overflow', '');
    });
});