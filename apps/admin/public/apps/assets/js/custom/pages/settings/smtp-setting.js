(function () {
    const form = document.getElementById('smtpSettingForm');
    const saveBtn = document.getElementById('smtpSettingSaveBtn');
    if (!form || !saveBtn) {
        return;
    }

    const testForm = document.getElementById('formTestSmtp');
    const testSendBtn = document.getElementById('btnSendTestEmail');
    const testResultBox = document.getElementById('testResultBox');
    const btnTogglePwd = document.getElementById('btnToggleSmtpPwd');
    const inputPwd = document.getElementById('smtpPassword');
    const iconTogglePwd = document.getElementById('iconToggleSmtpPwd');

    const defaults = {
        smtp__host: '',
        smtp__port: '587',
        smtp__encryption: 'tls',
        smtp__username: '',
        smtp__password: '',
        smtp__from_name: 'SIMOJANG - Kanreg III BKN',
        smtp__from_email: '',
    };

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
        
        let feedback = el.closest('div').querySelector('.invalid-feedback');
        if (!feedback) {
            feedback = document.createElement('div');
            feedback.className = 'invalid-feedback d-block';
            el.closest('div').appendChild(feedback);
        }
        feedback.textContent = errorText || 'Bidang ini wajib diisi dengan benar';
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
        try {
            const res = await fetch(buildAppUrl('api/manage-smtp/data'), { credentials: 'same-origin' });
            const json = await res.json();
            if (!res.ok || !json.status) {
                throw new Error(json.message || 'Gagal memuat SMTP setting');
            }
            applyValues(json.data || {});
        } catch (err) {
            applyValues({});
            showToast(err.message, 'danger');
        }
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
        const originalBtnText = saveBtn.innerHTML;
        saveBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-1" role="status" aria-hidden="true"></span> Menyimpan...';
        
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
            saveBtn.innerHTML = originalBtnText;
        }
    }

    // Toggle Password Visibility
    if (btnTogglePwd && inputPwd && iconTogglePwd) {
        btnTogglePwd.addEventListener('click', function () {
            if (inputPwd.type === 'password') {
                inputPwd.type = 'text';
                iconTogglePwd.className = 'bi bi-eye-slash';
            } else {
                inputPwd.type = 'password';
                iconTogglePwd.className = 'bi bi-eye';
            }
        });
    }

    // Port quick-select hints
    document.querySelectorAll('.js-port-hint').forEach(function (badge) {
        badge.addEventListener('click', function () {
            const port = this.getAttribute('data-port') || '';
            const enc = this.getAttribute('data-enc') || '';
            const portInput = document.getElementById('smtpPort');
            const encSelect = document.getElementById('smtpEncryption');
            if (portInput && port) portInput.value = port;
            if (encSelect) encSelect.value = enc;
        });
    });

    // Test Email Form Handler
    if (testForm && testSendBtn && testResultBox) {
        testForm.addEventListener('submit', async function (e) {
            e.preventDefault();
            const emailInput = document.getElementById('testRecipientEmail');
            const testEmail = emailInput ? emailInput.value.trim() : '';
            if (!testEmail) {
                showToast('Masukkan alamat email tujuan uji coba', 'danger');
                return;
            }

            testSendBtn.disabled = true;
            const originalBtnText = testSendBtn.innerHTML;
            testSendBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-1" role="status" aria-hidden="true"></span> Mengirim...';
            
            testResultBox.className = 'p-3 rounded-3 mt-3 border bg-light text-secondary';
            testResultBox.innerHTML = '<div class="d-flex align-items-center gap-2"><span class="spinner-border spinner-border-sm text-primary"></span> <span>Sedang menguji koneksi dan mengirim email...</span></div>';
            testResultBox.classList.remove('d-none');

            try {
                const res = await fetch(buildAppUrl('api/manage-smtp/test'), {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    credentials: 'same-origin',
                    body: JSON.stringify({ test_email: testEmail }),
                });
                const json = await res.json();

                if (!res.ok || !json.status) {
                    testResultBox.className = 'p-3 rounded-3 mt-3 border border-danger-subtle bg-danger-subtle text-danger';
                    testResultBox.innerHTML = '<div class="d-flex align-items-start gap-2"><i class="bi bi-exclamation-triangle-fill fs-5 mt-n1"></i> <div><strong>Pengujian Gagal:</strong><br>' + (json.message || 'Gagal mengirim email') + '</div></div>';
                    showToast(json.message || 'Pengujian SMTP gagal', 'danger');
                    return;
                }

                testResultBox.className = 'p-3 rounded-3 mt-3 border border-success-subtle bg-success-subtle text-success';
                testResultBox.innerHTML = '<div class="d-flex align-items-start gap-2"><i class="bi bi-check-circle-fill fs-5 mt-n1"></i> <div><strong>Berhasil!</strong><br>' + (json.message || 'Email uji coba berhasil dikirim.') + ' Silakan periksa inbox / spam email Anda.</div></div>';
                showToast(json.message || 'Email uji coba berhasil dikirim', 'success');
            } catch (err) {
                testResultBox.className = 'p-3 rounded-3 mt-3 border border-danger-subtle bg-danger-subtle text-danger';
                testResultBox.innerHTML = '<div class="d-flex align-items-start gap-2"><i class="bi bi-exclamation-triangle-fill fs-5 mt-n1"></i> <div><strong>Terjadi Kesalahan:</strong><br>' + err.message + '</div></div>';
                showToast(err.message, 'danger');
            } finally {
                testSendBtn.disabled = false;
                testSendBtn.innerHTML = originalBtnText;
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
