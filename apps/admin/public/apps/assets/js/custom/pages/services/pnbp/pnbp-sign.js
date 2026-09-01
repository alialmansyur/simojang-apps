/**
 * Mobile Digital Signature Canvas Controller
 */

$(document).ready(function() {
    const canvas = document.getElementById('signatureCanvas');
    if (!canvas) return;

    const ctx = canvas.getContext('2d');
    const hint = document.getElementById('canvasHint');
    const consentCheck = document.getElementById('checkConsent');
    const submitBtn = document.getElementById('btnSubmitSign');
    const clearBtn = document.getElementById('btnClearCanvas');

    let isDrawing = false;
    let hasDrawn = false;
    let lastX = 0;
    let lastY = 0;

    function resizeCanvas() {
        const rect = canvas.getBoundingClientRect();
        const ratio = window.devicePixelRatio || 1;

        // Simpan data gambar saat ini sebelum resize jika ada
        let tempImage = null;
        if (hasDrawn) {
            tempImage = canvas.toDataURL();
        }

        canvas.width = rect.width * ratio;
        canvas.height = rect.height * ratio;
        ctx.scale(ratio, ratio);

        ctx.strokeStyle = '#0f172a';
        ctx.lineWidth = 2.5;
        ctx.lineCap = 'round';
        ctx.lineJoin = 'round';

        if (tempImage) {
            const img = new Image();
            img.onload = function() {
                ctx.drawImage(img, 0, 0, rect.width, rect.height);
            };
            img.src = tempImage;
        }
    }

    resizeCanvas();
    window.addEventListener('resize', resizeCanvas);

    function getCoords(e) {
        const rect = canvas.getBoundingClientRect();
        let clientX = 0;
        let clientY = 0;

        if (e.touches && e.touches.length > 0) {
            clientX = e.touches[0].clientX;
            clientY = e.touches[0].clientY;
        } else {
            clientX = e.clientX;
            clientY = e.clientY;
        }

        return {
            x: clientX - rect.left,
            y: clientY - rect.top
        };
    }

    function startDrawing(e) {
        e.preventDefault();
        isDrawing = true;
        const coords = getCoords(e);
        lastX = coords.x;
        lastY = coords.y;

        if (!hasDrawn) {
            hasDrawn = true;
            if (hint) hint.style.display = 'none';
            validateForm();
        }
    }

    function draw(e) {
        if (!isDrawing) return;
        e.preventDefault();

        const coords = getCoords(e);
        ctx.beginPath();
        ctx.moveTo(lastX, lastY);
        ctx.lineTo(coords.x, coords.y);
        ctx.stroke();

        lastX = coords.x;
        lastY = coords.y;
    }

    function stopDrawing(e) {
        if (isDrawing) {
            e.preventDefault();
            isDrawing = false;
        }
    }

    // Pointer & Mouse Events
    canvas.addEventListener('mousedown', startDrawing);
    canvas.addEventListener('mousemove', draw);
    canvas.addEventListener('mouseup', stopDrawing);
    canvas.addEventListener('mouseleave', stopDrawing);

    // Touch Events (Mobile)
    canvas.addEventListener('touchstart', startDrawing, { passive: false });
    canvas.addEventListener('touchmove', draw, { passive: false });
    canvas.addEventListener('touchend', stopDrawing, { passive: false });
    canvas.addEventListener('touchcancel', stopDrawing, { passive: false });

    // Clear Canvas
    function clearCanvas() {
        const rect = canvas.getBoundingClientRect();
        ctx.clearRect(0, 0, rect.width, rect.height);
        hasDrawn = false;
        if (hint) hint.style.display = 'block';
        validateForm();
    }

    if (clearBtn) {
        clearBtn.addEventListener('click', clearCanvas);
    }

    // Validate Checkbox and Canvas
    function validateForm() {
        if (submitBtn && consentCheck) {
            submitBtn.disabled = !(hasDrawn && consentCheck.checked);
        }
    }

    if (consentCheck) {
        consentCheck.addEventListener('change', validateForm);
    }

    // Submit Signature
    $('#signatureForm').on('submit', function(e) {
        e.preventDefault();

        if (!hasDrawn) {
            Swal.fire('Perhatian', 'Silakan goreskan tanda tangan Anda pada bidang canvas yang tersedia.', 'warning');
            return;
        }

        const base64Image = canvas.toDataURL('image/png');
        const token = $('#token').val();

        Swal.fire({
            title: 'Konfirmasi Tanda Tangan',
            text: 'Apakah Anda yakin ingin membubuhkan tanda tangan ini secara permanen?',
            icon: 'question',
            showCancelButton: true,
            confirmButtonText: 'Ya, Bubuhkan',
            cancelButtonText: 'Batal'
        }).then((result) => {
            if (!result.isConfirmed) return;

            Swal.fire({
                title: 'Menyimpan Tanda Tangan...',
                text: 'Mohon tunggu beberapa detik.',
                allowOutsideClick: false,
                didOpen: () => Swal.showLoading()
            });

            $.ajax({
                url: '/pnbp-sign/submit',
                type: 'POST',
                data: {
                    token: token,
                    signature_image: base64Image
                },
                dataType: 'json',
                success: function(res) {
                    Swal.close();
                    if (res && res.status === 'success') {
                        $('#signFormSection').addClass('d-none');
                        $('#signSuccessSection').removeClass('d-none');
                        $('#successHash').text(res.hash || '-');
                    } else {
                        Swal.fire('Gagal', res.message || 'Gagal menyimpan tanda tangan.', 'error');
                    }
                },
                error: function(xhr) {
                    Swal.close();
                    const msg = xhr.responseJSON && xhr.responseJSON.message ? xhr.responseJSON.message : 'Terjadi kesalahan saat memproses tanda tangan.';
                    Swal.fire('Error', msg, 'error');
                }
            });
        });
    });
});
