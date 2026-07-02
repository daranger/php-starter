<!-- Auth Modals Overlay -->
<div class="modal-overlay" id="auth-modal-overlay"></div>

<!-- Login Modal -->
<div class="modal" id="login-modal">
    <div class="modal-header">
        <h2>Войти</h2>
        <button class="modal-close" data-modal-close>&times;</button>
    </div>
    <div class="modal-body">
        <form id="login-form">
            <div class="form__group">
                <label class="form__label">Email</label>
                <input type="email" name="email" class="form__input" required>
            </div>
            <div class="form__group">
                <label class="form__label">Пароль</label>
                <input type="password" name="password" class="form__input" required>
            </div>
            <div class="form__group" id="login-2fa-group" style="display: none;">
                <label class="form__label">Код из Authenticator</label>
                <input type="text" name="code" id="login-2fa-input" class="form__input" autocomplete="off" style="letter-spacing: 2px;">
            </div>
            <div class="form-error" id="login-error"></div>
            <button type="submit" class="btn btn--primary" style="width: 100%; padding: 12px; font-size: 16px;">Войти</button>
        </form>
        
        <div style="text-align: center; margin-top: 15px;">
            <a href="/api/auth/google" class="btn btn--outline js-google-login" style="width: 100%; display: flex; align-items: center; justify-content: center; gap: 10px; padding: 12px; border: 1px solid var(--color-border); background: var(--color-surface); color: var(--color-text);">
                <svg width="18" height="18" viewBox="0 0 18 18" fill="none" xmlns="http://www.w3.org/2000/svg">
                    <path d="M17.64 9.2c0-.637-.057-1.251-.164-1.84H9v3.481h4.844c-.209 1.125-.843 2.078-1.796 2.717v2.258h2.908c1.702-1.567 2.684-3.874 2.684-6.615z" fill="#4285F4"/>
                    <path d="M9 18c2.43 0 4.467-.806 5.956-2.184l-2.908-2.258c-.806.54-1.837.86-3.048.86-2.344 0-4.328-1.584-5.036-3.711H.957v2.332C2.438 15.983 5.482 18 9 18z" fill="#34A853"/>
                    <path d="M3.964 10.707c-.18-.54-.282-1.117-.282-1.707s.102-1.167.282-1.707V4.961H.957C.347 6.175 0 7.545 0 9s.348 2.825.957 4.039l3.007-2.332z" fill="#FBBC05"/>
                    <path d="M9 3.58c1.321 0 2.508.454 3.44 1.345l2.582-2.58C13.463.891 11.426 0 9 0 5.482 0 2.438 2.017.957 4.961L3.964 7.293C4.672 5.166 6.656 3.58 9 3.58z" fill="#EA4335"/>
                </svg>
                Войти через Google
            </a>
        </div>

        <div class="modal-footer-links">
            <a href="#" data-modal-open="register-modal">Регистрация</a>
            <a href="#" data-modal-open="forgot-password-modal">Забыли пароль?</a>
        </div>
    </div>
</div>

<!-- Register Modal -->
<div class="modal" id="register-modal">
    <div class="modal-header">
        <h2>Регистрация</h2>
        <button class="modal-close" data-modal-close>&times;</button>
    </div>
    <div class="modal-body">
        <form id="register-form">
            <div class="form__group">
                <label class="form__label">Email</label>
                <input type="email" name="email" class="form__input" required>
            </div>
            <div class="form__group">
                <label class="form__label">Пароль</label>
                <input type="password" name="password" class="form__input" required minlength="6">
            </div>
            <div class="form-error" id="register-error"></div>
            <button type="submit" class="btn btn--primary" style="width: 100%; padding: 12px; font-size: 16px;">Зарегистрироваться</button>
        </form>

        <div style="text-align: center; margin-top: 15px;">
            <a href="/api/auth/google" class="btn btn--outline js-google-login" style="width: 100%; display: flex; align-items: center; justify-content: center; gap: 10px; padding: 12px; border: 1px solid var(--color-border); background: var(--color-surface); color: var(--color-text);">
                <svg width="18" height="18" viewBox="0 0 18 18" fill="none" xmlns="http://www.w3.org/2000/svg">
                    <path d="M17.64 9.2c0-.637-.057-1.251-.164-1.84H9v3.481h4.844c-.209 1.125-.843 2.078-1.796 2.717v2.258h2.908c1.702-1.567 2.684-3.874 2.684-6.615z" fill="#4285F4"/>
                    <path d="M9 18c2.43 0 4.467-.806 5.956-2.184l-2.908-2.258c-.806.54-1.837.86-3.048.86-2.344 0-4.328-1.584-5.036-3.711H.957v2.332C2.438 15.983 5.482 18 9 18z" fill="#34A853"/>
                    <path d="M3.964 10.707c-.18-.54-.282-1.117-.282-1.707s.102-1.167.282-1.707V4.961H.957C.347 6.175 0 7.545 0 9s.348 2.825.957 4.039l3.007-2.332z" fill="#FBBC05"/>
                    <path d="M9 3.58c1.321 0 2.508.454 3.44 1.345l2.582-2.58C13.463.891 11.426 0 9 0 5.482 0 2.438 2.017.957 4.961L3.964 7.293C4.672 5.166 6.656 3.58 9 3.58z" fill="#EA4335"/>
                </svg>
                Продолжить через Google
            </a>
        </div>

        <div class="modal-footer-links">
            Уже есть аккаунт? <a href="#" data-modal-open="login-modal">Войти</a>
        </div>
    </div>
</div>

<!-- Forgot Password Modal -->
<div class="modal" id="forgot-password-modal">
    <div class="modal-header">
        <h2>Сброс пароля</h2>
        <button class="modal-close" data-modal-close>&times;</button>
    </div>
    <div class="modal-body">
        <form id="forgot-password-form">
            <div class="form__group">
                <label class="form__label">Ваш Email</label>
                <input type="email" name="email" class="form__input" required>
            </div>
            <div class="form-error" id="forgot-password-error"></div>
            <div class="form-success" id="forgot-password-success" style="display: none; color: var(--color-success); margin-bottom: 15px;"></div>
            <button type="submit" class="btn btn--primary" style="width: 100%; padding: 12px; font-size: 16px;">Отправить ссылку</button>
        </form>
        <div class="modal-footer-links">
            Вспомнили пароль? <a href="#" data-modal-open="login-modal">Войти</a>
        </div>
    </div>
</div>

<!-- Reset Password Modal (Shown only via URL params) -->
<div class="modal" id="reset-password-modal">
    <div class="modal-header">
        <h2>Новый пароль</h2>
        <button class="modal-close" data-modal-close>&times;</button>
    </div>
    <div class="modal-body">
        <form id="reset-password-form">
            <input type="hidden" name="token" id="reset-token">
            <input type="hidden" name="email" id="reset-email">
            <div class="form__group">
                <label class="form__label">Новый пароль</label>
                <input type="password" name="password" class="form__input" required>
            </div>
            <div class="form__group">
                <label class="form__label">Подтвердите пароль</label>
                <input type="password" name="password_confirm" class="form__input" required>
            </div>
            <div class="form-error" id="reset-password-error"></div>
            <button type="submit" class="btn btn--primary" style="width: 100%; padding: 12px; font-size: 16px;">Сохранить пароль</button>
        </form>
    </div>
</div>

