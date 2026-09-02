/**
 * PNBP PDF Generator & Preview Helper
 */

function generateAndPreviewPdf(uid, onDone) {
    if (!uid) return;

    if (typeof swlwaitProsessing === 'function') {
        swlwaitProsessing('Sedang menyusun dokumen PDF...');
    }

    $.ajax({
        url: AppConfig.initGlobal + 'apps-pnbp/generate-pdf',
        type: 'POST',
        dataType: 'json',
        data: { uid: uid },
        success: function(res) {
            if (typeof Swal !== 'undefined' && Swal.close) {
                Swal.close();
            }
            if (res && res.status === 'success') {
                openPdfPreviewModal(uid, res.preview, res.download);
                if (typeof onDone === 'function') onDone(res);
            } else {
                if (typeof swlErrorHandler === 'function') {
                    swlErrorHandler(res && res.message ? res.message : 'Gagal membuat dokumen PDF.');
                } else {
                    alert(res && res.message ? res.message : 'Gagal membuat dokumen PDF.');
                }
            }
        },
        error: function(xhr) {
            if (typeof Swal !== 'undefined' && Swal.close) {
                Swal.close();
            }
            const msg = xhr.responseJSON && xhr.responseJSON.message ? xhr.responseJSON.message : 'Gagal menghubungi server generator PDF.';
            if (typeof swlErrorHandler === 'function') {
                swlErrorHandler(msg);
            } else {
                alert(msg);
            }
        }
    });
}

function openPdfPreviewModal(uid, previewUrl, downloadUrl) {
    const modal = $('#pnbpPreviewModal');
    const iframe = $('#pnbpPdfIframe');
    const loading = $('#pdfLoadingState');
    const btnDownload = $('#btnPreviewDownload');
    const btnPrint = $('#btnPreviewPrint');

    if (!previewUrl) {
        previewUrl = AppConfig.initGlobal + 'apps-pnbp/preview-pdf/' + uid;
    }
    if (!downloadUrl) {
        downloadUrl = AppConfig.initGlobal + 'apps-pnbp/download-pdf/' + uid;
    }

    btnDownload.attr('href', downloadUrl);
    
    btnPrint.off('click').on('click', function() {
        if (iframe[0] && iframe[0].contentWindow) {
            try {
                iframe[0].contentWindow.focus();
                iframe[0].contentWindow.print();
            } catch (e) {
                window.open(previewUrl, '_blank');
            }
        } else {
            window.open(previewUrl, '_blank');
        }
    });

    loading.removeClass('d-none').html(`
        <div class="spinner-border text-light mb-3" style="width: 3rem; height: 3rem;" role="status">
            <span class="visually-hidden">Loading...</span>
        </div>
        <h6 class="fw-semibold">Sedang merender pratinjau PDF...</h6>
    `);
    iframe.addClass('d-none');

    // Timeout safeguard for iframe rendering
    let loadTimer = setTimeout(function() {
        if (loading.is(':visible')) {
            loading.html(`
                <div class="text-warning mb-2"><i class="bi bi-exclamation-triangle fs-1"></i></div>
                <h6 class="fw-semibold text-white">Memuat dokumen memakan waktu lebih lama dari biasanya.</h6>
                <div class="mt-3">
                    <a href="${downloadUrl}" class="btn btn-sm btn-primary px-3 me-2"><i class="bi bi-download me-1"></i> Unduh Langsung</a>
                    <button type="button" class="btn btn-sm btn-outline-light px-3" onclick="openPdfPreviewModal('${uid}', '${previewUrl}', '${downloadUrl}')"><i class="bi bi-arrow-clockwise me-1"></i> Coba Lagi</button>
                </div>
            `);
        }
    }, 12000);

    iframe.off('load').on('load', function() {
        clearTimeout(loadTimer);
        loading.addClass('d-none');
        iframe.removeClass('d-none');
    });

    // Set src with cache buster
    iframe.attr('src', previewUrl + '?t=' + new Date().getTime());

    modal.modal('show');

    modal.off('hidden.bs.modal').on('hidden.bs.modal', function() {
        clearTimeout(loadTimer);
        iframe.attr('src', 'about:blank');
        iframe.addClass('d-none');
    });
}
