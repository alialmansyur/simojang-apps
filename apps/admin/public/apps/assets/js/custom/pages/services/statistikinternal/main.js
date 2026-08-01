$(document).ready(function () {

    $(document).on('shown.bs.modal', function (e) {
        const modal = $(e.target);

        modal.find('.select2-dynamic').each(function () {
            const el = $(this);
            const source = el.data('source');

            if (el.hasClass('select2-hidden-accessible')) {
                el.select2('destroy');
            }

            el.select2({
                theme: 'bootstrap-5',
                width: '100%',
                dropdownParent: el.closest('.modal'),
                minimumInputLength: 0,
                ajax: {
                    url: AppConfig.initGlobal + 'select2/list',
                    type: 'POST',
                    dataType: 'json',
                    delay: 300,
                    data: function (params) {
                        return {
                            search: params.term || '',
                            source: source
                        };
                    },
                    processResults: function (data) {
                        return { results: data };
                    },
                    cache: true
                }
            });
        });

    });

    $('#form-usulan').on('submit', function (e) {
        e.preventDefault();
        swlwaitProsessing();

        $.ajax({
            url: AppConfig.initGlobal + 'store/save-data-statistik-internal', 
            type: 'POST',
            data: $(this).serialize(),
            success: function (response) {
                if (response.status == 'error') {
                    swlErrorHandler(response.message);
                } else {
                    if (response) {
                        $('#dataTable').DataTable().ajax.reload(null, false);
                        if (typeof loadSummaryStatistikInternal === 'function') { loadSummaryStatistikInternal(); }
                        $('#DataModal').modal('hide');
                        swlSuccess();
                    }
                }
            }
        });
    }); 

});

$('#DataModal').on('hidden.bs.modal', function () {
    const form = $('#form-usulan');
    form[0].reset();
    form.find('.select2-dynamic').each(function () {
        $(this).val(null).trigger('change');
        if ($(this).hasClass("select2-hidden-accessible")) {
            $(this).select2('destroy');
        }
    });
    $('.modal-backdrop').remove();
    $('body').removeClass('modal-open').css('overflow', '');
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
