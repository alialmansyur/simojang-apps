$(document).ready(function () {
    renderEmptyLottie();
    loadData();
});



let activeFetchController = null;

const TEAM_ICONS = [
    `<svg viewBox="0 0 24 24" aria-hidden="true"><path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M22 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg>`,
    `<svg viewBox="0 0 24 24" aria-hidden="true"><rect x="3" y="4" width="18" height="18" rx="2"/><path d="M9 9h6"/><path d="M9 13h6"/><path d="M9 17h4"/></svg>`,
    `<svg viewBox="0 0 24 24" aria-hidden="true"><path d="M3 3v18h18"/><path d="M7 13l3-3 3 2 4-5"/></svg>`,
    `<svg viewBox="0 0 24 24" aria-hidden="true"><ellipse cx="12" cy="5" rx="7" ry="3"/><path d="M5 5v6c0 1.7 3.1 3 7 3s7-1.3 7-3V5"/><path d="M5 11v6c0 1.7 3.1 3 7 3s7-1.3 7-3v-6"/></svg>`,
    `<svg viewBox="0 0 24 24" aria-hidden="true"><path d="M12 3l8 4v5c0 5-3.5 8-8 9-4.5-1-8-4-8-9V7l8-4z"/><path d="M9 12l2 2 4-4"/></svg>`,
    `<svg viewBox="0 0 24 24" aria-hidden="true"><path d="M21 16V8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16z"/></svg>`,
    `<svg viewBox="0 0 24 24" aria-hidden="true"><rect x="2" y="7" width="20" height="13" rx="2"/><path d="M16 7V5a4 4 0 0 0-8 0v2"/></svg>`,
    `<svg viewBox="0 0 24 24" aria-hidden="true"><path d="M3 7h5l2 3h11v9a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"/><path d="M3 7V5a2 2 0 0 1 2-2h4l2 3h8a2 2 0 0 1 2 2v2"/></svg>`
];

function getTeamIcon(name = '', index = 0) {
    const normalized = String(name).toLowerCase();

    if (normalized.includes('data') || normalized.includes('statistik')) return TEAM_ICONS[3];
    if (normalized.includes('humas') || normalized.includes('layanan')) return TEAM_ICONS[1];
    if (normalized.includes('pengawasan') || normalized.includes('wasdal')) return TEAM_ICONS[4];
    if (normalized.includes('integrasi') || normalized.includes('digital')) return TEAM_ICONS[5];
    if (normalized.includes('merit') || normalized.includes('talenta')) return TEAM_ICONS[0];
    if (normalized.includes('arsip') || normalized.includes('dokumen')) return TEAM_ICONS[7];

    return TEAM_ICONS[index % TEAM_ICONS.length];
}

function showFetchBackdrop() {
    $('#twLoadingBackdrop').stop(true, true).fadeIn(180).css('display', 'flex');
    $('#loaded').attr('aria-busy', 'true');
    $('#twReload').prop('disabled', true);
}

function hideFetchBackdrop() {
    $('#twLoadingBackdrop').stop(true, true).fadeOut(180);
    $('#loaded').attr('aria-busy', 'false');
    $('#twReload').prop('disabled', false);
}

function getSkeletonMarkup() {
    return `
        <div class="col-12 col-md-6 col-xl-3">
            <div class="tw-skel-card d-flex align-items-center px-3 py-2 gap-3">
                <div class="tw-skel-icon skeleton" style="width: 56px; height: 56px; border-radius: 8px; flex-shrink: 0;"></div>
                <div class="d-flex flex-column w-100 gap-2">
                    <div class="tw-skel-title skeleton" style="width: 40%; height: 12px; border-radius: 4px;"></div>
                    <div class="tw-skel-sub skeleton" style="width: 80%; height: 16px; border-radius: 4px;"></div>
                </div>
            </div>
        </div>
    `;
}



function renderEmptyLottie() {
    $('#twEmptyLottie').html(`
<div class="service-ui-empty-panel text-center py-5">
    <img src="${window.AppConfig ? AppConfig.initGlobal : '/'}apps/assets/media/illustrations/empty-content-profile.png" alt="Empty" class="img-fluid mb-3" style="max-width: 180px; opacity: 0.85;">
    <h5 class="fw-bolder text-dark mb-1">Pencarian Tidak Ditemukan</h5>
    <p class="text-muted mb-0 mx-auto" style="max-width: 400px; font-size: .95rem;">
        Maaf, kami tidak dapat menemukan data yang Anda cari. Silakan periksa kembali kata kunci atau filter pencarian Anda.
    </p>
</div>
    `);
}

function showSkeleton(count = 6) {
    const $container = $('#loaded');
    const skeletonTpl = getSkeletonMarkup();

    $('#twEmptyState').addClass('d-none');
    $('#twErrorState').addClass('d-none');
    $container.empty();

    for (let i = 0; i < count; i += 1) {
        $container.append(skeletonTpl);
    }
}

async function loadData() {
    if (activeFetchController) {
        activeFetchController.abort();
    }
    activeFetchController = new AbortController();

    const startAt = Date.now();
    const minSkeletonMs = 450;

    showSkeleton(6);
    showFetchBackdrop();

    try {
        const response = await fetch(AppConfig.initGlobal + 'fetch-timkerja', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            signal: activeFetchController.signal
        });

        if (!response.ok) {
            throw new Error('Failed to fetch tim kerja');
        }

        const data = await response.json();
        const elapsed = Date.now() - startAt;
        if (elapsed < minSkeletonMs) {
            await new Promise((resolve) => setTimeout(resolve, minSkeletonMs - elapsed));
        }
        pageLoaded(data);
    } catch (error) {
        if (error.name === 'AbortError') return;
        $('#loaded').empty();
        $('#twErrorState').removeClass('d-none');
    } finally {
        hideFetchBackdrop();
        activeFetchController = null;
    }
}

function resolveServiceCount(value = {}) {
    const candidates = [
        value.total_layanan,
        value.jumlah_layanan,
        value.total_service,
        value.service_count,
        value.total,
    ];

    const found = candidates.find((item) => item !== undefined && item !== null && item !== '');
    const parsed = Number(found);
    return Number.isFinite(parsed) ? parsed : null;
}

function pageLoaded(data) {
    const $container = $('#loaded');
    const list = Array.isArray(data?.list) ? data.list : [];

    $container.empty();
    $('#twErrorState').addClass('d-none');

    if (!list.length) {
        $('#twEmptyState').removeClass('d-none');
        return;
    }

    $('#twEmptyState').addClass('d-none');

    $.each(list, function (index, value) {
        const iconSvg = getTeamIcon(value.nama, index);
        const toneClass = `tw-tone-${(index % 4) + 1}`;
        const codeDisplay = value.code || 'CODE-' + (index + 1).toString().padStart(3, '0');
        const hasAccess = Boolean(value.has_access);

        const accessBadgeHtml = hasAccess
            ? `<span class="tw-access-icon icon-unlocked" title="Layanan dapat diakses"><i class="bi bi-unlock-fill"></i></span>`
            : `<span class="tw-access-icon icon-locked" title="Layanan tidak dapat diakses"><i class="bi bi-lock-fill"></i></span>`;


        const cardLinkHref = hasAccess ? (AppConfig.initGlobal + "timkerja-layanan/" + value.uid) : "javascript:void(0)";
        const tooltipAttr = hasAccess ? '' : `data-bs-toggle="tooltip" data-bs-placement="top" title="Layanan tidak dapat diakses. Anda belum memiliki hak akses pada modul di tim kerja ini."`;

        const card = `
            <div class="col-12 col-md-6 col-xl-3">
                <a class="tw-link text-decoration-none ${hasAccess ? '' : 'is-locked-link'}" href="${cardLinkHref}" ${tooltipAttr} data-has-access="${hasAccess ? '1' : '0'}" data-name="${escapeHtml(value.nama)}">
                    <div class="card tw-card tw-animate-entry h-100 ${toneClass} ${hasAccess ? '' : 'tw-card-locked'}" style="--animation-order: ${index};" data-key="${value.id}" data-code="${value.code}">
                        <div class="card-body position-relative overflow-hidden d-flex align-items-center p-3 gap-3">
                            <div class="tw-icon-box flex-shrink-0 d-flex align-items-center justify-content-center z-1">
                                <span class="tw-icon">${iconSvg}</span>
                            </div>
                            <div class="tw-text-box d-flex flex-column text-start overflow-hidden z-1 pe-3">
                                <span class="tw-code text-uppercase fw-bold text-muted">${codeDisplay}</span>
                                <h6 class="fw-bold tw-team-name mb-0 lh-sm" title="${escapeHtml(value.nama)}">${escapeHtml(value.nama)}</h6>
                            </div>
                            <div class="tw-access-corner z-2">
                                ${accessBadgeHtml}
                            </div>
                            <div class="tw-card-bg-decoration pe-none">
                                ${iconSvg}
                            </div>
                        </div>
                    </div>
                </a>
            </div>
        `;

        $container.append(card);
    });

    // Initialize Bootstrap tooltips
    if (typeof bootstrap !== 'undefined' && bootstrap.Tooltip) {
        const tooltipTriggerList = [].slice.call(document.querySelectorAll('#loaded [data-bs-toggle="tooltip"]'));
        tooltipTriggerList.map(function (tooltipTriggerEl) {
            return new bootstrap.Tooltip(tooltipTriggerEl);
        });
    }
}

function escapeHtml(string) {
    if (!string) return '';
    const entityMap = {
        '&': '&amp;',
        '<': '&lt;',
        '>': '&gt;',
        '"': '&quot;',
        "'": '&#39;',
        '/': '&#x2F;'
    };
    return String(string).replace(/[&<>"'\/]/g, function (s) {
        return entityMap[s];
    });
}

// Click handler on locked card
$(document).on('click', '.tw-link.is-locked-link', function (e) {
    e.preventDefault();
    const teamName = $(this).data('name') || 'tim kerja ini';
    if (typeof notifyWarning === 'function') {
        notifyWarning(`Layanan pada ${teamName} tidak dapat diakses. Hubungi Administrator untuk mendapatkan izin akses.`);
    } else if (typeof Swal !== 'undefined') {
        Swal.fire({
            icon: 'warning',
            title: 'Akses Layanan Terkunci',
            text: `Anda belum memiliki izin akses untuk modul layanan pada ${teamName}. Hubungi Administrator untuk mengajukan hak akses.`,
            confirmButtonColor: '#1040c1'
        });
    } else {
        alert(`Layanan pada ${teamName} tidak dapat diakses. Hubungi Administrator untuk mendapatkan izin akses.`);
    }
});

$('#twReload').on('click', function () {
    loadData();
});


