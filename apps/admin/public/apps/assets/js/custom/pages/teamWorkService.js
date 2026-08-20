const SERVICE_ICONS = [
    `<svg viewBox="0 0 24 24" aria-hidden="true"><path d="M3 7h5l2 3h11v9a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"/><path d="M3 7V5a2 2 0 0 1 2-2h4l2 3h8a2 2 0 0 1 2 2v2"/></svg>`,
    `<svg viewBox="0 0 24 24" aria-hidden="true"><rect x="3" y="4" width="18" height="18" rx="2"/><path d="M9 9h6"/><path d="M9 13h6"/><path d="M9 17h4"/></svg>`,
    `<svg viewBox="0 0 24 24" aria-hidden="true"><path d="M12 3l8 4v5c0 5-3.5 8-8 9-4.5-1-8-4-8-9V7l8-4z"/><path d="M9 12l2 2 4-4"/></svg>`,
    `<svg viewBox="0 0 24 24" aria-hidden="true"><path d="M3 3v18h18"/><path d="M7 13l3-3 3 2 4-5"/></svg>`,
    `<svg viewBox="0 0 24 24" aria-hidden="true"><path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M22 21v-2a4 4 0 0 0-3-3.87"/></svg>`,
    `<svg viewBox="0 0 24 24" aria-hidden="true"><path d="M21 16V8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16z"/></svg>`
];

const SEARCH_DEBOUNCE_MS = 300;
const LOAD_UI_DELAY_MS = 120;

const PREFS_KEY = 'tws:prefs:v2';
const FAVORITES_KEY = 'tws:favorites:v1';
const RECENTS_KEY = 'tws:recents:v1';
const RECENTS_LIMIT = 5;

const BACK_TO_TOP_THRESHOLD = 260;
const FILTER_LABELS = {
    all: 'Semua',
    updated: 'Sudah Update',
    pending: 'Belum Update',
    accessible: 'Bisa Diakses',
    favorite: 'Favorit',
};

const state = {
    keyword: '',
    filter: 'all',
    sort: 'name_asc',
    viewMode: 'list',
    favorites: new Set(),
    recents: [],
    latestRawList: [],
};

let searchDebounceTimer = null;
let loadingUiTimer = null;
let isLoadingUiVisible = false;
let activeFetchController = null;
let activeRequestId = 0;
let isNavigatingToService = false;
let scrollTicking = false;


function normalizeKeyword(keyword = '') {
    return String(keyword).trim().toLowerCase();
}

function escapeRegExp(value = '') {
    return String(value).replace(/[.*+?^${}()|[\]\\]/g, '\\$&');
}

function isSearchActive(keyword = state.keyword) {
    return normalizeKeyword(keyword).length > 0;
}

function syncSearchInputState() {
    const hasKeyword = isSearchActive($('#searchdata').val());
    const isLoading = !$('#twsSearchLoading').hasClass('d-none');

    $('.tws-search-wrap').toggleClass('is-searching', hasKeyword);
    $('.tws-search-wrap').toggleClass('is-loading', isLoading);
    $('#twsSummary').toggleClass('tws-summary-searching', hasKeyword);
}

function highlightKeyword(value = '', keyword = state.keyword) {
    const rawValue = String(value ?? '');
    const trimmedKeyword = String(keyword ?? '').trim();

    if (!trimmedKeyword) return escapeHtml(rawValue);

    const matcher = new RegExp(escapeRegExp(trimmedKeyword), 'ig');
    const matches = rawValue.matchAll(matcher);
    let html = '';
    let lastIndex = 0;

    for (const match of matches) {
        const start = match.index ?? 0;
        const end = start + match[0].length;
        html += escapeHtml(rawValue.slice(lastIndex, start));
        html += `<mark class="tws-search-highlight">${escapeHtml(match[0])}</mark>`;
        lastIndex = end;
    }

    if (lastIndex === 0) return escapeHtml(rawValue);

    html += escapeHtml(rawValue.slice(lastIndex));
    return html;
}

function resolveLayananKey() {
    const fromInput = String($('#key').val() || '').trim();
    if (fromInput) return fromInput;

    const path = String(window.location.pathname || '');
    const parts = path.split('/').filter(Boolean);
    return parts.length ? parts[parts.length - 1] : '';
}

function parseJSONStorage(key, fallback) {
    try {
        const raw = localStorage.getItem(key);
        if (!raw) return fallback;
        const parsed = JSON.parse(raw);
        return parsed ?? fallback;
    } catch {
        return fallback;
    }
}

function saveJSONStorage(key, value) {
    localStorage.setItem(key, JSON.stringify(value));
}

function loadPersistedState() {
    const prefs = parseJSONStorage(PREFS_KEY, {});
    state.keyword = typeof prefs.keyword === 'string' ? prefs.keyword : '';
    state.filter = typeof prefs.filter === 'string' ? prefs.filter : 'all';
    state.sort = typeof prefs.sort === 'string' ? prefs.sort : 'name_asc';
    state.viewMode = prefs.viewMode === 'grid' ? 'grid' : 'list';

    const favs = parseJSONStorage(FAVORITES_KEY, []);
    state.favorites = new Set(Array.isArray(favs) ? favs : []);

    const recents = parseJSONStorage(RECENTS_KEY, []);
    state.recents = Array.isArray(recents) ? recents.slice(0, RECENTS_LIMIT) : [];
}

function persistPrefs() {
    saveJSONStorage(PREFS_KEY, {
        keyword: state.keyword,
        filter: state.filter,
        sort: state.sort,
        viewMode: state.viewMode,
    });
}

function persistFavorites() {
    saveJSONStorage(FAVORITES_KEY, Array.from(state.favorites));
}

function persistRecents() {
    saveJSONStorage(RECENTS_KEY, state.recents);
}

function applyStateToControls() {
    $('#searchdata').val(state.keyword);
    $('#twsSort').val(state.sort);

    $('.tws-filter-chip').removeClass('is-active');
    $(`.tws-filter-chip[data-filter="${state.filter}"]`).addClass('is-active');

    $('#twsViewGrid').toggleClass('is-active', state.viewMode === 'grid');
    $('#twsViewList').toggleClass('is-active', state.viewMode === 'list');

    toggleSearchClearButton();
    syncSearchInputState();
}



function renderEmptyLottie() {
    $('#twsEmptyLottie').html(`
<div class="service-ui-empty-panel text-center py-5">
    <img src="${window.AppConfig ? AppConfig.initGlobal : '/'}apps/assets/media/illustrations/empty-content-profile.png" alt="Empty" class="img-fluid mb-3" style="max-width: 180px; opacity: 0.85;">
    <h5 class="fw-bolder text-dark mb-1">Pencarian Tidak Ditemukan</h5>
    <p class="text-muted mb-0 mx-auto" style="max-width: 400px; font-size: .95rem;">
        Maaf, kami tidak dapat menemukan data yang Anda cari. Silakan periksa kembali kata kunci atau filter pencarian Anda.
    </p>
</div>
    `);
}

function getSkeletonCount() {
    if (state.viewMode === 'list') return 6;
    const columns = window.innerWidth >= 768 ? 4 : 1;
    return Math.max(columns * 2, 4);
}

function getServiceIcon(name = '', index = 0) {
    const normalized = String(name).toLowerCase();
    if (normalized.includes('data') || normalized.includes('statistik')) return SERVICE_ICONS[3];
    if (normalized.includes('surat') || normalized.includes('naskah')) return SERVICE_ICONS[1];
    if (normalized.includes('wasdal') || normalized.includes('audit')) return SERVICE_ICONS[2];
    if (normalized.includes('cat') || normalized.includes('konsul')) return SERVICE_ICONS[4];
    if (normalized.includes('dms') || normalized.includes('arsip')) return SERVICE_ICONS[0];
    return SERVICE_ICONS[index % SERVICE_ICONS.length];
}

function getServiceKey(value = {}) {
    if (value.id !== undefined && value.id !== null) return String(value.id);
    if (value.code) return String(value.code);
    return String(value.nama_layanan ?? 'unknown');
}

function escapeHtml(value = '') {
    return String(value)
        .replace(/&/g, '&amp;')
        .replace(/</g, '&lt;')
        .replace(/>/g, '&gt;')
        .replace(/"/g, '&quot;')
        .replace(/'/g, '&#39;');
}

function getServiceDescription(value = {}) {
    const candidates = [
        value.deskripsi_layanan,
        value.deskripsi,
        value.description,
        value.keterangan,
        value.alias,
    ];

    for (const item of candidates) {
        const text = String(item ?? '').trim();
        if (text) return text;
    }

    return 'Belum ada deskripsi layanan.';
}

function showFetchBackdrop() {
    if (typeof showLoading === 'function') {
        showLoading('Memuat layanan...');
    } else {
        $('#twsLoadingBackdrop').stop(true, true).fadeIn(180).css('display', 'flex');
    }
    isLoadingUiVisible = true;
}

function hideFetchBackdrop() {
    if (typeof hideLoading === 'function') {
        hideLoading();
    } else {
        $('#twsLoadingBackdrop').stop(true, true).fadeOut(180);
    }
    isLoadingUiVisible = false;
}

function toggleSearchLoading(isLoading) {
    $('#twsSearchLoading').toggleClass('d-none', !isLoading);
    $('#twsSearchIcon').toggleClass('d-none', isLoading);
    $('#twsClearSearch').prop('disabled', isLoading);
    if (isLoading) $('#twsClearSearch').addClass('d-none');
    syncSearchInputState();
}

function toggleSearchClearButton() {
    const hasKeyword = normalizeKeyword($('#searchdata').val()).length > 0;
    $('#twsClearSearch').toggleClass('d-none', !hasKeyword);
    syncSearchInputState();
}

function updateBackToTopVisibility() {
    const isVisible = window.scrollY > BACK_TO_TOP_THRESHOLD;
    $('#twsBackToTop').toggleClass('is-visible', isVisible);
}

function handleWindowScroll() {
    if (scrollTicking) return;

    scrollTicking = true;
    window.requestAnimationFrame(() => {
        updateBackToTopVisibility();
        scrollTicking = false;
    });
}

function showSkeleton(count = getSkeletonCount()) {
    const $container = $('#loaded');
    const skeletonTpl = state.viewMode === 'list'
        ? $('#twsSkeletonTemplateList').html()
        : $('#twsSkeletonTemplateGrid').html();

    $('#twsEmptyState').addClass('d-none');
    $('#twsErrorState').addClass('d-none');
    $container
        .toggleClass('tws-list-mode', state.viewMode === 'list')
        .empty();

    for (let i = 0; i < count; i += 1) {
        $container.append(skeletonTpl);
    }
}

function clearLoadingUiTimer() {
    if (!loadingUiTimer) return;
    clearTimeout(loadingUiTimer);
    loadingUiTimer = null;
}

function beginLoadingUi() {
    clearLoadingUiTimer();
    toggleSearchLoading(true);
    loadingUiTimer = setTimeout(() => {
        showSkeleton(getSkeletonCount());
        showFetchBackdrop();
        loadingUiTimer = null;
    }, LOAD_UI_DELAY_MS);
}

function endLoadingUi() {
    clearLoadingUiTimer();
    if (isLoadingUiVisible) {
        hideFetchBackdrop();
    }
    toggleSearchLoading(false);
    toggleSearchClearButton();
}

function getAccessState(value = {}) {
    const allowedValue = value.is_allowed ?? value.allowed ?? value.has_access ?? value.accessible ?? null;
    let isAllowed = true;

    if (allowedValue !== null && allowedValue !== undefined) {
        const normalized = String(allowedValue).toLowerCase();
        isAllowed = !['0', 'false', 'no', 'denied', 'blocked'].includes(normalized);
    }

    const hasUrl = Boolean(value.url);
    const canAccess = hasUrl && isAllowed;

    return {
        canAccess,
        cardDisabledClass: canAccess ? '' : 'is-disabled',
        buttonDisabledClass: canAccess ? '' : 'is-disabled',
        lockIconClass: canAccess ? 'bi-unlock' : 'bi-lock',
        lockStateClass: canAccess ? 'is-open' : 'is-locked',
        lockLabel: canAccess ? 'Akses tersedia' : 'Akses terbatas',
    };
}

function setSummary(total, shown, accessible, updated, favorites) {
    const text = `${shown} ditampilkan dari ${total} layanan | ${accessible} bisa diakses | ${updated} sudah update | ${favorites} favorit`;
    $('#twsSummary').text(text);
}

function updateFilterCounts(list = []) {
    const keyword = normalizeKeyword(state.keyword);
    const baseList = keyword
        ? list.filter((item) => {
            const name = String(item.nama_layanan ?? '').toLowerCase();
            const code = String(item.code ?? '').toLowerCase();
            return name.includes(keyword) || code.includes(keyword);
        })
        : [...list];

    const counts = {
        all: baseList.length,
        updated: baseList.filter((item) => Boolean(item.last_upload_at)).length,
        pending: baseList.filter((item) => !item.last_upload_at).length,
        accessible: baseList.filter((item) => getAccessState(item).canAccess).length,
        favorite: baseList.filter((item) => state.favorites.has(getServiceKey(item))).length,
    };

    $('.tws-filter-chip').each(function () {
        const key = String($(this).data('filter') || 'all');
        const label = FILTER_LABELS[key] || key;
        const count = counts[key] ?? 0;
        $(this).text(`${label} (${count})`);
    });
}

function setAccessHint(total, accessible) {
    const showHint = total > 0 && accessible === 0;
    $('#twsAccessHint').toggleClass('d-none', !showHint);
}

function setEmptyStateContext(context = 'default') {
    if (context === 'search') {
        $('#twsEmptyTitle').text('Pencarian Tidak Ditemukan');
        $('#twsEmptyDesc').text('Tidak ada data seleksi yang cocok dengan pencarian Anda.');
        return;
    }

    if (context === 'filter') {
        $('#twsEmptyTitle').text('Filter Tidak Ditemukan');
        $('#twsEmptyDesc').text('Tidak ada data seleksi yang cocok dengan filter Anda.');
        return;
    }

    $('#twsEmptyTitle').text('Layanan tidak ditemukan.');
    $('#twsEmptyDesc').text('Silakan muat ulang data atau hubungi admin layanan.');
}

function compareUpdatedDesc(a, b) {
    const tA = a.last_upload_at ? Date.parse(a.last_upload_at) || 0 : 0;
    const tB = b.last_upload_at ? Date.parse(b.last_upload_at) || 0 : 0;
    return tB - tA;
}

function getRenderedList(list = []) {
    let rendered = [...list];

    const keyword = normalizeKeyword(state.keyword);
    if (keyword) {
        rendered = rendered.filter((item) => {
            const name = String(item.nama_layanan ?? '').toLowerCase();
            const code = String(item.code ?? '').toLowerCase();
            return name.includes(keyword) || code.includes(keyword);
        });
    }

    rendered = rendered.filter((item) => {
        const accessState = getAccessState(item);
        const isFavorite = state.favorites.has(getServiceKey(item));

        if (state.filter === 'updated') return Boolean(item.last_upload_at);
        if (state.filter === 'pending') return !item.last_upload_at;
        if (state.filter === 'accessible') return accessState.canAccess;
        if (state.filter === 'favorite') return isFavorite;
        return true;
    });

    rendered.sort((a, b) => {
        if (state.sort === 'updated_desc') return compareUpdatedDesc(a, b);

        if (state.sort === 'pending_first') {
            const aPending = a.last_upload_at ? 1 : 0;
            const bPending = b.last_upload_at ? 1 : 0;
            if (aPending !== bPending) return aPending - bPending;
            return String(a.nama_layanan ?? '').localeCompare(String(b.nama_layanan ?? ''), 'id');
        }

        if (state.sort === 'favorite_first') {
            const aFav = state.favorites.has(getServiceKey(a)) ? 1 : 0;
            const bFav = state.favorites.has(getServiceKey(b)) ? 1 : 0;
            if (aFav !== bFav) return bFav - aFav;
            return String(a.nama_layanan ?? '').localeCompare(String(b.nama_layanan ?? ''), 'id');
        }

        return String(a.nama_layanan ?? '').localeCompare(String(b.nama_layanan ?? ''), 'id');
    });

    return rendered;
}

function renderServices(list = []) {
    const total = list.length;
    const accessible = list.filter((item) => getAccessState(item).canAccess).length;
    const updated = list.filter((item) => Boolean(item.last_upload_at)).length;
    const favorites = list.filter((item) => state.favorites.has(getServiceKey(item))).length;

    const rendered = getRenderedList(list);
    updateFilterCounts(list);
    setSummary(total, rendered.length, accessible, updated, favorites);
    setAccessHint(total, accessible);

    const $loaded = $('#loaded');
    $loaded.toggleClass('tws-list-mode', state.viewMode === 'list');
    $loaded.html('');

    if (!rendered.length) {
        const emptyCtx = state.keyword ? 'search' : (state.filter !== 'all' ? 'filter' : 'default');
        setEmptyStateContext(emptyCtx);
        $('#twsEmptyState').removeClass('d-none');
        return;
    }

    $('#twsEmptyState').addClass('d-none');

    $.each(rendered, function (index, value) {
        const iconSvg = getServiceIcon(value.nama_layanan, index);
        const accessState = getAccessState(value);
        const toneClass = `tws-tone-${(index % 4) + 1}`;
        const ringClass = value.last_upload_at ? 'tws-ring-high' : 'tws-ring-low';
        const serviceKey = getServiceKey(value);
        const isFavorite = state.favorites.has(serviceKey);
        const isListMode = state.viewMode === 'list';
        const serviceNameText = String(value.nama_layanan ?? 'Nama tidak ada');
        const serviceDescriptionText = getServiceDescription(value);
        const serviceName = highlightKeyword(serviceNameText);
        const serviceDescription = highlightKeyword(serviceDescriptionText);
        const serviceNameAttr = escapeHtml(serviceNameText);
        const favoriteLabel = isFavorite ? 'Hapus favorit' : 'Tambah favorit';
        const accessButtonLabel = '';
        const colClass = state.viewMode === 'list' ? 'col-12 tws-col-list' : 'col-12 col-sm-6 col-md-4 col-xl-2 tws-col-grid';

        const favoriteButtonInline = `
            <button type="button" class="btn tws-fav-btn tws-fav-btn-inline ${isFavorite ? 'is-active' : ''}" aria-label="${favoriteLabel}" title="${favoriteLabel}">
                <i class="bi ${isFavorite ? 'bi-star-fill' : 'bi-star'}"></i>
            </button>
        `;

        const favoriteButtonFloating = `
            <button type="button" class="btn tws-fav-btn ${isFavorite ? 'is-active' : ''}" aria-label="${favoriteLabel}" title="${favoriteLabel}">
                <i class="bi ${isFavorite ? 'bi-star-fill' : 'bi-star'}"></i>
            </button>
        `;

        const card = `
            <div class="${colClass} tw-animate-entry" style="--animation-order: ${index};">
              <div class="card h-100 p-4 rounded-4 border tws-service-card tws-card-soft tws-anim-card overflow-hidden position-relative ${toneClass} ${accessState.cardDisabledClass}"
                  role="button" tabindex="${accessState.canAccess ? '0' : '-1'}"
                  aria-label="Buka layanan ${serviceNameAttr}"
                  data-url="${accessState.canAccess ? AppConfig.initGlobal + value.url : ''}"
                  data-can-access="${accessState.canAccess ? 1 : 0}"
                  data-service-key="${serviceKey}"
                  data-key="${value.id}"
                  data-code="${value.code}">
                ${isListMode ? '' : favoriteButtonFloating}
                
                <div class="position-absolute tws-bg-icon-wrapper">
                    <div class="tws-bg-icon-svg">${iconSvg}</div>
                </div>

                <div class="card-body p-0" style="position: relative; z-index: 1;">
                  <div class="tws-content-wrap">
                    <span class="tws-service-icon ${ringClass}">${iconSvg}</span>
                    <h5 class="fw-bold tws-service-title">${serviceName}</h5>
                    <p class="tws-service-desc">${serviceDescription}</p>
                </div>
                <div class="tws-card-actions">
                    ${isListMode ? `<div class="tws-card-icons-row">${favoriteButtonInline}</div>` : ''}
                    <a class="tws-access-btn ${accessState.buttonDisabledClass}" aria-label="Akses layanan" title="Buka layanan ini">
                        <i class="bi bi-arrow-right"></i>
                        ${accessButtonLabel ? `<span class="tws-access-btn-text">${accessButtonLabel}</span>` : ''}
                    </a>
                </div>
              </div>
            </div>
          </div>
        `;
        $loaded.append(card);
    });
}

async function loadData(options = {}) {
    const layananID = resolveLayananKey();
    if (activeFetchController) activeFetchController.abort();

    activeFetchController = new AbortController();
    const requestId = ++activeRequestId;


    beginLoadingUi();

    try {
        const response = await fetch(AppConfig.initGlobal + 'fetch-layanan-timkerja', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            signal: activeFetchController.signal,
            body: JSON.stringify({
                keyword: '',
                layanan_id: layananID,
            }),
        });

        if (!response.ok) throw new Error('Fetch layanan gagal');

        const data = await response.json();
        if (requestId !== activeRequestId) return;

        pageLoaded(data);

    } catch (error) {
        if (error.name === 'AbortError') return;
        if (requestId !== activeRequestId) return;
        $('#loaded').empty();
        $('#twsEmptyState').addClass('d-none');
        $('#twsErrorState').removeClass('d-none');
        $('#twsSummary').text('Gagal memuat ringkasan layanan.');
    } finally {
        if (requestId !== activeRequestId) return;
        endLoadingUi();
    }
}

function pageLoaded(data) {
    $('#twsErrorState').addClass('d-none');

    const percent = Number(data?.progress?.percent || 0);
    $('#myProgressBar')
        .css('width', percent + '%')
        .attr('aria-valuenow', percent);
    $('#myProgressLabel').text(percent + '%');

    const list = Array.isArray(data?.list) ? data.list : [];
    state.latestRawList = list;

    if (list.length > 0) {
        const scopeName = (list[0].timkerja ?? '-')
            .replace(/tim kerja/gi, '')
            .replace(/\bdan\b/gi, '&')
            .trim();
        $('#twsScopeName').text(scopeName || '-');
    } else {
        $('#twsScopeName').text('-');
    }

    renderServices(state.latestRawList);
}

function markRecent(serviceKey) {
    state.recents = [serviceKey, ...state.recents.filter((key) => key !== serviceKey)].slice(0, RECENTS_LIMIT);
    persistRecents();
}

function accessServiceCard($card) {
    if (isNavigatingToService) return;

    const canAccess = Number($card.data('can-access')) === 1;
    const url = $card.data('url');
    const serviceKey = String($card.data('service-key') ?? '');
    const serviceName = $card.find('.tws-service-name').text().trim() || 'Layanan ini';

    if (!canAccess || !url) {
        if (typeof notifyWarning === 'function') {
            notifyWarning(`Layanan "${serviceName}" tidak dapat diakses. Anda belum memiliki izin akses untuk modul ini.`);
        } else if (typeof Swal !== 'undefined') {
            Swal.fire({
                icon: 'warning',
                title: 'Akses Layanan Terkunci',
                text: `Anda belum memiliki izin akses untuk modul "${serviceName}". Silakan hubungi Administrator.`,
                confirmButtonColor: '#1040c1'
            });
        }
        return;
    }

    if (serviceKey) markRecent(serviceKey);

    isNavigatingToService = true;
    const $btn = $card.find('.tws-access-btn');
    $btn
        .addClass('is-loading')
        .html('<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span>');
    $card.attr('aria-busy', 'true');

    setTimeout(() => {
        window.location.href = url;
    }, 120);
}


function toggleFavorite($card) {
    const serviceKey = String($card.data('service-key') ?? '');
    if (!serviceKey) return;

    if (state.favorites.has(serviceKey)) {
        state.favorites.delete(serviceKey);
    } else {
        state.favorites.add(serviceKey);
    }

    persistFavorites();
    renderServices(state.latestRawList);
}

function scheduleLoadData(keyword) {
    // Deprecated: Search now filters state.latestRawList in-memory instantly
}

$(document).on('click', '.tws-service-card', function (event) {
    if ($(event.target).closest('.tws-access-btn, .tws-fav-btn').length) return;
    accessServiceCard($(this));
});

$(document).on('click', '.tws-access-btn', function (event) {
    event.preventDefault();
    event.stopPropagation();
    accessServiceCard($(this).closest('.tws-service-card'));
});

$(document).on('click', '.tws-fav-btn', function (event) {
    event.preventDefault();
    event.stopPropagation();
    toggleFavorite($(this).closest('.tws-service-card'));
});

$(document).on('keydown', '.tws-service-card', function (event) {
    if (event.key !== 'Enter' && event.key !== ' ') return;
    event.preventDefault();
    accessServiceCard($(this));
});

$('#searchdata').on('input', function () {
    state.keyword = $(this).val();
    persistPrefs();
    toggleSearchClearButton();
    renderServices(state.latestRawList);
});

$('#searchdata').on('keydown', function (event) {
    if (event.key !== 'Enter') return;
    event.preventDefault();
});

$('#twsResetSearch, #twsClearSearch').on('click', function () {
    $('#searchdata').val('').trigger('focus');
    state.keyword = '';
    persistPrefs();
    toggleSearchClearButton();
    renderServices(state.latestRawList);
});

$('#twsRetryLoad, #twsReload').on('click', function () {
    loadData({ force: true });
});

$('.tws-filter-chip').on('click', function () {
    state.filter = String($(this).data('filter') || 'all');
    persistPrefs();
    $('.tws-filter-chip').removeClass('is-active');
    $(this).addClass('is-active');
    renderServices(state.latestRawList);
});

$('#twsSort').on('change', function () {
    state.sort = String($(this).val() || 'name_asc');
    persistPrefs();
    renderServices(state.latestRawList);
});

$('#twsViewGrid, #twsViewList').on('click', function () {
    state.viewMode = this.id === 'twsViewList' ? 'list' : 'grid';
    persistPrefs();
    $('#twsViewGrid').toggleClass('is-active', state.viewMode === 'grid');
    $('#twsViewList').toggleClass('is-active', state.viewMode === 'list');
    renderServices(state.latestRawList);
});

$('#twsRequestAccess').on('click', function () {
    const el = document.getElementById('twsAccessModal');
    if (el && typeof bootstrap !== 'undefined' && bootstrap.Modal) {
        const modal = bootstrap.Modal.getOrCreateInstance(el);
        modal.show();
        return;
    }

    if (typeof notifyInfo === 'function') {
        notifyInfo('Hubungi admin untuk pengajuan akses layanan.');
    }
});

$('#twsOpenContactAdmin').on('click', function () {
    window.location.href = 'mailto:admin@kanreg3.go.id?subject=Pengajuan%20Akses%20Layanan&body=Halo%20Admin%2C%20saya%20ingin%20mengajukan%20akses%20layanan.';
});

$(document).on('keydown', function (event) {
    const target = event.target;
    const isEditable = target && (
        target.tagName === 'INPUT' ||
        target.tagName === 'TEXTAREA' ||
        target.isContentEditable
    );

    if (event.key === '/' && !isEditable) {
        event.preventDefault();
        $('#searchdata').trigger('focus');
        return;
    }

    if (event.key === 'Escape') {
        if (isEditable && target.id !== 'searchdata') return;
        if (!normalizeKeyword($('#searchdata').val())) return;
        $('#searchdata').val('');
        state.keyword = '';
        persistPrefs();
        toggleSearchClearButton();
        renderServices(state.latestRawList);
    }
});

$(window).on('resize', function () {
    if (isLoadingUiVisible) {
        showSkeleton(getSkeletonCount());
    }
});

$(window).on('scroll', handleWindowScroll);

$('#twsBackToTop').on('click', function () {
    window.scrollTo({
        top: 0,
        behavior: 'smooth',
    });
});

loadPersistedState();
applyStateToControls();
renderEmptyLottie();
loadData({ force: true });
updateBackToTopVisibility();

