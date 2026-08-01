$(document).ready(function () {

    $(document).on('click', '.btn-submit-form', function () {
        $('#form-usulan').submit();
    });

    $('#form-usulan').on('submit', function (e) {
        e.preventDefault();
        swlwaitProsessing();
        $.ajax({
            url: AppConfig.initGlobal + 'store/save-data-ikm', 
            type: 'POST',
            data: $(this).serialize(),
            success: function (response) {
                if (response.status == 'error') {
                    swlErrorHandler(response.message);
                } else {
                    if (response) {
                        $('#dataTable').DataTable().ajax.reload(null, false);
                        if (typeof loadSummaryIKM === 'function') { loadSummaryIKM(); }
                        $('#DataModal').modal('hide');
                        swlSuccess();
                    }
                }
            }
        });
    });

    $('#DataModal').on('hidden.bs.modal', function () {
        const form = $('#form-usulan');
        form[0].reset();
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
