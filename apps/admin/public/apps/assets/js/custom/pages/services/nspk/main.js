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

        modal.find('.select-step').each(function () {
            if ($(this).hasClass("select2-hidden-accessible")) {
                $(this).select2('destroy');
            }

            $(this).select2({
                theme: 'bootstrap-5',
                width: '100%',
                dropdownParent: modal
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
            url: AppConfig.initGlobal + 'store/save-data-nspk',
            type: 'POST',
            dataType: 'json',
            data: $(this).serialize(),
            success(response) {
                if (response.status === 'error') {
                    swlErrorHandler(response.message);
                    return;
                }

                $('#dataTable').DataTable().ajax.reload(null, false);
                if (typeof loadNspkSummary === 'function') {
                    loadNspkSummary();
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
        form.find('input[type="hidden"]').val('');
        $('#DataModalLabel').text('Tambah Data');

        form.find('.select-instansi, .select-step').each(function () {
            $(this).val(null).trigger('change');
            if ($(this).hasClass('select2-hidden-accessible')) {
                $(this).select2('destroy');
            }
        });
        
        $('.modal-backdrop').remove();
        $('body').removeClass('modal-open').css('overflow', '');
    });
});
