$(document).ready(function () {
    $('#changePasswordForm').on('submit', function (event) {
        event.preventDefault();

        if (typeof swlwaitProsessing === 'function') swlwaitProsessing();

        let formData = new FormData(this);
        fetch( AppConfig.initGlobal + 'change-password', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.status === 'success') {
                    Swal.fire({
                        toast: true,
                        position: 'top',
                        icon: 'success',
                        title: data.messages,
                        showConfirmButton: false,
                        timer: 3000,
                    });
                    $('#change-password').modal('hide');
                    $('#changePasswordForm')[0].reset();
                } else {
                    let errorMessage = data.messages;
                    if (typeof errorMessage === 'object') {
                        errorMessage = Object.values(errorMessage).join('<br>');
                    }
                    Swal.fire({
                        toast: true,
                        position: 'top',
                        icon: 'error',
                        title: errorMessage,
                        showConfirmButton: false,
                        timer: 3000,
                    });
                }
            })
    });

    // Password Strength & Generator Logic
    const pwInput = document.getElementById('password2');
    const pwConfirm = document.getElementById('password3');
    const strengthBox = document.getElementById('pwStrengthBox');
    const strengthBar = document.getElementById('pwStrengthBar');
    const strengthText = document.getElementById('pwStrengthText');
    const btnGenerate = document.getElementById('btnGeneratePassword');

    if (pwInput && strengthBox) {
        pwInput.addEventListener('input', function() {
            const val = this.value;
            if (val.length === 0) {
                strengthBox.style.display = 'none';
                return;
            }
            strengthBox.style.display = 'block';
            
            let strength = 0;
            if (val.length > 5) strength += 1;
            if (val.length > 7) strength += 1;
            if (/[A-Z]/.test(val)) strength += 1;
            if (/[0-9]/.test(val)) strength += 1;
            if (/[^A-Za-z0-9]/.test(val)) strength += 1;

            let pct = 0;
            let text = '';
            let colorClass = '';

            switch(strength) {
                case 0:
                case 1:
                    pct = 20; text = 'Sangat Lemah'; colorClass = 'bg-danger'; break;
                case 2:
                    pct = 40; text = 'Lemah'; colorClass = 'bg-warning'; break;
                case 3:
                    pct = 60; text = 'Cukup'; colorClass = 'bg-info'; break;
                case 4:
                    pct = 80; text = 'Kuat'; colorClass = 'bg-primary'; break;
                case 5:
                    pct = 100; text = 'Sangat Kuat'; colorClass = 'bg-success'; break;
            }

            strengthBar.style.width = pct + '%';
            strengthBar.className = 'progress-bar ' + colorClass;
            strengthText.textContent = text;
            
            // Text color for label
            strengthText.className = 'fw-bold text-' + colorClass.replace('bg-', '');
        });
    }

    if (btnGenerate) {
        btnGenerate.addEventListener('click', function(e) {
            e.preventDefault();
            const chars = "abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789!@#$%^&*()_+~`|}{[]:;?><,./-=";
            let password = "";
            
            // Ensure at least one of each required type
            password += "ABCDEFGHIJKLMNOPQRSTUVWXYZ"[Math.floor(Math.random() * 26)];
            password += "abcdefghijklmnopqrstuvwxyz"[Math.floor(Math.random() * 26)];
            password += "0123456789"[Math.floor(Math.random() * 10)];
            password += "!@#$%^&*"[Math.floor(Math.random() * 8)];
            
            for (let i = 0; i < 12; i++) {
                password += chars[Math.floor(Math.random() * chars.length)];
            }
            // Shuffle
            password = password.split('').sort(function(){return 0.5-Math.random()}).join('');
            
            pwInput.value = password;
            if (pwConfirm) pwConfirm.value = password;
            
            // Trigger input event to update strength bar
            pwInput.dispatchEvent(new Event('input'));
            
            // Show password temporarily (optional, assuming user has eyes toggle)
            pwInput.type = 'text';
            if (pwConfirm) pwConfirm.type = 'text';
            
            // Auto hide after 3 seconds
            setTimeout(() => {
                pwInput.type = 'password';
                if (pwConfirm) pwConfirm.type = 'password';
            }, 4000);
            
            Swal.fire({
                toast: true,
                position: 'top',
                icon: 'success',
                title: 'Sandi kuat berhasil dibuat!',
                showConfirmButton: false,
                timer: 2500,
            });
        });
    }

    // Passcode Visibility Toggle
    const passcodeSwitches = document.querySelectorAll('.passcode-switch');
    passcodeSwitches.forEach(btn => {
        btn.addEventListener('click', function(e) {
            e.preventDefault();
            const targetId = this.getAttribute('data-target');
            const targetInput = document.getElementById(targetId);
            
            if (targetInput) {
                const isPassword = targetInput.type === 'password';
                targetInput.type = isPassword ? 'text' : 'password';
                
                const iconShow = this.querySelector('.icon-show');
                const iconHide = this.querySelector('.icon-hide');
                
                if (iconShow && iconHide) {
                    if (isPassword) {
                        iconShow.classList.add('d-none');
                        iconHide.classList.remove('d-none');
                    } else {
                        iconShow.classList.remove('d-none');
                        iconHide.classList.add('d-none');
                    }
                }
            }
        });
    });
});