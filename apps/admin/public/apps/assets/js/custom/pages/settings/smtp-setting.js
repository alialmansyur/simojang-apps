(function () {
    const form = document.getElementById('smtpSettingForm');
    const saveBtn = document.getElementById('smtpSettingSaveBtn');
    if (!form || !saveBtn) {
        return;
    }

    const defaults = {
        smtp__host: '',
        smtp__port: '587',
        smtp__encryption: 'tls',
        smtp__username: '',
        smtp__password: '',
        smtp__from_name: 'Simojang',
        smtp__from_email: '',
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
        const res = await fetch(buildAppUrl('api/manage-smtp/data'), { credentials: 'same-origin' });
        const json = await res.json();
        if (!res.ok || !json.status) {
            throw new Error(json.message || 'Gagal memuat SMTP setting');
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
            const res = await fetch(buildAppUrl('api/manage-smtp/save'), {
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
                throw new Error(json.message || 'Gagal menyimpan SMTP setting');
            }
            applyValues(json.data || {});
            showToast(json.message || 'SMTP setting berhasil disimpan', 'success');
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
