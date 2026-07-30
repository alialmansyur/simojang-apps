(function () {
    const form = document.getElementById('systemSettingForm');
    const saveBtn = document.getElementById('systemSettingSaveBtn');
    if (!form || !saveBtn) {
        return;
    }

    const defaults = {
        app__name: 'Simojang',
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
    }

    function clearFieldErrors() {
        form.querySelectorAll('.is-invalid').forEach(function (el) {
            el.classList.remove('is-invalid');
            el.removeAttribute('title');
        });
    }

    function applyValues(map) {
        Object.keys(defaults).forEach(function (name) {
            const input = form.querySelector('[name="' + name + '"]');
            if (!input) return;
            const sourceKey = name.replace('__', '.');
            const val = map[sourceKey];
            input.value = (val === undefined || val === null || val === '') ? defaults[name] : String(val);
        });
    }

    async function loadData() {
        const res = await fetch(buildAppUrl('api/manage-setting/data'), { credentials: 'same-origin' });
        const json = await res.json();
        if (!res.ok || !json.status) {
            throw new Error(json.message || 'Gagal memuat system setting');
        }
        applyValues(json.data || {});
    }

    function getPayload() {
        const fd = new FormData(form);
        const payload = {};
        fd.forEach(function (value, key) {
            payload[key] = String(value || '').trim();
        });
        return payload;
    }

    async function saveData() {
        const payload = getPayload();
        clearFieldErrors();
        saveBtn.disabled = true;
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
            showToast(json.message || 'System setting berhasil disimpan', 'success');
        } finally {
            saveBtn.disabled = false;
        }
    }

    form.addEventListener('submit', function (e) {
        e.preventDefault();
        saveData().catch(function (err) {
            showToast(err.message, 'danger');
        });
    });

    loadData().catch(function (err) {
        applyValues({});
        showToast(err.message, 'danger');
    });
})();
