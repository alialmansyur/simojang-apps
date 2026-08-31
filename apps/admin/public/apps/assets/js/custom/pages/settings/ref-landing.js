(function () {
    const grid = document.getElementById('refLandingGrid');
    

    if (!grid) return;

    function showToast(message, type) {
        if (!message) return;
        if (type === 'success') {
            notifySuccess(message);
            return;
        }
        if (type === 'warning') {
            notifyInfo(message);
            return;
        }
        notifyError(message);
    }

    function escapeHtml(text) {
        return String(text)
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;')
            .replace(/'/g, '&#039;');
    }

    function buildAppUrl(path) {
        const rawBase = window.AppConfig && AppConfig.initGlobal ? String(AppConfig.initGlobal) : '/';
        if (/^https?:\/\//i.test(String(path || ''))) {
            return String(path);
        }
        const base = rawBase.endsWith('/') ? rawBase : (rawBase + '/');
        const cleanPath = String(path || '').replace(/^\/+/, '');
        return base + cleanPath;
    }

    function renderEmpty(message) {
        grid.innerHTML = ''
            + '<div class="col-12 text-center py-4 ref-empty">'
            + '  <div class="d-flex flex-column justify-content-center align-items-center">'
            + '    <div class="display-1 text-muted mb-3"><i class="bi bi-inbox"></i></div>'
            + '    <p class="text-muted mt-2 mb-0">' + escapeHtml(message) + '</p>'
            + '  </div>'
            + '</div>';
    }

    function renderTables(items) {
        if (!Array.isArray(items) || items.length === 0) {
            renderEmpty('Belum ada tabel referensi yang bisa Anda akses.');
            return;
        }

        grid.innerHTML = items.map((item) => {
            const targetUrl = buildAppUrl(item.url || ('ref/' + item.slug));
            return ''
                + '<div class="col-12 col-sm-6 col-md-4 col-xl-2">'
                + '  <a class="ref-menu-link" href="' + escapeHtml(targetUrl) + '">'
                + '    <div class="card h-100 ref-menu-card mb-0">'
                + '      <div class="card-body d-flex flex-column text-center align-items-center justify-content-center">'
                + '        <span class="ref-menu-icon"><i class="' + escapeHtml(item.icon || 'bi bi-table') + '"></i></span>'
                + '        <h5 class="fw-bold ref-menu-title text-center">' + escapeHtml(item.label || item.slug) + '</h5>'
                + '      </div>'
                + '    </div>'
                + '  </a>'
                + '</div>';
        }).join('');
    }

    async function init() {
        try {
            const res = await fetch(buildAppUrl('api/ref/tables'), { credentials: 'same-origin' });
            const json = await res.json();
            if (!res.ok || !json.status) {
                throw new Error(json.message || 'Gagal memuat daftar tabel referensi');
            }
            renderTables(json.data || []);
        } catch (err) {
            renderEmpty('Gagal memuat tabel referensi.');
            showToast(err.message, 'danger');
        }
    }

    init();
})();

