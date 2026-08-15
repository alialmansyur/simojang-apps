$(document).ready(function () {

    $(document).on('click', '.btn-submit-form', function () {
        $('#form-usulan').submit();
    });

    $(document).on('shown.bs.modal', function (e) {
        const modal = $(e.target);

        modal.find('.select-naskah').each(function () {
            const $select = $(this);

            if ($select.hasClass("select2-hidden-accessible")) {
                $select.select2('destroy');
            }

            $select.select2({
                theme: 'bootstrap-5',
                width: '100%',
                dropdownParent: modal,
                allowClear: true,
                placeholder: 'Pilih Klasifikasi',
                ajax: {
                    url: AppConfig.initGlobal + 'naskah-list',
                    type: 'POST',
                    dataType: 'json',
                    delay: 300,
                    data: function (params) {
                        return {
                            search: params.term,
                            jenis_id: modal.find('[name="jenis"]').val() // ðŸ”¥ KUNCI
                        };
                    },
                    processResults: function (data) {
                        return { results: data };
                    }
                }
            });
        });
    });

    $(document).on('change', '[name="jenis"]', function () {
        const modal = $(this).closest('.modal');

        modal.find('.select-naskah')
            .val(null)
            .trigger('change');
    });

    $('#form-usulan').on('submit', function (e) {
        e.preventDefault();
        $('#DataModal').modal('hide');
        
        const btnSubmit = $(this).find('.btn-submit-form');
        const originalText = btnSubmit.html();
        btnSubmit.prop('disabled', true).html('<span class="spinner-border spinner-border-sm me-1" role="status" aria-hidden="true"></span>Loading...');
        swlwaitProsessing();
        
        $.ajax({
            url: AppConfig.initGlobal + 'store/save-data-surat', 
            type: 'POST',
            data: $(this).serialize(),
            success: function (response) {
                if (response.status == 'error') {
                    swlErrorHandler(response.message);
                    btnSubmit.prop('disabled', false).html(originalText);
                } else { 
                    if (response) {
                        $('#dataTable').DataTable().ajax.reload(null, false);
                        if (typeof loadSummarySurat === 'function') { loadSummarySurat(); }
                        swlSuccess();
                    }
                }
            },
            error: function () {
                swlErrorHandler('Terjadi kesalahan pada server saat memproses data.');
                btnSubmit.prop('disabled', false).html(originalText);
            }
        });
    });

    $('#DataModal').on('hidden.bs.modal', function () {
        const form = $('#form-usulan');
        form[0].reset();
        form.find('input[type="hidden"]').val('');
        $('#DataModalLabel').text('Tambah Data');
        $('.btn-submit-form').prop('disabled', false).text('Simpan Data');
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
