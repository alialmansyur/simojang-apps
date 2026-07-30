(function (window, $) {
    'use strict';

    if (window.__serviceTableUiInitialized === true) return;
    window.__serviceTableUiInitialized = true;
    if (!window || !$) return;
    const FALLBACK_HIDE_MS = 650;
    const SHOW_LOADING_DELAY_MS = 120;

    function debounce(fn, delay) {
        let t = null;
        return function (...args) {
            clearTimeout(t);
            t = setTimeout(() => fn.apply(this, args), delay);
        };
    }

    function formatNumber(value, digits = 0) {
        const n = Number(value || 0);
        return new Intl.NumberFormat('id-ID', {
            minimumFractionDigits: digits,
            maximumFractionDigits: digits,
        }).format(n);
    }

    function formatDateTime(value) {
        if (!value) return '-';
        const d = new Date(value);
        if (Number.isNaN(d.getTime())) return value;
        return d.toLocaleString('id-ID', {
            year: 'numeric',
            month: 'short',
            day: '2-digit',
            hour: '2-digit',
            minute: '2-digit',
        });
    }

    function escapeHtml(value) {
        return String(value ?? '')
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;')
            .replace(/'/g, '&#39;');
    }

    function createBackdrop() {
        if ($('#serviceUiBackdrop').length) return;
        $('body').append(`
            <div id="serviceUiBackdrop" class="service-ui-backdrop">
                <div class="service-ui-backdrop-box">
                    <span class="spinner-border" role="status" aria-hidden="true"></span>
                    <span>Memuat data...</span>
                </div>
            </div>
        `);
    }

    function createProcessingState(text = 'Memuat data...') {
        return `
            <div class="service-ui-processing">
                <span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span>
                <span>${escapeHtml(text)}</span>
            </div>
        `;
    }

    function isDetailServiceRoute() {
        const path = String(window.location.pathname || '');
        return /\/apps-[^/]+-detail(?:\/|$)/.test(path);
    }

    function applyProcessingMarkup(config, table) {
        if (!table || typeof table.settings !== 'function') return;
        const settings = table.settings()[0];
        if (!settings || !settings.oLanguage) return;

        const processingMarkup = createProcessingState(config.processingText || 'Memuat data...');
        settings.oLanguage.sProcessing = processingMarkup;

        const $container = $(table.table().container());
        $container.find('.dataTables_processing').html(processingMarkup);
    }

    function showBackdrop() {
        $('#serviceUiBackdrop').addClass('is-show');
    }

    function hideBackdrop() {
        $('#serviceUiBackdrop').removeClass('is-show');
    }

    function getPageSkeletonMarkup(id) {
        return `
            <div class="service-ui-page-skeleton" id="${id}">
                <div class="service-ui-page-skel-topbar">
                    <div class="service-ui-page-skel-pill service-ui-page-skel-pill-lg"></div>
                    <div class="service-ui-page-skel-pill service-ui-page-skel-pill-sm"></div>
                </div>
                <div class="service-ui-page-skel-recap">
                    <div class="service-ui-page-skel-recap-card"></div>
                    <div class="service-ui-page-skel-recap-card"></div>
                    <div class="service-ui-page-skel-recap-card"></div>
                </div>
                <div class="service-ui-page-skel-table">
                    <div class="service-ui-page-skel-row"></div>
                    <div class="service-ui-page-skel-row"></div>
                    <div class="service-ui-page-skel-row"></div>
                    <div class="service-ui-page-skel-row"></div>
                    <div class="service-ui-page-skel-row"></div>
                    <div class="service-ui-page-skel-row"></div>
                </div>
            </div>
        `;
    }

    function mountPageSkeleton(config, table) {
        const pageSkelId = `${config.key}-page-skeleton`;
        if ($(`#${pageSkelId}`).length) return pageSkelId;

        const $table = $(table.table().node());
        const $cardBody = $table.closest('.table-responsive').closest('.card-body');
        const $mount = $cardBody.length ? $cardBody : $table.parent();
        if (!$mount.length) return null;

        const topbarRadius = String($cardBody.find('.service-ui-static-topbar').first().css('border-radius') || '').trim();
        const recapRadius = String($cardBody.find('.service-ui-recap-card').first().css('border-radius') || '').trim();
        const cardRadius = String($mount.closest('.card').first().css('border-radius') || '').trim();

        if (topbarRadius) $mount.css('--service-ui-topbar-radius', topbarRadius);
        if (recapRadius) $mount.css('--service-ui-recap-radius', recapRadius);
        if (cardRadius) $mount.css('--service-ui-card-radius', cardRadius);

        $mount.addClass('service-ui-card-loading');
        $mount.append(getPageSkeletonMarkup(pageSkelId));
        return pageSkelId;
    }

    function showPageSkeleton(key) {
        const $skel = $(`#${key}-page-skeleton`);
        $skel.addClass('is-show');
        const $mount = $skel.closest('.service-ui-card-loading');
        if ($mount.length) $mount.addClass('is-loading');
    }

    function hidePageSkeleton(key) {
        const $skel = $(`#${key}-page-skeleton`);
        $skel.removeClass('is-show');
        const $mount = $skel.closest('.service-ui-card-loading');
        if ($mount.length) $mount.removeClass('is-loading');
    }

    function resolveTableFromTrigger($trigger) {
        const $scope = $trigger.closest('.card, .page-content, .container-sm');
        let $table = $scope.find('table').first();
        if (!$table.length) $table = $('table').first();
        return $table;
    }

    function getTableSkeletonKey($table) {
        let key = String($table.attr('data-service-ui-key') || '').trim();
        if (!key) {
            key = String($table.attr('id') || '').toLowerCase().replace(/[^a-z0-9]/g, '');
        }
        return key;
    }

    function bindReloadIntentEvents() {
        if ($(document).data('service-ui-reload-intent-bound') === 1) return;
        $(document).data('service-ui-reload-intent-bound', 1);
        let clickFallbackTimer = null;
        let clickRevealTimer = null;

        $(document).on('click', '.js-service-reload', function () {
            const $btn = $(this);
            const $table = resolveTableFromTrigger($btn);
            if (!$table.length) return;

            const key = getTableSkeletonKey($table);
            if (clickRevealTimer) clearTimeout(clickRevealTimer);
            clickRevealTimer = setTimeout(function () {
                if (key) showPageSkeleton(key);
                showBackdrop();
                clickRevealTimer = null;
            }, SHOW_LOADING_DELAY_MS);

            if (clickFallbackTimer) clearTimeout(clickFallbackTimer);
            clickFallbackTimer = setTimeout(function () {
                if (clickRevealTimer) {
                    clearTimeout(clickRevealTimer);
                    clickRevealTimer = null;
                }
                if (key) hidePageSkeleton(key);
                hideBackdrop();
            }, FALLBACK_HIDE_MS + SHOW_LOADING_DELAY_MS);
        });
    }

    function getDefaultIcons() {
        return [
            '<svg viewBox="0 0 24 24" aria-hidden="true"><path d="M4 7h16"/><path d="M7 3v4"/><path d="M17 3v4"/><rect x="4" y="7" width="16" height="14" rx="2"/></svg>',
            '<svg viewBox="0 0 24 24" aria-hidden="true"><path d="M3 3v18h18"/><path d="M8 14l3-3 3 2 4-5"/></svg>',
            '<svg viewBox="0 0 24 24" aria-hidden="true"><circle cx="12" cy="12" r="9"/><path d="M12 7v5l3 2"/></svg>'
        ];
    }

    function mountRecap(config, table) {
        const recapId = `serviceUiRecap-${config.key}`;
        if ($(`#${recapId}`).length) return recapId;
        $(`#generic-recap-${config.key}`).remove();

        const icons = getDefaultIcons();
        const cards = config.cards || [
            { id: 'total_data', label: 'Total Data', value: '0' },
            { id: 'metric_1', label: 'Total', value: '0' },
            { id: 'last_update', label: 'Update Terakhir', value: '-' },
        ];

        const html = cards.map((card, idx) => `
            <div class="service-ui-recap-card">
                <span class="service-ui-recap-icon">${card.icon || icons[idx % icons.length]}</span>
                <div>
                    <p class="service-ui-recap-label">${card.label}</p>
                    <h6 class="service-ui-recap-value" id="${config.key}-${card.id}">${card.value || '-'}</h6>
                </div>
            </div>
        `).join('');

        if (table && typeof table.table === 'function') {
            const $table = $(table.table().node());
            const $cardBody = $table.closest('.table-responsive').closest('.card-body');
            if ($cardBody.length) {
                $cardBody.find(`#generic-recap-${config.key}`).remove();
                const $existingRecap = $cardBody.find('.service-ui-recap').first();
                if ($existingRecap.length) {
                    if (!$existingRecap.attr('id')) {
                        $existingRecap.attr('id', recapId);
                    }
                    return $existingRecap.attr('id');
                }
                const $staticTopbar = $cardBody.find('.service-ui-static-topbar').first();
                if ($staticTopbar.length) {
                    $staticTopbar.after(`<div id="${recapId}" class="service-ui-recap">${html}</div>`);
                    return recapId;
                }

                const $tableWrap = $cardBody.find('.table-responsive').first();
                if ($tableWrap.length) {
                    $tableWrap.before(`<div id="${recapId}" class="service-ui-recap">${html}</div>`);
                    return recapId;
                }
            }
        }

        const $mount = $(config.recapMountSelector);
        if (!$mount.length) return null;
        $mount.after(`<div id="${recapId}" class="service-ui-recap">${html}</div>`);
        return recapId;
    }

    function mountTableSkeleton(config, table) {
        const $wrap = $(table.table().container()).closest('.table-responsive');
        const $cardBody = $wrap.closest('.card-body');
        if (!$cardBody.length) return;

        const $tableWrap = $wrap.closest('.service-ui-table-wrap');
        if (!$tableWrap.length) {
            $wrap.wrap('<div class="service-ui-table-wrap"></div>');
            $wrap.before(`
                <div class="service-ui-table-skeleton" id="${config.key}-skeleton">
                    <div class="service-ui-skel-line"></div>
                    <div class="service-ui-skel-line"></div>
                    <div class="service-ui-skel-line"></div>
                    <div class="service-ui-skel-line"></div>
                    <div class="service-ui-skel-line"></div>
                    <div class="service-ui-skel-line"></div>
                </div>
            `);
        }
    }

    function bindTopbarEvents(config, table) {
        const eventNs = `.serviceUiReload${String(config.key || '').replace(/[^a-z0-9_-]/gi, '')}`;
        const $root = $(table.table().node()).closest('.page-content, .container-sm, .container, .container-fluid');
        const $reloadButtons = $root
            .find('.page-heading .js-service-reload, .page-title-actions .js-service-reload, .module-heading-actions .js-service-reload, .service-page-inline-actions .js-service-reload')
            .filter(function () {
                return $(this).closest('.page-heading').length > 0;
            });

        if (!$reloadButtons.length) return;

        $reloadButtons.off(`click${eventNs}`).on(`click${eventNs}`, function () {
            showPageSkeleton(config.key);
            table.ajax.reload(null, false);
            if (config.reloadSummaryOnClick !== false && typeof config.loadSummary === 'function') {
                config.loadSummary();
            }
        });
    }

    function mountDataTableState(config, table) {
        const $skel = $(`#${config.key}-skeleton`);
        let hideFallbackTimer = null;
        let showIntentTimer = null;
        let hasLoadedOnce = false;
        const useInitialLoading = !isDetailServiceRoute();

        const shouldShowTableSkeleton = function (phase) {
            return phase === 'init'
                ? (config.initialTableSkeleton ?? useInitialLoading) !== false
                : config.tableSkeleton === true;
        };

        const shouldShowPageSkeleton = function (phase) {
            return phase === 'init'
                ? (config.initialPageSkeleton ?? useInitialLoading) !== false
                : config.pageSkeleton === true;
        };

        const shouldShowBackdrop = function (phase) {
            return phase === 'init'
                ? (config.initialBackdrop ?? useInitialLoading) !== false
                : config.backdrop === true;
        };

        const clearShowIntent = function () {
            if (!showIntentTimer) return;
            clearTimeout(showIntentTimer);
            showIntentTimer = null;
        };

        const scheduleHideFallback = function () {
            if (hideFallbackTimer) clearTimeout(hideFallbackTimer);
            hideFallbackTimer = setTimeout(function () {
                if ($skel.length) $skel.removeClass('is-show');
                hidePageSkeleton(config.key);
                hideBackdrop();
            }, FALLBACK_HIDE_MS);
        };

        const hideLoadingState = function () {
            clearShowIntent();
            if ($skel.length) $skel.removeClass('is-show');
            hidePageSkeleton(config.key);
            hideBackdrop();
            if (hideFallbackTimer) clearTimeout(hideFallbackTimer);
        };

        const queueLoadingState = function () {
            const phase = hasLoadedOnce ? 'reload' : 'init';
            clearShowIntent();
            if (hideFallbackTimer) clearTimeout(hideFallbackTimer);
            showIntentTimer = setTimeout(function () {
                if (shouldShowTableSkeleton(phase) && $skel.length) $skel.addClass('is-show');
                if (shouldShowPageSkeleton(phase)) showPageSkeleton(config.key);
                if (shouldShowBackdrop(phase)) showBackdrop();
                scheduleHideFallback();
                showIntentTimer = null;
            }, SHOW_LOADING_DELAY_MS);
        };

        table.on('preXhr.dt', queueLoadingState);

        table.on('xhr.dt', function () {
            hasLoadedOnce = true;
            hideLoadingState();
        });

        table.on('error.dt', function () {
            hasLoadedOnce = true;
            hideLoadingState();
        });

        table.on('draw.dt', function () {
            hasLoadedOnce = true;
            hidePageSkeleton(config.key);
            if (hideFallbackTimer) clearTimeout(hideFallbackTimer);
        });

        table.on('processing.dt', function (_ev, _settings, processing) {
            if (processing) {
                queueLoadingState();
                return;
            }
            hasLoadedOnce = true;
            hideLoadingState();
        });
    }

    function createEmptyLottie(text = 'Maaf, kami tidak dapat menemukan data yang Anda cari. Silakan periksa kembali kata kunci atau filter pencarian Anda.') {
        return createEmptyState('Pencarian Tidak Ditemukan', text);
    }

    function createEmptyState(text = 'Pencarian Tidak Ditemukan', desc = 'Maaf, kami tidak dapat menemukan data yang Anda cari. Silakan periksa kembali kata kunci atau filter pencarian Anda.') {
        const message = escapeHtml(text);
        const submessage = escapeHtml(desc);
        return `
            <div class="d-flex flex-column align-items-center justify-content-center text-center mt-5 mb-5 pb-4 tw-animate-entry">
                <img src="${AppConfig.initGlobal}apps/assets/images/empty-content-profile.png" alt="Tidak Ditemukan" style="max-width: 320px; margin-bottom: 2rem;">
                <h5 class="fw-bold" style="color: #1a202c; font-size: 1.35rem;">${message}</h5>
                <p class="text-muted" style="font-size: 1.05rem; max-width: 480px;">${submessage}</p>
            </div>
        `;
    }

    window.ServiceTableUI = {
        createEmptyLottie,
        createEmptyState: createEmptyState,
        formatNumber,
        formatDateTime,
        createProcessingState,
        setup: function (config) {
            if (!config || !config.table) return;
            $(config.table.table().node())
                .attr('data-service-ui-managed', '1')
                .attr('data-service-ui-key', String(config.key || '').trim());
            createBackdrop();
            applyProcessingMarkup(config, config.table);
            mountTableSkeleton(config, config.table);
            mountRecap(config, config.table);
            mountPageSkeleton(config, config.table);
            bindTopbarEvents(config, config.table);
            mountDataTableState(config, config.table);
            hidePageSkeleton(config.key);
            hideBackdrop();
        },
        showLoadingForTable: function (tableNode) {
            if (!tableNode) return;
            const $table = $(tableNode);
            let key = String($table.attr('data-service-ui-key') || '').trim();
            if (!key) {
                key = String($table.attr('id') || '').toLowerCase().replace(/[^a-z0-9]/g, '');
            }
            if (!key) return;
            showPageSkeleton(key);
            showBackdrop();
        },
        showGlobalLoading: function () {
            showBackdrop();
        },
        hideGlobalLoading: function () {
            hideBackdrop();
        }
    };

    bindReloadIntentEvents();

})(window, window.jQuery);
