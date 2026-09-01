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
            Swal.close();
            if (res && res.status === 'success') {
                openPdfPreviewModal(uid, res.preview, res.download);
                if (typeof onDone === 'function') onDone(res);
            } else {
                if (typeof swlErrorHandler === 'function') {
                    swlErrorHandler(res && res.message ? res.message : 'Gagal membuat dokumen PDF.');
                }
            }
        },
        error: function(xhr) {
            Swal.close();
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
            iframe[0].contentWindow.focus();
            iframe[0].contentWindow.print();
        }
    });

    loading.removeClass('d-none');
    iframe.addClass('d-none');

    iframe.off('load').on('load', function() {
        loading.addClass('d-none');
        iframe.removeClass('d-none');
    });

    // Set src with cache buster
    iframe.attr('src', previewUrl + '?t=' + new Date().getTime());

    modal.modal('show');
}
