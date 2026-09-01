<!-- Modal PDF Preview Viewer -->
<div class="modal fade" id="pnbpPreviewModal" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1" aria-labelledby="pnbpPreviewModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-fullscreen modal-dialog-centered">
        <div class="modal-content border-0">
            <div class="modal-header border-bottom py-2 px-4 bg-white d-flex align-items-center justify-content-between" style="background-color: #ffffff !important;">
                <div class="d-flex align-items-center gap-2">
                    <div class="flex-shrink-0 d-flex align-items-center justify-content-center bg-danger-subtle text-danger rounded-2" style="width: 36px; height: 36px;">
                        <i class="bi bi-file-earmark-pdf-fill fs-5"></i>
                    </div>
                    <div>
                        <h6 class="modal-title fw-bold mb-0" id="pnbpPreviewModalLabel" style="color: #1e293b !important; font-size: 1.05rem;">Pratinjau Dokumen PDF</h6>
                        <small class="text-muted" id="previewSubTitle" style="color: #64748b !important; font-size: 0.8rem;">Memuat pratinjau dokumen...</small>
                    </div>
                </div>
                
                <div class="d-flex align-items-center gap-2">
                    <a href="javascript:void(0)" class="btn btn-sm btn-primary fw-semibold px-3" id="btnPreviewDownload">
                        <i class="bi bi-download me-1"></i> Unduh PDF
                    </a>
                    <button type="button" class="btn btn-sm btn-outline-primary fw-semibold px-3" id="btnPreviewPrint">
                        <i class="bi bi-printer me-1"></i> Cetak
                    </button>
                    <button type="button" class="btn-close ms-2" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
            </div>
            
            <div class="modal-body p-0 position-relative" style="height: calc(100vh - 60px); background-color: #525659;">
                <!-- Loading State inside Viewer -->
                <div id="pdfLoadingState" class="position-absolute top-50 start-50 translate-middle text-center text-white">
                    <div class="spinner-border text-light mb-3" style="width: 3rem; height: 3rem;" role="status">
                        <span class="visually-hidden">Loading...</span>
                    </div>
                    <h6 class="fw-semibold">Sedang merender pratinjau PDF...</h6>
                </div>

                <!-- Iframe PDF Viewer -->
                <iframe id="pnbpPdfIframe" class="w-100 h-100 border-0 d-none" src="about:blank"></iframe>
            </div>
        </div>
    </div>
</div>
