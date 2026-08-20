(function () {
    const form = document.getElementById('smtpSettingForm');
    const saveBtn = document.getElementById('smtpSettingSaveBtn');
    const resetBtn = document.getElementById('btnResetSmtpForm');
    if (!form || !saveBtn) {
        return;
    }

    const testForm = document.getElementById('formTestSmtp');
    const testSendBtn = document.getElementById('btnSendTestEmail');
    const testResultBox = document.getElementById('testResultBox');
    const btnUseFromEmail = document.getElementById('btnUseFromEmail');
    const btnTogglePwd = document.getElementById('btnToggleSmtpPwd');
    const inputPwd = document.getElementById('smtpPassword');
    const iconTogglePwd = document.getElementById('iconToggleSmtpPwd');
    const pwdSavedBadge = document.getElementById('pwdSavedBadge');

    // Banner elements
    const bannerHost = document.getElementById('bannerHost');
    const bannerPortEnc = document.getElementById('bannerPortEnc');
    const bannerStatusBadge = document.getElementById('bannerSmtpStatusBadge');

    const defaults = {
        smtp__host: '',
        smtp__port: '587',
        smtp__encryption: 'tls',
        smtp__username: '',
        smtp__password: '',
        smtp__from_name: 'SIMOJANG - Kanreg III BKN',
        smtp__from_email: '',
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

    function updateBannerStats(map) {
        const host = String(map['smtp.host'] || '').trim();
        const port = String(map['smtp.port'] || '587').trim();
        const enc = String(map['smtp.encryption'] || '').trim().toUpperCase();

        if (bannerHost) {
            bannerHost.textContent = host || 'Belum Diatur';
            bannerHost.className = host ? 'smtp-stat-value font-monospace text-dark' : 'smtp-stat-value text-muted';
        }

        if (bannerPortEnc) {
            const encText = enc ? ` (${enc})` : ' (None)';
            bannerPortEnc.textContent = (port || '-') + encText;
        }

        if (bannerStatusBadge) {
            if (host && map['smtp.username']) {
                bannerStatusBadge.className = 'badge bg-success-subtle text-success border border-success-subtle rounded-pill px-2.5 py-1';
                bannerStatusBadge.innerHTML = '<i class="bi bi-check2-circle me-1"></i>Siap Digunakan';
            } else {
                bannerStatusBadge.className = 'badge bg-warning-subtle text-warning border border-warning-subtle rounded-pill px-2.5 py-1';
                bannerStatusBadge.innerHTML = '<i class="bi bi-exclamation-circle me-1"></i>Belum Lengkap';
            }
        }

        updateActivePortBadge(port, enc.toLowerCase());
    }

    function updateActivePortBadge(port, enc) {
        document.querySelectorAll('.js-port-hint').forEach(function (badge) {
            const bPort = badge.getAttribute('data-port') || '';
            const bEnc = badge.getAttribute('data-enc') || '';
            if (bPort === String(port) && bEnc === String(enc)) {
                badge.classList.add('is-active');
            } else {
                badge.classList.remove('is-active');
            }
        });
    }

    function applyValues(map) {
        latestLoadedData = Object.assign({}, map);
        Object.keys(defaults).forEach(function (name) {
            const input = form.querySelector('[name="' + name + '"]');
            if (!input) return;
            const sourceKey = name.replace('__', '.');
            const val = map[sourceKey];
            input.value = (val === undefined || val === null || val === '') ? defaults[name] : String(val);
        });

        if (inputPwd) {
            inputPwd.value = '';
            if (map.has_password) {
                inputPwd.setAttribute('placeholder', '•••••••••••• (Tersimpan di Server)');
                if (pwdSavedBadge) pwdSavedBadge.classList.remove('d-none');
            } else {
                inputPwd.setAttribute('placeholder', '••••••••••••');
                if (pwdSavedBadge) pwdSavedBadge.classList.add('d-none');
            }
        }

        updateBannerStats(map);
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
            showToast(json.message || 'Konfigurasi SMTP berhasil disimpan', 'success');
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
            updateActivePortBadge(port, enc);
        });
    });

    // Sync active port pill on manual input
    const portInput = document.getElementById('smtpPort');
    const encSelect = document.getElementById('smtpEncryption');
    if (portInput && encSelect) {
        ['input', 'change'].forEach(evt => {
            portInput.addEventListener(evt, () => updateActivePortBadge(portInput.value, encSelect.value));
            encSelect.addEventListener(evt, () => updateActivePortBadge(portInput.value, encSelect.value));
        });
    }

    // Reset Form button
    if (resetBtn) {
        resetBtn.addEventListener('click', function () {
            applyValues(latestLoadedData);
            clearFieldErrors();
            if (typeof notifyInfo === 'function') {
                notifyInfo('Formulir telah dimuat ulang sesuai data tersimpan.');
            }
        });
    }

    // Quick fill "Gunakan Email Pengirim" in modal
    if (btnUseFromEmail) {
        btnUseFromEmail.addEventListener('click', function () {
            const fromEmailInput = document.getElementById('smtpFromEmail');
            const testEmailInput = document.getElementById('testRecipientEmail');
            if (fromEmailInput && testEmailInput && fromEmailInput.value.trim()) {
                testEmailInput.value = fromEmailInput.value.trim();
            }
        });
    }

    // Test Email Form Handler
    if (testForm && testSendBtn && testResultBox) {
        testForm.addEventListener('submit', async function (e) {
            e.preventDefault();
            const emailInput = document.getElementById('testRecipientEmail');
            const testEmail = emailInput ? emailInput.value.trim() : '';
            if (!testEmail) {
                showToast('Masukkan alamat email penerima uji coba', 'danger');
                return;
            }

            testSendBtn.disabled = true;
            const originalBtnText = testSendBtn.innerHTML;
            testSendBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-1" role="status" aria-hidden="true"></span> Menguji Koneksi...';
            
            testResultBox.className = 'p-3 rounded-3 mt-3 border bg-light text-secondary';
            testResultBox.innerHTML = `
                <div class="d-flex align-items-center gap-2">
                    <span class="spinner-border spinner-border-sm text-primary" role="status" aria-hidden="true"></span>
                    <span>Sedang melakukan handshake & pengiriman email ke <strong>${escapeHtml(testEmail)}</strong>...</span>
                </div>
            `;
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
                    testResultBox.innerHTML = `
                        <div class="d-flex align-items-start gap-2">
                            <i class="bi bi-exclamation-triangle-fill fs-5 mt-n1 flex-shrink-0"></i>
                            <div>
                                <strong>Pengujian Gagal:</strong><br>
                                ${escapeHtml(json.message || 'Gagal mengirim email uji coba')}
                            </div>
                        </div>
                    `;
                    showToast(json.message || 'Pengujian SMTP gagal', 'danger');
                    return;
                }

                testResultBox.className = 'p-3 rounded-3 mt-3 border border-success-subtle bg-success-subtle text-success';
                testResultBox.innerHTML = `
                    <div class="d-flex align-items-start gap-2">
                        <i class="bi bi-check-circle-fill fs-5 mt-n1 flex-shrink-0"></i>
                        <div>
                            <strong>Berhasil!</strong><br>
                            ${escapeHtml(json.message || 'Email uji coba berhasil dikirim.')}
                            <div class="small mt-1 text-secondary">Silakan periksa kotak masuk atau folder spam pada email <strong>${escapeHtml(testEmail)}</strong>.</div>
                        </div>
                    </div>
                `;
                showToast(json.message || 'Email uji coba berhasil dikirim', 'success');
            } catch (err) {
                testResultBox.className = 'p-3 rounded-3 mt-3 border border-danger-subtle bg-danger-subtle text-danger';
                testResultBox.innerHTML = `
                    <div class="d-flex align-items-start gap-2">
                        <i class="bi bi-exclamation-triangle-fill fs-5 mt-n1 flex-shrink-0"></i>
                        <div>
                            <strong>Terjadi Kesalahan Jaringan:</strong><br>
                            ${escapeHtml(err.message)}
                        </div>
                    </div>
                `;
                showToast(err.message, 'danger');
            } finally {
                testSendBtn.disabled = false;
                testSendBtn.innerHTML = originalBtnText;
            }
        });
    }

    function escapeHtml(text) {
        if (!text) return '';
        const map = { '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#039;' };
        return String(text).replace(/[&<>"']/g, function (m) { return map[m]; });
    }

    form.addEventListener('submit', function (e) {
        e.preventDefault();
        saveData().catch(function (err) {
            showToast(err.message, 'danger');
        });
    });

    loadData();
})();

