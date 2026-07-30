loadData();

async function loadData(keyword = '') {
    const response = await fetch( AppConfig.initGlobal + 'fetch-layanan-enrolled', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json'
        }
    });
    const data = await response.json();
    pageLoaded(data);
}

function pageLoaded(data) {
    $('#loaded').html('');
    $('#spinLoadData').addClass('d-none');
    if (Array.isArray(data.list) && data.list.length === 0) {
        var card = `
        <div class="col-md-12">
            <div class="d-flex justify-content-center align-items-center" style="height:500px;">
                <div class="service-ui-empty-panel text-center py-5">
    <img src="${window.AppConfig ? AppConfig.initGlobal : '/'}apps/assets/media/illustrations/empty-content-profile.png" alt="Empty" class="img-fluid mb-3" style="max-width: 180px; opacity: 0.85;">
    <h5 class="fw-bolder text-dark mb-1">Pencarian Tidak Ditemukan</h5>
    <p class="text-muted mb-0 mx-auto" style="max-width: 400px; font-size: .95rem;">
        Maaf, kami tidak dapat menemukan data yang Anda cari. Silakan periksa kembali kata kunci atau filter pencarian Anda.
    </p>
</div>
            </div>
        </div>
        `;
        $('#loaded').append(card);
    } else {
        $.each(data.list, function (index, value) {
            var card = `
                <div class="col-12 col-md-3">
                    <div class="card text-center h-100 ${value.status == 1 ? 'card-hover-border card-daily-task' : 'deactivated-card'}" data-key="${value.id}" data-alias="${value.alias}" data-code="${value.code}">
                        <div class="card-body card-body-task" style="cursor:pointer;">
                            <span class="badge badge-position-top-end ${value.status == 1 ? 'bg-light-primary' : 'bg-light-secondary'}">${value.status == 1 ? 'active' : 'deactive'}</span>
                            <i class="bi bi-folder-check fs-1 text-primary mb-2"></i>
                            <h5 class="fw-bold">${value.alias}</h5>
                            <p class="text-muted small mb-0 mt-auto d-none d-md-block">
                                Layanan Kanreg III BKN Bandung
                            </p>
                        </div>
                    </div>
                </div>
            `;
            $('#loaded').append(card);
        });
    }

    $('.card-daily-task').on('click', function () {
        var code = $(this).attr('data-code');
        var urlupload =  AppConfig.initGlobal + 'upload-' + code;
        var urlentry =  AppConfig.initGlobal + 'entry-' + code;
        var urlinfo =  AppConfig.initGlobal + 'info-' + code;

        $('.card-upload').on('click', function () {
            window.location.href = urlupload;
        })

        $('.card-entry').on('click', function () {
            window.location.href = urlentry;
        })

        $('.card-info').on('click', function () {
            window.location.href = urlinfo;
        })

        $('#quickActionModal').modal('show');
    });
}

function swlErrorHandler(msg) {
    Swal.fire({
        toast: true,
        position: 'top',
        icon: 'error',
        title: msg,
        timer: 3000,
        showConfirmButton: false
    });
}

function swlSuccess() {
    Swal.fire({
        toast: true,
        position: 'top',
        icon: 'success',
        title: 'Data berhasil di simpan',
        timer: 3000,
        showConfirmButton: false
    }).then(() => {
        window.location.reload();
    });
}
