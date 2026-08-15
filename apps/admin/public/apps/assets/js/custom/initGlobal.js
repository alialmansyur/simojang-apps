
window.__appLoadingCounter = 0;
window.__appLoadingMounted = false;

function notify(message, type) {
    const text = String(message || '').trim();
    if (!text || typeof Toastify !== 'function') return;

    const colors = {
        success: '#198754',
        error: '#dc3545',
        info: '#0d6efd',
        warning: '#f59e0b',
    };

    Toastify({
        text,
        duration: 3000,
        gravity: 'top',
        position: 'center',
        className: 'app-toast app-toast-top-center',
        close: true,
        stopOnFocus: true,
        style: {
            background: colors[type] || colors.info,
        },
    }).showToast();
}

function notifySuccess(message) {
    notify(message || 'Data berhasil diproses', 'success');
}

function notifyError(message) {
    notify(message || 'Terjadi kesalahan saat memproses data', 'error');
}

function notifyInfo(message) {
    notify(message || 'Informasi', 'info');
}

function ensureGlobalLoader() {
    if (window.__appLoadingMounted) return;

    if (!document.getElementById('appGlobalLoadingOverlay')) {
        document.body.insertAdjacentHTML('beforeend', `
            <div id="appGlobalLoadingOverlay" class="app-global-loading" aria-live="polite" aria-hidden="true">
                <div class="app-global-loading-box">
                    <span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span>
                    <span id="appGlobalLoadingText">Memproses data...</span>
                </div>
            </div>
        `);
    }

    window.__appLoadingMounted = true;
}

function showLoading(text) {
    ensureGlobalLoader();
    window.__appLoadingCounter += 1;

    const overlay = document.getElementById('appGlobalLoadingOverlay');
    const messageNode = document.getElementById('appGlobalLoadingText');
    if (!overlay || !messageNode) return;

    messageNode.textContent = String(text || 'Memproses data...');
    overlay.classList.add('is-show');
    overlay.setAttribute('aria-hidden', 'false');
}

function hideLoading() {
    window.__appLoadingCounter = Math.max(0, window.__appLoadingCounter - 1);
    if (window.__appLoadingCounter > 0) return;

    const overlay = document.getElementById('appGlobalLoadingOverlay');
    if (!overlay) return;

    overlay.classList.remove('is-show');
    overlay.setAttribute('aria-hidden', 'true');
}

function closeProcessingLoaderIfAny() {
    window.__appLoadingCounter = 1;
    hideLoading();
}

function patchSwalNotifications() {
    if (typeof Swal === 'undefined' || Swal.__appPatched === true) return;
    Swal.__appPatched = true;

    const originalFire = Swal.fire.bind(Swal);
    const originalClose = typeof Swal.close === 'function' ? Swal.close.bind(Swal) : null;

    Swal.fire = function () {
        const options = arguments[0];
        if (options && typeof options === 'object') {
            const title = String(options.title || options.text || '').toLowerCase();
            if (title.includes('permintaan sedang di proses')) {
                showLoading('Memproses data...');
                return Promise.resolve({ isDismissed: true });
            }

            const isSimpleNotification = options.showCancelButton !== true && options.input === undefined;
            if (isSimpleNotification && (options.icon === 'success' || options.icon === 'error')) {
                closeProcessingLoaderIfAny();
                if (options.icon === 'success') {
                    notifySuccess(options.title || options.text || 'Data berhasil diproses');
                } else {
                    notifyError(options.title || options.text || 'Terjadi kesalahan saat memproses data');
                }
                return Promise.resolve({ isConfirmed: true });
            }
        }
        return originalFire.apply(Swal, arguments);
    };

    if (originalClose) {
        Swal.close = function () {
            closeProcessingLoaderIfAny();
            return originalClose.apply(Swal, arguments);
        };
    }
}

function swlErrorHandler(msg) {
    closeProcessingLoaderIfAny();
    notifyError(msg);
}

function swlSuccess(message) {
    closeProcessingLoaderIfAny();
    notifySuccess(message || 'Data berhasil diproses');
}

function swlwaitProsessing() {
    showLoading('Memproses data...');
}

function getSessionLoginUrl() {
    const configured = window.APP_SESSION && window.APP_SESSION.loginUrl
        ? String(window.APP_SESSION.loginUrl).trim()
        : '';
    if (configured) return configured;
    if (window.AppConfig && window.AppConfig.initGlobal) {
        return String(window.AppConfig.initGlobal).replace(/\/?$/, '/') + 'login';
    }
    return '/login';
}

function sessionExpiredSvg() {
    return `
        <svg class="app-session-expired-icon" width="112" height="112" viewBox="0 0 112 112" fill="none" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
            <circle cx="56" cy="56" r="56" fill="#FFF3F2"/>
            <circle cx="56" cy="56" r="37" fill="#FFE4E2"/>
            <path d="M56 37v18l10 7" stroke="#D63031" stroke-width="5" stroke-linecap="round" stroke-linejoin="round"/>
            <circle cx="56" cy="56" r="24" stroke="#D63031" stroke-width="5"/>
        </svg>
    `;
}

function showSessionExpiredAlert() {
    if (window.__sessionExpiredAlertShown === true) return Promise.resolve(false);
    window.__sessionExpiredAlertShown = true;

    closeProcessingLoaderIfAny();

    const loginUrl = getSessionLoginUrl();
    const message = 'Sesi anda telah selesai, silahkan login kembali';

    if (typeof Swal === 'undefined') {
        notifyError(message);
        window.setTimeout(function () {
            window.location.href = loginUrl;
        }, 1000);
        return Promise.resolve(true);
    }

    return Swal.fire({
        html: `
            <div class="app-session-expired-wrap">
                <div class="app-session-expired-visual">${sessionExpiredSvg()}</div>
                <strong class="app-session-expired-heading">Sesi Berakhir</strong>
                <p class="app-session-expired-text">${message}</p>
            </div>
        `,
        icon: undefined,
        showCancelButton: false,
        confirmButtonText: 'Login Kembali',
        allowOutsideClick: false,
        allowEscapeKey: false,
        customClass: {
            popup: 'app-session-expired-swal',
        },
    }).then(function () {
        window.location.href = loginUrl;
    });
}

function scheduleSessionExpiryAlert() {
    const expiresAtSeconds = Number(window.APP_SESSION && window.APP_SESSION.jwtExpiresAt || 0);
    if (!Number.isFinite(expiresAtSeconds) || expiresAtSeconds <= 0) return;

    const expiresAtMs = expiresAtSeconds * 1000;
    const now = Date.now();
    const timeout = expiresAtMs - now;

    if (timeout <= 0) {
        showSessionExpiredAlert();
        return;
    }

    window.setTimeout(function () {
        showSessionExpiredAlert();
    }, timeout);
}

function isDetailModalElement(modalEl) {
    if (!modalEl) return false;
    const modalId = String(modalEl.id || '').toLowerCase();
    return modalId.includes('detail') || modalId === 'filedetailmodal';
}

function isFullscreenModalElement(modalEl) {
    if (!modalEl) return false;
    const dialogEl = modalEl.querySelector('.modal-dialog');
    if (!dialogEl) return false;
    return /\bmodal-fullscreen(?:-[a-z]+-down)?\b/.test(String(dialogEl.className || ''));
}

function applyModalRadius(modalEl) {
    if (!modalEl) return;

    const forceRounded = modalEl.classList.contains('modal-force-rounded');
    const isDetailModal = isDetailModalElement(modalEl);
    const isFullscreenModal = !forceRounded && isFullscreenModalElement(modalEl);
    const radius = (isDetailModal || isFullscreenModal) ? '0' : '1.25rem';
    const contentEl = modalEl.querySelector('.modal-content');
    const bodyEl = modalEl.querySelector('.modal-body');
    const headerEl = modalEl.querySelector('.modal-header');
    const footerEl = modalEl.querySelector('.modal-footer');

    modalEl.style.setProperty('--bs-modal-border-radius', radius, 'important');
    modalEl.style.setProperty('--bs-modal-inner-border-radius', radius, 'important');

    if (contentEl) {
        contentEl.style.setProperty('border-radius', radius, 'important');
        contentEl.style.setProperty('overflow', 'hidden', 'important');
    }

    if (headerEl) {
        headerEl.style.setProperty('border-top-left-radius', radius, 'important');
        headerEl.style.setProperty('border-top-right-radius', radius, 'important');
    }

    if (footerEl) {
        footerEl.style.setProperty('border-bottom-left-radius', radius, 'important');
        footerEl.style.setProperty('border-bottom-right-radius', radius, 'important');
    }

    if (bodyEl) {
        if (isDetailModal || isFullscreenModal) {
            bodyEl.style.setProperty('border-radius', '0', 'important');
        } else {
            bodyEl.style.removeProperty('border-radius');
        }
    }
}

function updateLogoByTheme() {
	const theme = localStorage.getItem("theme") || "theme-light";
	const logo = document.getElementById("main-logo");
	if (!logo) return;
	if (theme === "theme-dark") {
		logo.src = AppConfig.initGlobal + 'apps/assets/images/logo/logo-light.png';
	} else {
		logo.src = AppConfig.initGlobal + 'apps/assets/images/logo/logo-dark.png';
	}
}

function applyThemeFromStorage() {
	const theme = localStorage.getItem("theme") || "theme-light";
	document.documentElement.className = theme;
	const toggle = document.getElementById("toggle-dark");
	if (toggle) {
		toggle.checked = theme === "theme-dark";
	}
	updateLogoByTheme();
}

document.addEventListener("DOMContentLoaded", function () {
	applyThemeFromStorage();
	const toggle = document.getElementById("toggle-dark");
	if (toggle) {
		toggle.addEventListener("change", function () {
			const isDark = this.checked;
			const newTheme = isDark ? "theme-dark" : "theme-light";
			localStorage.setItem("theme", newTheme);
			document.documentElement.className = newTheme;
			updateLogoByTheme();
		});
	}
});

$(document).ready(function () {
    patchSwalNotifications();
    scheduleSessionExpiryAlert();

    $(document).ajaxStop(function () {
        closeProcessingLoaderIfAny();
    });

    $(document).ajaxError(function (event, jqXHR) {
        closeProcessingLoaderIfAny();
        const statusCode = Number(jqXHR && jqXHR.status || 0);
        if ([401, 403, 419, 440].includes(statusCode)) {
            showSessionExpiredAlert();
        }
    });

    $('.select2').select2({
        theme: "bootstrap-5",
        width: $(this).data('width') ? $(this).data('width') : $(this).hasClass('w-100') ? '100%' : 'style',
        placeholder: $(this).data('placeholder'),
    });

    $(document).on('show.bs.modal', function (e) {
        applyModalRadius(e.target);
    });

    $(document).on('shown.bs.modal', function (e) {
        const modalEl = e.target;
        applyModalRadius(modalEl);

        $(modalEl).find('.select2').each(function () {
            $(this).select2({
                // width: '100%',
                theme: 'bootstrap-5',
                dropdownParent: $(modalEl)
            });
        });
    });

    $('.modal').each(function () {
        applyModalRadius(this);
    });
});

$(window).on('load', function () {
    // Sembunyikan loader secara otomatis begitu halaman sepenuhnya termuat
    window.__appLoadingMounted = true; // Tandai bahwa overlay HTML sudah ada (dari header.php)
    hideLoading();
});

// --- Global DataTables Configuration ---
if (typeof jQuery !== 'undefined' && jQuery.fn.dataTable) {
    jQuery.extend(true, jQuery.fn.dataTable.defaults, {
        lengthMenu: [[10, 25, 50, 100], [10, 25, 50, 100]],
        dom: "<'d-flex flex-wrap justify-content-between mb-3'<'d-flex align-items-center gap-2'B><'d-flex'f>>" +
             "<'table-responsive'tr>" +
             "<'d-flex flex-wrap justify-content-between align-items-center mt-3'<'d-flex align-items-center gap-3'li><'d-flex align-items-center'p>>",
        language: {
            lengthMenu: "_MENU_",
            info: "Menampilkan <b>_START_</b> - <b>_END_</b> dari <b>_TOTAL_</b> data",
            infoEmpty: "Menampilkan <b>0</b> - <b>0</b> dari <b>0</b> data",
            infoFiltered: "(difilter dari <b>_MAX_</b> data)"
        }
    });
}
