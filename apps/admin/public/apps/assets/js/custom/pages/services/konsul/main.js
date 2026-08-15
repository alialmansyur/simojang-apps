$(document).ready(function () {

    $(document).on('click', '.btn-submit-form', function () {
        $('#form-usulan').submit();
    });

    $('#form-usulan').on('submit', function (e) {
        e.preventDefault(); 
        $('#DataModal').modal('hide');
        swlwaitProsessing();
        const $btn = $('.btn-submit-form');
        $btn.prop('disabled', true);
        
        $.ajax({
            url: AppConfig.initGlobal + 'store/save-data-konsultasi', 
            type: 'POST',
            data: $(this).serialize(),
            success: function (response) {
                $btn.prop('disabled', false);
                if (response?.status === 'error' || response?.status === false) {
                    swlErrorHandler(response?.message || 'Terjadi kesalahan.');
                } else {
                    setTimeout(() => {
                        swlSuccess(response?.message || 'Data berhasil disimpan.');
                        $('#dataTable').DataTable().ajax.reload(null, false);
                        if (typeof loadSummaryKonsul === 'function') { loadSummaryKonsul(); }
                    }, 300);
                }
            },
            error: function () {
                $btn.prop('disabled', false);
                swlErrorHandler('Terjadi kesalahan pada server.');
            }
        });
    });

    // Reset form properly when opening modal for Tambah Data
    $('[data-bs-target="#DataModal"]').on('click', function () {
        const form = $('#form-usulan');
        form[0].reset();
        form.find('[name="key"]').val(''); 
        form.find('[name="media"]').val('').trigger('change');
        $('#DataModalLabel').text('Tambah Data');
    });

    $('#DataModal').on('hidden.bs.modal', function () {
        const form = $('#form-usulan');
        form[0].reset();
        form.find('[name="key"]').val('');
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
