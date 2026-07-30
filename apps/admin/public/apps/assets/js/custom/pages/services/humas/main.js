$(document).ready(function () {

    $(document).on('click', '.btn-submit-form', function () {
        $('#form-usulan').submit();
    });

    $('#form-usulan').on('submit', function (e) {
        e.preventDefault();

         $('.number-format').each(function () {
            this.value = this.value.replace(/,/g, '');
        });

        swlwaitProsessing();

        $.ajax({
            url: AppConfig.initGlobal + 'store/save-data-humas', 
            type: 'POST',
            data: $(this).serialize(),
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
            }
        });
    }); 

    $('#DataModal').on('hidden.bs.modal', function () {
        const form = $('#form-usulan');
        form[0].reset();
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





