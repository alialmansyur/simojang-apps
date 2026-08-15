$(document).ready(function () {

    $(document).on('click', '.btn-submit-form', function () {
        $('#form-usulan').submit();
    });

    $('#form-usulan').on('submit', function (e) {
        e.preventDefault();
        $('#DataModal').modal('hide');
        
        const btnSubmit = $(this).find('.btn-submit-form');
        const originalText = btnSubmit.html();
        btnSubmit.prop('disabled', true).html('<span class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span>Menyimpan...');

        swlwaitProsessing();
        $.ajax({
            url: AppConfig.initGlobal + 'store/save-data-ikm', 
            type: 'POST',
            data: $(this).serialize(),
            success: function (response) {
                if (response.status == 'error') {
                    swlErrorHandler(response.message);
                    btnSubmit.prop('disabled', false).html(originalText);
                } else {
                    setTimeout(() => {
                        $('#dataTable').DataTable().ajax.reload(null, false);
                        if (typeof loadSummaryIKM === 'function') { loadSummaryIKM(); }
                        swlSuccess();
                    }, 300);
                    setTimeout(() => {
                        btnSubmit.prop('disabled', false).html(originalText);
                    }, 500);
                }
            },
            error: function () {
                swlErrorHandler('Terjadi kesalahan pada server.');
                btnSubmit.prop('disabled', false).html(originalText);
            }
        });
    });

    $('#DataModal').on('hidden.bs.modal', function () {
        const form = $('#form-usulan');
        form[0].reset();
        form.find('input[type="hidden"]').val('');
        $('#DataModalLabel').text('Tambah Data');
        $('.modal-backdrop').remove();
        $('body').removeClass('modal-open').css('overflow', '');
    });

});

function getCurrentDateTime() {
    const now = new Date();
    const dd = String(now.getDate()).padStart(2, '0');
    const mm = String(now.getMonth() + 1).padStart(2, '0');
    const yyyy = now.getFullYear();
    const hh = String(now.getHours()).padStart(2, '0');
    const ii = String(now.getMinutes()).padStart(2, '0');
    const ss = String(now.getSeconds()).padStart(2, '0');
    return `${dd}-${mm}-${yyyy} ${hh}:${ii}:${ss}`;
}
