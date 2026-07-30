(function (window, $) {
    'use strict';

    if (window.__serviceTopbarActionsInitialized === true) return;
    window.__serviceTopbarActionsInitialized = true;
    if (!window || !$ || !$.fn || !$.fn.DataTable) return;
    let globalReloadFallbackTimer = null;
    let globalRevealTimer = null;
    const FALLBACK_HIDE_MS = 650;
    const SHOW_LOADING_DELAY_MS = 120;

    function formatNumber(value) {
        return new Intl.NumberFormat('id-ID').format(Number(value || 0));
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

    function getProcessingMarkup(text) {
        if (window.ServiceTableUI && typeof window.ServiceTableUI.createProcessingState === 'function') {
            return window.ServiceTableUI.createProcessingState(text || 'Memuat data...');
        }
        return `
            <div class="service-ui-processing">
                <span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span>
                <span>${text || 'Memuat data...'}</span>
            </div>
        `;
    }

    function getRecapKey($table) {
        const tableId = String($table.attr('id') || 'service').toLowerCase();
        return tableId.replace(/[^a-z0-9]/g, '');
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

    function ensurePageSkeleton($table, key) {
        const skelId = `${key}-page-skeleton`;
        if ($(`#${skelId}`).length) return skelId;

        const $tableWrap = $table.closest('.table-responsive');
        const $cardBody = $tableWrap.closest('.card-body');
        const $mount = $cardBody.length ? $cardBody : $table.parent();
        if (!$mount.length) return null;

        const topbarRadius = String($cardBody.find('.service-ui-static-topbar').first().css('border-radius') || '').trim();
        const recapRadius = String($cardBody.find('.service-ui-recap-card').first().css('border-radius') || '').trim();
        const cardRadius = String($mount.closest('.card').first().css('border-radius') || '').trim();

        if (topbarRadius) $mount.css('--service-ui-topbar-radius', topbarRadius);
        if (recapRadius) $mount.css('--service-ui-recap-radius', recapRadius);
        if (cardRadius) $mount.css('--service-ui-card-radius', cardRadius);

        $mount.addClass('service-ui-card-loading');
        $mount.append(getPageSkeletonMarkup(skelId));
        return skelId;
    }

    function showPageSkeletonByKey(key) {
        if (!key) return;
        const $skel = $(`#${key}-page-skeleton`);
        $skel.addClass('is-show');
        const $mount = $skel.closest('.service-ui-card-loading');
        if ($mount.length) $mount.addClass('is-loading');
    }

    function hidePageSkeletonByKey(key) {
        if (!key) return;
        const $skel = $(`#${key}-page-skeleton`);
        $skel.removeClass('is-show');
        const $mount = $skel.closest('.service-ui-card-loading');
        if ($mount.length) $mount.removeClass('is-loading');
    }

    function scheduleGlobalFallbackHide(key) {
        if (globalReloadFallbackTimer) clearTimeout(globalReloadFallbackTimer);
        globalReloadFallbackTimer = setTimeout(function () {
            if (globalRevealTimer) {
                clearTimeout(globalRevealTimer);
                globalRevealTimer = null;
            }
            hidePageSkeletonByKey(key);
            if (window.ServiceTableUI && typeof window.ServiceTableUI.hideGlobalLoading === 'function') {
                window.ServiceTableUI.hideGlobalLoading();
            }
        }, FALLBACK_HIDE_MS);
    }

    function clearGlobalLoadingTimers() {
        if (globalRevealTimer) {
            clearTimeout(globalRevealTimer);
            globalRevealTimer = null;
        }
        if (globalReloadFallbackTimer) {
            clearTimeout(globalReloadFallbackTimer);
            globalReloadFallbackTimer = null;
        }
    }

    function ensureGenericRecap($table, key) {
        const $cardBody = $table.closest('.card-body');
        if (!$cardBody.length) return null;
        if ($cardBody.find(`#serviceUiRecap-${key}`).length) return null;
        if ($cardBody.find('.service-ui-recap').length) return null;

        const recapId = `generic-recap-${key}`;
        const html = `
            <div id="${recapId}" class="service-ui-recap">
                <div class="service-ui-recap-card">
                    <span class="service-ui-recap-icon">
                        <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M4 7h16"/><path d="M7 3v4"/><path d="M17 3v4"/><rect x="4" y="7" width="16" height="14" rx="2"/></svg>
                    </span>
                    <div>
                        <p class="service-ui-recap-label">Total Data</p>
                        <h6 class="service-ui-recap-value" id="${key}-recap-total">0</h6>
                    </div>
                </div>
                <div class="service-ui-recap-card">
                    <span class="service-ui-recap-icon">
                        <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M3 3v18h18"/><path d="M8 14l3-3 3 2 4-5"/></svg>
                    </span>
                    <div>
                        <p class="service-ui-recap-label">Data Ditampilkan</p>
                        <h6 class="service-ui-recap-value" id="${key}-recap-shown">0</h6>
                    </div>
                </div>
                <div class="service-ui-recap-card">
                    <span class="service-ui-recap-icon">
                        <svg viewBox="0 0 24 24" aria-hidden="true"><circle cx="12" cy="12" r="9"/><path d="M12 7v5l3 2"/></svg>
                    </span>
                    <div>
                        <p class="service-ui-recap-label">Update Terakhir</p>
                        <h6 class="service-ui-recap-value" id="${key}-recap-last">-</h6>
                    </div>
                </div>
            </div>
        `;

        const $topbar = $cardBody.find('.service-ui-static-topbar').first();
        if ($topbar.length) {
            $topbar.after(html);
        } else {
            const $tableWrap = $table.closest('.table-responsive');
            if ($tableWrap.length) {
                $tableWrap.before(html);
            } else {
                $cardBody.prepend(html);
            }
        }

        return recapId;
    }

    function normalizeCardStructure($table) {
        const $tableWrap = $table.closest('.table-responsive');
        const $cardBody = $tableWrap.closest('.card-body');
        if (!$cardBody.length) return;

        const $topbar = $cardBody.find('.service-ui-static-topbar').first();
        if (!$topbar.length) return;

        let $recap = $cardBody.find('.service-ui-recap').first();
        if (!$recap.length) {
            const $globalRecap = $('.service-ui-recap').not($cardBody.find('.service-ui-recap')).first();
            if ($globalRecap.length) {
                $recap = $globalRecap;
                $topbar.after($recap);
            }
        }

        if ($recap.length) {
            if (!$recap.prev().is($topbar)) {
                $topbar.after($recap);
            }
            if ($tableWrap.prev()[0] !== $recap[0]) {
                $recap.after($tableWrap);
            }
        } else if ($tableWrap.prev()[0] !== $topbar[0]) {
            $topbar.after($tableWrap);
        }
    }

    function extractLastUpdate(rows) {
        const candidates = ['updated_at', 'created_at', 'tanggal_upload', 'last_update', 'period_end_date', 'syncdate2'];
        let latest = null;

        rows.forEach((row) => {
            if (!row || typeof row !== 'object') return;
            for (let i = 0; i < candidates.length; i += 1) {
                const val = row[candidates[i]];
                if (!val) continue;
                const ts = Date.parse(val);
                if (Number.isNaN(ts)) continue;
                if (latest === null || ts > latest) latest = ts;
                break;
            }
        });

        return latest ? new Date(latest).toISOString() : null;
    }

    function updateGenericRecap(api, key, json) {
        const info = api.page.info();
        const total = Number((json && json.recordsTotal) ?? info.recordsTotal ?? 0);
        const shown = Number((json && json.recordsFiltered) ?? info.recordsDisplay ?? 0);
        const rows = Array.isArray(json && json.data) ? json.data : api.rows({ search: 'applied' }).data().toArray();
        const lastUpdate = extractLastUpdate(rows);

        $(`#${key}-recap-total`).text(formatNumber(total));
        $(`#${key}-recap-shown`).text(formatNumber(shown));
        $(`#${key}-recap-last`).text(formatDateTime(lastUpdate));
    }

    function resolveTable($trigger) {
        const $scope = $trigger.closest('.card, .page-content, .container-sm');
        let $table = $scope.find('table').first();
        if (!$table.length) {
            $table = $('table').first();
        }
        return $table;
    }

    $(document).on('click', '.js-service-reload', function (event) {
        event.preventDefault();
        const $btn = $(this);
        const $table = resolveTable($btn);

        if (!$table.length || !$.fn.DataTable.isDataTable($table)) return;

        if ($table.attr('data-service-ui-managed') === '1') {
            return;
        }

        const key = String($table.attr('data-service-ui-key') || getRecapKey($table)).trim();
        ensurePageSkeleton($table, key);
        if (globalRevealTimer) clearTimeout(globalRevealTimer);
        globalRevealTimer = setTimeout(function () {
            showPageSkeletonByKey(key);
            if (window.ServiceTableUI && typeof window.ServiceTableUI.showGlobalLoading === 'function') {
                window.ServiceTableUI.showGlobalLoading();
            }
            globalRevealTimer = null;
        }, SHOW_LOADING_DELAY_MS);
        scheduleGlobalFallbackHide(key);

        const dt = $table.DataTable();
        dt.ajax.reload(null, false);
    });

    $(document).on('init.dt', function (_, settings) {
        if ($('link[href*="service-table-ui.css"]').length === 0) return;

        const api = new $.fn.dataTable.Api(settings);
        const $table = $(api.table().node());
        const processingMarkup = getProcessingMarkup();
        settings.oLanguage = settings.oLanguage || {};
        settings.oLanguage.sProcessing = processingMarkup;
        $(api.table().container()).find('.dataTables_processing').html(processingMarkup);

        setTimeout(function () {
            if ($table.attr('data-service-ui-managed') === '1') return;
            normalizeCardStructure($table);
            if ($table.closest('.card-body').find('.service-ui-recap[id^="serviceUiRecap-"]').length) return;

            const key = getRecapKey($table);
            $table.attr('data-service-ui-key', key);
            ensurePageSkeleton($table, key);
            const recapId = ensureGenericRecap($table, key);
            let hideFallbackTimer = null;

            let showIntentTimer = null;

            const clearTimers = function () {
                if (showIntentTimer) {
                    clearTimeout(showIntentTimer);
                    showIntentTimer = null;
                }
                if (hideFallbackTimer) {
                    clearTimeout(hideFallbackTimer);
                    hideFallbackTimer = null;
                }
            };

            const scheduleHideFallback = function () {
                if (hideFallbackTimer) clearTimeout(hideFallbackTimer);
                hideFallbackTimer = setTimeout(function () {
                    hidePageSkeletonByKey(key);
                }, FALLBACK_HIDE_MS);
            };

            const queueShowLoading = function () {
                if (showIntentTimer) clearTimeout(showIntentTimer);
                showIntentTimer = setTimeout(function () {
                    showPageSkeletonByKey(key);
                    scheduleHideFallback();
                    showIntentTimer = null;
                }, SHOW_LOADING_DELAY_MS);
            };

            api.on('preXhr.dt', function () {
                queueShowLoading();
            });

            api.on('xhr.dt', function (_ev, _s, json) {
                if (recapId) updateGenericRecap(api, key, json || null);
                clearTimers();
                clearGlobalLoadingTimers();
                hidePageSkeletonByKey(key);
                if (window.ServiceTableUI && typeof window.ServiceTableUI.hideGlobalLoading === 'function') {
                    window.ServiceTableUI.hideGlobalLoading();
                }
            });

            api.on('error.dt', function () {
                clearTimers();
                clearGlobalLoadingTimers();
                hidePageSkeletonByKey(key);
                if (window.ServiceTableUI && typeof window.ServiceTableUI.hideGlobalLoading === 'function') {
                    window.ServiceTableUI.hideGlobalLoading();
                }
            });

            api.on('draw.dt', function () {
                if (recapId) updateGenericRecap(api, key, null);
                clearTimers();
                clearGlobalLoadingTimers();
                hidePageSkeletonByKey(key);
                if (window.ServiceTableUI && typeof window.ServiceTableUI.hideGlobalLoading === 'function') {
                    window.ServiceTableUI.hideGlobalLoading();
                }
            });

            api.on('processing.dt', function (_ev, _settings, processing) {
                if (processing) {
                    queueShowLoading();
                    return;
                }
                clearTimers();
                clearGlobalLoadingTimers();
                hidePageSkeletonByKey(key);
                if (window.ServiceTableUI && typeof window.ServiceTableUI.hideGlobalLoading === 'function') {
                    window.ServiceTableUI.hideGlobalLoading();
                }
            });
            if (recapId) updateGenericRecap(api, key, null);
            hidePageSkeletonByKey(key);
            if (window.ServiceTableUI && typeof window.ServiceTableUI.hideGlobalLoading === 'function') {
                window.ServiceTableUI.hideGlobalLoading();
            }
        }, SHOW_LOADING_DELAY_MS);
    });
})(window, window.jQuery);
