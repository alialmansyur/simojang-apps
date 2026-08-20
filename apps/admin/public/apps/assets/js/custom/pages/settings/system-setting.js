(function () {
    const form = document.getElementById('systemSettingForm');
    const saveBtn = document.getElementById('systemSettingSaveBtn');
    const resetBtn = document.getElementById('btnResetSystemForm');
    if (!form || !saveBtn) {
        return;
    }

    const defaults = {
        app__name: 'SIMOJANG',
        app__timezone: 'Asia/Jakarta',
        env__flag: 'production',
        app__maintenance_mode: '0',
        pagination__default_per_page: '10',
        session__timeout_minutes: '60',
        security__max_login_attempt: '5',
        security__lock_duration_minutes: '15',
        security__default_role_code: 'USR',
        upload__max_size_mb: '10',
        upload__allowed_types: 'xls,xlsx,csv,pdf,jpg,jpeg,png,zip',
        app__logo: '',
        app__favicon: '',
    };

    let latestLoadedData = {};

    function showToast(message, type) {
        if (!message) return;
        if (type === 'success') {
            if (typeof notifySuccess === 'function') {
                notifySuccess(message);
            } else if (typeof notify === 'function') {
                notify(message, 'success');
            }
            return;
        }
        if (type === 'warning') {
            if (typeof notifyInfo === 'function') {
                notifyInfo(message);
            } else if (typeof notify === 'function') {
                notify(message, 'warning');
            }
            return;
        }
        if (typeof notifyError === 'function') {
            notifyError(message);
        } else if (typeof notify === 'function') {
            notify(message, 'error');
        }
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

    function setFieldError(fieldName, errorText) {
        const el = form.querySelector('[name="' + fieldName + '"]');
        if (!el) return;
        el.classList.add('is-invalid');
        el.setAttribute('title', errorText || '');

        let container = el.closest('.input-group') || el.closest('div');
        let feedback = container.querySelector('.invalid-feedback');
        if (!feedback) {
            feedback = document.createElement('div');
            feedback.className = 'invalid-feedback d-block';
            container.appendChild(feedback);
        }
        feedback.textContent = errorText || 'Bidang ini tidak valid';
    }

    function clearFieldErrors() {
        form.querySelectorAll('.is-invalid').forEach(function (el) {
            el.classList.remove('is-invalid');
            el.removeAttribute('title');
        });
        form.querySelectorAll('.invalid-feedback').forEach(function (el) {
            el.remove();
        });
    }

    function updateBannerStats(map) {
        const nameEl = document.getElementById('cardAppName');
        const envBadge = document.getElementById('cardEnvBadge');
        const tzEl = document.getElementById('statTimezone');
        const sessEl = document.getElementById('statSessionTimeout');

        const appName = map['app.name'] || defaults.app__name;
        const env = (map['env.flag'] || defaults.env__flag).toLowerCase();
        const tz = map['app.timezone'] || defaults.app__timezone;
        const sess = map['session.timeout_minutes'] || defaults.session__timeout_minutes;

        if (nameEl) nameEl.textContent = appName + ' System Configuration';
        if (tzEl) tzEl.textContent = tz;
        if (sessEl) sessEl.textContent = sess + ' Menit';

        if (envBadge) {
            envBadge.textContent = env.charAt(0).toUpperCase() + env.slice(1);
            if (env === 'production') {
                envBadge.className = 'badge bg-success-subtle text-success border border-success-subtle rounded-pill px-2.5 py-1';
            } else if (env === 'staging') {
                envBadge.className = 'badge bg-warning-subtle text-warning border border-warning-subtle rounded-pill px-2.5 py-1';
            } else {
                envBadge.className = 'badge bg-info-subtle text-info border border-info-subtle rounded-pill px-2.5 py-1';
            }
        }
    }

    function updateBrandPreviews() {
        const logoInput = document.getElementById('appLogo');
        const faviconInput = document.getElementById('appFavicon');
        const logoBox = document.getElementById('logoPreviewBox');
        const faviconBox = document.getElementById('faviconPreviewBox');

        if (logoInput && logoBox) {
            const val = logoInput.value.trim();
            if (val) {
                const src = /^https?:\/\//i.test(val) ? val : buildAppUrl(val);
                logoBox.innerHTML = '<img src="' + src + '" alt="Logo" onerror="this.parentElement.innerHTML=\'<i class=\\\'bi bi-image text-muted fs-5\\\'></i>\'">';
            } else {
                logoBox.innerHTML = '<i class="bi bi-image text-muted fs-5"></i>';
            }
        }

        if (faviconInput && faviconBox) {
            const val = faviconInput.value.trim();
            if (val) {
                const src = /^https?:\/\//i.test(val) ? val : buildAppUrl(val);
                faviconBox.innerHTML = '<img src="' + src + '" alt="Favicon" onerror="this.parentElement.innerHTML=\'<i class=\\\'bi bi-browser-chrome text-muted fs-5\\\'></i>\'">';
            } else {
                faviconBox.innerHTML = '<i class="bi bi-browser-chrome text-muted fs-5"></i>';
            }
        }
    }

    function applyValues(map) {
        latestLoadedData = Object.assign({}, map);
        Object.keys(defaults).forEach(function (name) {
            const input = form.querySelector('[name="' + name + '"]');
            if (!input) return;
            const sourceKey = name.replace('__', '.');
            const val = map[sourceKey];
            const finalVal = (val === undefined || val === null || val === '') ? defaults[name] : String(val);

            if (input.type === 'checkbox') {
                input.checked = (finalVal === '1' || finalVal === 'true' || finalVal === 'on');
            } else {
                input.value = finalVal;
            }
        });

        updateBannerStats(map);
        updateBrandPreviews();
    }

    async function loadData() {
        try {
            const res = await fetch(buildAppUrl('api/manage-setting/data'), { credentials: 'same-origin' });
            const json = await res.json();
            if (!res.ok || !json.status) {
                throw new Error(json.message || 'Gagal memuat system setting');
            }
            applyValues(json.data || {});
        } catch (err) {
            applyValues({});
            showToast(err.message, 'danger');
        }
    }

    function getPayload() {
        const payload = {};
        
        // Loop all recognized default fields to guarantee checkboxes & selects are included
        Object.keys(defaults).forEach(function (key) {
            const el = form.querySelector('[name="' + key + '"]');
            if (!el) return;
            if (el.type === 'checkbox') {
                payload[key] = el.checked ? '1' : '0';
            } else {
                payload[key] = String(el.value || '').trim();
            }
        });

        return payload;
    }

    async function saveData() {
        const payload = getPayload();
        clearFieldErrors();
        saveBtn.disabled = true;
        const originalBtnText = saveBtn.innerHTML;
        saveBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-1" role="status" aria-hidden="true"></span> Menyimpan...';
        
        try {
            const res = await fetch(buildAppUrl('api/manage-setting/save'), {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                credentials: 'same-origin',
                body: JSON.stringify(payload),
            });
            const json = await res.json();
            if (!res.ok || !json.status) {
                const errors = json.errors || {};
                Object.keys(errors).forEach(function (field) {
                    setFieldError(field, errors[field]);
                });
                throw new Error(json.message || 'Gagal menyimpan system setting');
            }
            applyValues(json.data || {});
            showToast(json.message || 'Konfigurasi sistem berhasil disimpan', 'success');
        } finally {
            saveBtn.disabled = false;
            saveBtn.innerHTML = originalBtnText;
        }
    }

    const appLogoInput = document.getElementById('appLogo');
    if (appLogoInput) {
        appLogoInput.addEventListener('input', updateBrandPreviews);
    }
    const appFaviconInput = document.getElementById('appFavicon');
    if (appFaviconInput) {
        appFaviconInput.addEventListener('input', updateBrandPreviews);
    }

    if (resetBtn) {
        resetBtn.addEventListener('click', function () {
            applyValues(latestLoadedData);
            clearFieldErrors();
            if (typeof notifyInfo === 'function') {
                notifyInfo('Formulir telah dimuat ulang sesuai data tersimpan.');
            }
        });
    }

    form.addEventListener('submit', function (e) {
        e.preventDefault();
        saveData().catch(function (err) {
            showToast(err.message, 'danger');
        });
    });

    loadData();
})();

