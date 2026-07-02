// JS for Authentication Modals
function initAuth() {
    const overlay = document.getElementById('auth-modal-overlay');
    const modals = document.querySelectorAll('.modal');
    const openBtns = document.querySelectorAll('[data-modal-open]');
    const closeBtns = document.querySelectorAll('[data-modal-close]');

    // Helpers to open/close
    const openModal = (id) => {
        closeAllModals();
        const modal = document.getElementById(id);
        if (modal) {
            overlay.classList.add('active');
            modal.classList.add('active');
        }
    };

    const closeAllModals = () => {
        if(overlay) overlay.classList.remove('active');
        modals.forEach(m => m.classList.remove('active'));
    };

    // Event Listeners (using initialized dataset to prevent duplicates)
    openBtns.forEach(btn => {
        if (!btn.dataset.authInitialized) {
            btn.addEventListener('click', (e) => {
                e.preventDefault();
                const target = btn.getAttribute('data-modal-open');
                openModal(target);
            });
            btn.dataset.authInitialized = '1';
        }
    });

    closeBtns.forEach(btn => {
        if (!btn.dataset.authInitialized) {
            btn.addEventListener('click', closeAllModals);
            btn.dataset.authInitialized = '1';
        }
    });

    if (overlay && !overlay.dataset.authInitialized) {
        overlay.addEventListener('click', closeAllModals);
        overlay.dataset.authInitialized = '1';
    }

    // Google Auth Popup
    const googleBtns = document.querySelectorAll('.js-google-login');
    googleBtns.forEach(btn => {
        if (!btn.dataset.authInitialized) {
            btn.addEventListener('click', (e) => {
                e.preventDefault();
                const width = 500;
                const height = 600;
                const left = window.screenX + (window.outerWidth - width) / 2;
                const top = window.screenY + (window.outerHeight - height) / 2;
                window.open(btn.href, 'google_auth', `width=${width},height=${height},left=${left},top=${top}`);
            });
            btn.dataset.authInitialized = '1';
        }
    });

    // Form handlers
    const handleFormSubmit = async (formId, endpoint, successCallback) => {
        const form = document.getElementById(formId);
        if (!form || form.dataset.authInitialized) return;

        form.addEventListener('submit', async (e) => {
            e.preventDefault();
            const errorDiv = document.getElementById(`${formId.replace('-form', '')}-error`);
            const btn = form.querySelector('button[type="submit"]');
            
            if (errorDiv) errorDiv.textContent = '';
            if (btn) btn.disabled = true;

            const formData = new FormData(form);
            const data = Object.fromEntries(formData.entries());

            try {
                const response = await fetch(endpoint, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                    },
                    body: JSON.stringify(data)
                });

                const result = await response.json();

                if (!response.ok) {
                    throw new Error(result.error || 'Произошла ошибка');
                }

                if (result.success || result.redirect || result.requires_2fa) {
                    if (successCallback) {
                        successCallback(result);
                    } else {
                        window.location.reload();
                    }
                }
            } catch (err) {
                if (errorDiv) errorDiv.textContent = err.message;
            } finally {
                if (btn) btn.disabled = false;
            }
        });
        
        form.dataset.authInitialized = '1';
    };

    // Setup forms
    handleFormSubmit('login-form', '/api/auth/login', (result) => {
        if (result.requires_2fa) {
            const mfaGroup = document.getElementById('login-2fa-group');
            const mfaInput = document.getElementById('login-2fa-input');
            mfaGroup.style.display = 'block';
            mfaInput.required = true;
            mfaInput.focus();
            
            if (!mfaInput.dataset.autoSubmitBound) {
                mfaInput.addEventListener('input', (e) => {
                    const code = e.target.value.replace(/\D/g, '');
                    if (code.length === 6) {
                        const form = document.getElementById('login-form');
                        if (form) form.requestSubmit();
                    }
                });
                mfaInput.dataset.autoSubmitBound = '1';
            }
        } else {
            window.location.reload();
        }
    });
    
    handleFormSubmit('register-form', '/api/auth/register');
    
    handleFormSubmit('forgot-password-form', '/api/auth/forgot-password', (result) => {
        document.getElementById('forgot-password-error').textContent = '';
        document.getElementById('forgot-password-success').style.display = 'block';
        document.getElementById('forgot-password-success').textContent = result.message;
    });

    handleFormSubmit('reset-password-form', '/api/auth/reset-password', () => {
        alert('Пароль успешно изменен! Теперь вы можете войти.');
        openModal('login-modal');
    });

    // Handle logout explicitly
    const logoutForms = document.querySelectorAll('form[action="/logout"]');
    logoutForms.forEach(form => {
        if (!form.dataset.authInitialized) {
            form.addEventListener('submit', async (e) => {
                e.preventDefault();
                await fetch('/api/auth/logout', {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                    }
                });
                window.location.href = '/';
            });
            form.dataset.authInitialized = '1';
        }
    });

    // Check URL parameters for password reset
    if (!window.authUrlChecked) {
        const urlParams = new URLSearchParams(window.location.search);
        const resetToken = urlParams.get('token');
        const resetEmail = urlParams.get('email');
        
        if (resetToken && resetEmail) {
            openModal('reset-password-modal');
            document.getElementById('reset-token').value = resetToken;
            document.getElementById('reset-email').value = resetEmail;
            
            // Clean URL without refreshing page
            const newUrl = window.location.protocol + "//" + window.location.host + window.location.pathname;
            window.history.pushState({path:newUrl}, '', newUrl);
        }
        window.authUrlChecked = true;
    }
}

document.addEventListener('DOMContentLoaded', initAuth);
document.addEventListener('app:navigated', initAuth);
