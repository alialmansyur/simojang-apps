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
                            search: params.term // ⬅️ keyword search
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
                dropdownParent: modal,
                ajax: {
                    url: AppConfig.initGlobal + 'step-mt-list',
                    type: 'POST',
                    dataType: 'json',
                    processResults: data => ({ results: data }),
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
        
        // Hide modal first to avoid overlapping with sweetalert loader
        $('#DataModal').modal('hide');
        swlwaitProsessing();
        
        $.ajax({
            url: AppConfig.initGlobal + 'store/add-mt',
            type: 'POST',
            data: $(this).serialize(),
            success: function (response) {
                console.log(response);
                if (response.status == 'error') {
                    swlErrorHandler(response.message);
                } else {
                    if (response) {
                        if ($.fn.DataTable.isDataTable('#dataTable')) {
                            $('#dataTable').DataTable().ajax.reload(null, false);
                        }
                        swlSuccess();
                    }
                }
            },
            error: function (xhr) {
                swlErrorHandler('Gagal menyimpan data.');
            }
        });
    });

    $('#DataModal').on('hidden.bs.modal', function () {
        const form = $('#form-usulan');
        form[0].reset();
        form.find('.select-instansi, .select-step').each(function () {
            $(this).val(null).trigger('change');
            if ($(this).hasClass("select2-hidden-accessible")) {
                $(this).select2('destroy');
            }
        });
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