$(document).ready(function () {

    $('[data-bs-target="#DataModal"]').on('click', function() {
        $('#DataModalLabel').text('Tambah Data');
        $('#form-usulan')[0].reset();
        $('#form-usulan').find('[name="key"]').val('');
    });

    $(document).on('click', '.btn-submit-form', function () {
        $('#form-usulan').submit();
    });

    $('#form-usulan').on('submit', function (e) {
        e.preventDefault();

        let formData = $(this).serializeArray();
        formData.forEach(function(item) {
            if (['contens', 'followers', 'viewers'].includes(item.name)) {
                item.value = item.value.replace(/,/g, '');
            }
        });

        let data = $.param(formData);
        let btnSubmit = $('.btn-submit-form');
        let originalText = btnSubmit.html();
        
        btnSubmit.prop('disabled', true).html('<i class="spinner-border spinner-border-sm me-1"></i> Menyimpan...');

        $.ajax({
            url: AppConfig.initGlobal + 'store/save-data-humas', 
            type: 'POST',
            data: data,
            success: function (response) {
                if (response.status == 'error') {
                    swlErrorHandler(response.message);
                } else {
                    if (response) {
                        $('#dataTable').DataTable().ajax.reload(null, false);
                        if (typeof loadSummaryHumas === 'function') { loadSummaryHumas(); }
                        $('#DataModal').modal('hide');
                        swlSuccess();
                    }
                }
            },
            error: function() {
                swlErrorHandler('Terjadi kesalahan pada server.');
            },
            complete: function() {
                btnSubmit.prop('disabled', false).html(originalText);
            }
        });
    }); 

    $('#DataModal').on('hidden.bs.modal', function () {
        const form = $('#form-usulan');
        form[0].reset();
        form.find('[name="key"]').val(''); // Ensure hidden key is removed
        $('.modal-backdrop').remove();
        $('body').removeClass('modal-open').css('overflow', '');
    });

    // FORMAT ANGKA DENGAN KOMA
    function formatNumber(input) {
        let value = input.value.replace(/,/g, '').replace(/\D/g, '');
        input.value = value.replace(/\B(?=(\d{3})+(?!\d))/g, ',');
    }

    $(document).on('input', '.number-format', function () {
        formatNumber(this);
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
