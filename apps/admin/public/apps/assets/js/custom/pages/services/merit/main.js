$(document).ready(function () {
    $(document).on('click', '.btn-submit-form', function () {
        $('#form-usulan').submit();
    });

    $('#form-usulan').on('submit', function (e) {
        e.preventDefault();
        swlwaitProsessing();

        const submitBtn = $('.btn-submit-form');
        submitBtn.prop('disabled', true);

        $.ajax({
            url: AppConfig.initGlobal + 'store/save-data-merit',
            type: 'POST',
            dataType: 'json',
            data: $(this).serialize(),
            success(response) {
                if (response.status === 'error') {
                    swlErrorHandler(response.message);
                    return;
                }

                $('#dataTable').DataTable().ajax.reload(null, false);
                if (typeof loadMeritSummary === 'function') {
                    loadMeritSummary();
                }
                $('#DataModal').modal('hide');
                swlSuccess();
            },
            error(xhr) {
                const message = xhr.responseJSON && xhr.responseJSON.message
                    ? xhr.responseJSON.message
                    : 'Gagal menyimpan data.';
                swlErrorHandler(message);
            },
            complete() {
                submitBtn.prop('disabled', false);
            }
        });
    });

    $('#DataModal').on('hidden.bs.modal', function () {
        const form = $('#form-usulan');
        form[0].reset();
        $('#DataModalLabel').text('Tambah Data');
        $('.modal-backdrop').remove();
        $('body').removeClass('modal-open').css('overflow', '');
    });
});
