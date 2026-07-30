(function () {
    const CARD_SELECTOR = '.js-setting-load-card';
    const RELOAD_SELECTOR = '.js-setting-reload';

    function createBackdrop() {
        if (document.getElementById('serviceUiBackdrop')) return;
        const backdrop = document.createElement('div');
        backdrop.id = 'serviceUiBackdrop';
        backdrop.className = 'service-ui-backdrop';
        backdrop.innerHTML = ''
            + '<div class="service-ui-backdrop-box">'
            + '  <span class="spinner-border" role="status" aria-hidden="true"></span>'
            + '  <span>Memuat data...</span>'
            + '</div>';
        document.body.appendChild(backdrop);
    }

    function setBackdrop(show) {
        const backdrop = document.getElementById('serviceUiBackdrop');
        if (!backdrop) return;
        backdrop.classList.toggle('is-show', !!show);
    }

    function skeletonMarkup() {
        return ''
            + '<div class="service-ui-page-skeleton is-show">'
            + '  <div class="service-ui-page-skel-topbar">'
            + '    <div class="service-ui-page-skel-pill service-ui-page-skel-pill-lg"></div>'
            + '    <div class="service-ui-page-skel-pill service-ui-page-skel-pill-sm"></div>'
            + '  </div>'
            + '  <div class="service-ui-page-skel-recap">'
            + '    <div class="service-ui-page-skel-recap-card"></div>'
            + '    <div class="service-ui-page-skel-recap-card"></div>'
            + '    <div class="service-ui-page-skel-recap-card"></div>'
            + '  </div>'
            + '  <div class="service-ui-page-skel-table">'
            + '    <div class="service-ui-page-skel-row"></div>'
            + '    <div class="service-ui-page-skel-row"></div>'
            + '    <div class="service-ui-page-skel-row"></div>'
            + '    <div class="service-ui-page-skel-row"></div>'
            + '    <div class="service-ui-page-skel-row"></div>'
            + '    <div class="service-ui-page-skel-row"></div>'
            + '  </div>'
            + '</div>';
    }

    function mountSkeletons() {
        const cards = document.querySelectorAll(CARD_SELECTOR);
        cards.forEach((card) => {
            if (card.querySelector('.service-ui-page-skeleton')) return;
            card.classList.add('service-ui-card-loading');
            card.insertAdjacentHTML('beforeend', skeletonMarkup());
        });
    }

    function setSkeleton(show) {
        const cards = document.querySelectorAll(CARD_SELECTOR);
        cards.forEach((card) => {
            card.classList.toggle('is-loading', !!show);
            const skeleton = card.querySelector('.service-ui-page-skeleton');
            if (skeleton) {
                skeleton.classList.toggle('is-show', !!show);
            }
        });
    }

    function initReloadButton() {
        document.querySelectorAll(RELOAD_SELECTOR).forEach((btn) => {
            btn.addEventListener('click', function () {
                this.disabled = true;
                setSkeleton(true);
                setBackdrop(true);
                window.location.reload();
            });
        });
    }

    function initInitialLoad() {
        setSkeleton(true);
        setBackdrop(true);

        const hide = function () {
            setTimeout(function () {
                setSkeleton(false);
                setBackdrop(false);
            }, 280);
        };

        if (document.readyState === 'complete') {
            hide();
            return;
        }
        window.addEventListener('load', hide, { once: true });
    }

    createBackdrop();
    mountSkeletons();
    initReloadButton();
    initInitialLoad();
})();
