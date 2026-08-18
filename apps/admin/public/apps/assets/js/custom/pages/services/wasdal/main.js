$(document).ready(function () {
    $(document).on('shown.bs.modal', function (e) {
        const modal = $(e.target);

        modal.find('.select-instansi').each(function () {
            if ($(this).hasClass("select2-hidden-accessible")) {
                $(this).select2('destroy');
            }

            $(this).select2({
                theme: 'bootstrap-5',
                width: '100%',
                dropdownParent: modal,
                minimumInputLength: 0,
                ajax: {
                    url: AppConfig.initGlobal + 'instansi-list',
                    type: 'POST',
                    dataType: 'json',
                    delay: 300,
                    data: function (params) {
                        return {
                            search: params.term 
                        };
                    },
                    processResults: function (data) {
                        return {
                            results: data
                        };
                    },
                    cache: true
                }
            });
        });
    });

    $(document).on('click', '.btn-submit-form', function () {
        $('#form-usulan').submit();
    });

    $('#form-usulan').on('submit', function (e) {
        e.preventDefault();
        swlwaitProsessing();
        const submitBtn = $('.btn-submit-form');
        submitBtn.prop('disabled', true);
        
        $.ajax({
            url: AppConfig.initGlobal + 'store/save-data-wasdal',
            type: 'POST',
            data: $(this).serialize(),
            success: function (response) {
                if (response.status == 'error') {
                    swlErrorHandler(response.message);
                } else {
                    if (response) {
                        $('#dataTable').DataTable().ajax.reload();
                        $('#DataModal').modal('hide');
                        if (typeof loadSummary === 'function') {
                            loadSummary();
                        }
                        swlSuccess();
                    }
                }
            },
            error: function (xhr) {
                const message = xhr.responseJSON && xhr.responseJSON.message
                    ? xhr.responseJSON.message
                    : 'Gagal menyimpan data.';
                swlErrorHandler(message);
            },
            complete: function () {
                submitBtn.prop('disabled', false);
            }
        });
    });

    $('#DataModal').on('hidden.bs.modal', function () {
        const form = $('#form-usulan');
        form[0].reset();
        form.find('input[type="hidden"]').val('');
        $('#DataModalLabel').text('Tambah Data');

        form.find('.select-instansi').each(function () {
            $(this).val(null).trigger('change');
            if ($(this).hasClass('select2-hidden-accessible')) {
                $(this).select2('destroy');
            }
        });
        
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
