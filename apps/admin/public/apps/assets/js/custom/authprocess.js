$(document).ready(function () {

    // Clean up entrance animation classes so they don't re-trigger on error (like when adding .shake)
    setTimeout(() => {
        $('.stagger-item').removeClass('stagger-item');
    }, 1500);

    // Dynamic Greeting
    const hour = new Date().getHours();
    let greeting = 'Selamat Datang';
    if (hour >= 4 && hour < 12) greeting = 'Selamat Pagi';
    else if (hour >= 12 && hour < 15) greeting = 'Selamat Siang';
    else if (hour >= 15 && hour < 18) greeting = 'Selamat Sore';
    else greeting = 'Selamat Malam';
    
    const $greetingEl = $('#greeting-text');
    if ($greetingEl.length) {
        $greetingEl.text(`${greeting}, silakan masuk untuk mengelola layanan dan aktivitas harian.`);
    }

    $('#togglePassword').on('click', function () {
        const $password = $('#passwordnew');
        const $icon = $(this).find('i');
        const isHidden = $password.attr('type') === 'password';
        $password.attr('type', isHidden ? 'text' : 'password');
        $icon.toggleClass('bi-eye bi-eye-slash');
        $(this).attr('aria-label', isHidden ? 'Sembunyikan kata sandi' : 'Tampilkan kata sandi');
    });

    const $password = $('#passwordnew');
    const $capsHint = $('#capsLockHint');

    const resetFieldErrors = () => {
        $('#o_userlogin, #passwordnew').removeClass('is-invalid');
        $('#o_userlogin_feedback, #o_password_feedback').text('');
    };

    const feedbackMap = {
        o_userlogin: 'o_userlogin_feedback',
        passwordnew: 'o_password_feedback',
    };

    const setFieldError = (fieldId, message) => {
        const feedbackId = feedbackMap[fieldId];
        $(`#${fieldId}`).addClass('is-invalid');
        if (feedbackId) $(`#${feedbackId}`).text(message || 'Input tidak valid.');
    };

    const toggleCapsLockHint = (event) => {
        if (!event || !event.getModifierState) return;
        const isCapsOn = event.getModifierState('CapsLock');
        $capsHint.toggleClass('d-none', !isCapsOn);
    };

    $password.on('keydown keyup', toggleCapsLockHint);
    $password.on('blur', function () {
        $capsHint.addClass('d-none');
    });

    $('#o_userlogin, #passwordnew').on('input', function () {
        $(this).removeClass('is-invalid');
        const feedbackId = feedbackMap[this.id];
        if (feedbackId) $(`#${feedbackId}`).text('');
    });

    const rememberedUser = localStorage.getItem('remembered_user_login');
    if (rememberedUser) {
        $('#o_userlogin').val(rememberedUser);
        $('#rememberLogin').prop('checked', true);
    }
        
    $('#loginForm').on('submit', function (event) {
        event.preventDefault();
        resetFieldErrors();
        const $form = $(this);
        const $button = $form.find('button[type="submit"]');
        const defaultLabel = $button.html();
        const shouldRemember = $('#rememberLogin').is(':checked');

        if ($button.prop('disabled')) {
            return;
        }

        const formData = new FormData(this);

        const fingerprint = {
            user_agent: navigator.userAgent,
            language: navigator.language || navigator.userLanguage,
            platform: navigator.platform,
            cpu_cores: navigator.hardwareConcurrency || null,
            device_memory: navigator.deviceMemory || null,
            screen_width: screen.width,
            screen_height: screen.height,
            timezone: Intl.DateTimeFormat().resolvedOptions().timeZone,
            touch_support: ('ontouchstart' in window) ? 1 : 0
        };

        formData.append('fingerprint', JSON.stringify(fingerprint));
        formData.append('remember_login', shouldRemember ? '1' : '0');

        $button.prop('disabled', true).html(
            '<span class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span><b>Memproses login...</b>'
        );

        fetch(AppConfig.initGlobal + 'authprocess', {
                method: 'POST',
                body: formData,
                cache: 'no-store'
            })
            .then(async response => {
                const data = await response.json();
                return { response, data };
            })
            .then(({ response, data }) => {
                if (!response.ok) {
                    throw data;
                }

                if (data.status === 'success') {
                    localStorage.setItem('theme', 'theme-light');
                    localStorage.removeItem('active_menu');
                    localStorage.removeItem('active_submenu');

                    if (shouldRemember) {
                        localStorage.setItem('jwt_token', data.token);
                        localStorage.setItem('remembered_user_login', $('#o_userlogin').val().trim());
                        sessionStorage.removeItem('jwt_token');
                    } else {
                        sessionStorage.setItem('jwt_token', data.token);
                        localStorage.removeItem('jwt_token');
                        localStorage.removeItem('remembered_user_login');
                    }

                    window.location.href = AppConfig.initGlobal + "dashboard";
                    return;
                }

                throw data;
            })
            .catch((error) => {
                const fieldErrors = (error?.errors && typeof error.errors === 'object')
                    ? error.errors
                    : ((error?.messages && typeof error.messages === 'object') ? error.messages : null);

                let hasFieldError = false;
                if (fieldErrors) {
                    if (fieldErrors.o_userlogin) { setFieldError('o_userlogin', fieldErrors.o_userlogin); hasFieldError = true; }
                    if (fieldErrors.o_password) { setFieldError('passwordnew', fieldErrors.o_password); hasFieldError = true; }
                }

                let errorMessage = error?.message || error?.messages || 'Login gagal. Coba lagi.';

                if (hasFieldError) {
                    errorMessage = 'Terdapat kesalahan pada input Anda. Silakan periksa kembali.';
                } else {
                    if (typeof errorMessage === 'object') {
                        errorMessage = Object.values(errorMessage).join('<br>');
                    }
                }

                if (!errorMessage || errorMessage === '[object Object]') {
                    errorMessage = 'Login gagal. Periksa username/password dan coba lagi.';
                }

                // Trigger shake animation
                $form.removeClass('shake');
                void $form[0].offsetWidth; // trigger reflow
                $form.addClass('shake');
                setTimeout(() => $form.removeClass('shake'), 650);

                if (typeof notifyError === 'function') {
                    notifyError(errorMessage);
                    return;
                }

                if (typeof Toastify === 'function') {
                    Toastify({
                        text: errorMessage,
                        duration: 3500,
                        className: 'app-toast app-toast-top-center',
                        close: true,
                        gravity: 'top',
                        position: 'center',
                        style: {
                            background: '#e74c3c'
                        }
                    }).showToast();
                }
            })
            .finally(() => {
                $button.prop('disabled', false).html(defaultLabel);
            });
    });
});
