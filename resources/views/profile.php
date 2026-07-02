<div style="max-width: 800px; margin: 0 auto; margin-top: 40px;">
    <h1 style="margin-bottom: 20px;">Настройки профиля</h1>

    <div class="profile-layout">
        <!-- Avatar Section -->
        <div class="card" style="flex: 1; text-align: center;">
            <h3>Аватар</h3>
            <div style="margin: 20px 0;">
                <img id="avatar-preview" src="<?= $user->avatar ? htmlspecialchars($user->avatar) : '/assets/default-avatar.png' ?>" alt="Avatar" style="width: 150px; height: 150px; border-radius: 50%; object-fit: cover; border: 2px solid var(--color-border);">
            </div>
            <form id="avatar-form">
                <input type="file" id="avatar-input" name="avatar" accept="image/png, image/jpeg, image/webp, image/gif" style="display: none;">
                <button type="button" class="btn" onclick="document.getElementById('avatar-input').click()">Загрузить</button>
                <div id="avatar-error" class="form-error" style="margin-top: 10px;"></div>
            </form>
        </div>

        <!-- Security Section -->
        <div class="card" style="flex: 2;">
            <h3>Безопасность</h3>
            
            <form id="password-form" style="margin-top: 20px;">
                <div class="form__group">
                    <label class="form__label">Текущий пароль</label>
                    <input type="password" name="current_password" class="form__input" required>
                </div>
                <div class="form__group">
                    <label class="form__label">Новый пароль</label>
                    <input type="password" name="new_password" class="form__input" required minlength="6">
                </div>
                <div class="form__group">
                    <label class="form__label">Подтвердите новый пароль</label>
                    <input type="password" name="confirm_password" class="form__input" required minlength="6">
                </div>
                <div id="password-error" class="form-error"></div>
                <div id="password-success" style="color: var(--color-success); margin-bottom: 10px;"></div>
                <button type="submit" class="btn btn--primary">Сменить пароль</button>
            </form>

            <hr style="border: 0; border-top: 1px solid var(--color-border); margin: 30px 0;">

            <h3>Двухфакторная аутентификация (2FA)</h3>
            <?php if ($tfaEnabled): ?>
                <p style="color: var(--color-success); margin: 15px 0;">✅ 2FA включена и защищает ваш аккаунт.</p>
                <form id="disable-2fa-form">
                    <button type="submit" class="btn btn--danger">Отключить 2FA</button>
                </form>
            <?php else: ?>
                <p style="margin: 15px 0;">Сканируйте QR-код в приложении Google Authenticator:</p>
                <div style="background: white; display: inline-block; padding: 10px; border-radius: var(--radius-md);">
                    <img src="<?= $tfaSetupQr ?>" alt="QR Code" width="200" height="200" style="display: block;">
                </div>
                <p style="margin-top: 10px;">Код: <strong><?= chunk_split($tfaSetupSecret, 4, ' ') ?></strong></p>

                <form id="enable-2fa-form" style="margin-top: 20px;">
                    <div class="form__group">
                        <label class="form__label">Введите код из приложения</label>
                        <input type="text" name="code" class="form__input" required autocomplete="off" style="letter-spacing: 2px;">
                    </div>
                    <div id="2fa-error" class="form-error"></div>
                    <button type="submit" class="btn btn--primary">Включить 2FA</button>
                </form>
            <?php endif; ?>
        </div>
    </div>
</div>

<script>
(function() {
    // Avatar Upload
    const avatarInput = document.getElementById('avatar-input');
    if (avatarInput) {
        avatarInput.addEventListener('change', async (e) => {
            if (!e.target.files.length) return;
            
            const formData = new FormData();
            formData.append('avatar', e.target.files[0]);
            
            document.getElementById('avatar-error').textContent = '';
            
            try {
                const res = await fetch('/api/profile/avatar', {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                    },
                    body: formData
                });
                const data = await res.json();
                if (data.success) {
                    document.getElementById('avatar-preview').src = data.avatar_url;
                } else {
                    document.getElementById('avatar-error').textContent = data.error;
                }
            } catch (err) {
                document.getElementById('avatar-error').textContent = 'Ошибка загрузки';
            }
        });
    }

    // Password Update
    const pwdForm = document.getElementById('password-form');
    if (pwdForm) {
        pwdForm.addEventListener('submit', async (e) => {
            e.preventDefault();
            const err = document.getElementById('password-error');
            const succ = document.getElementById('password-success');
            err.textContent = '';
            succ.textContent = '';
            
            const data = Object.fromEntries(new FormData(pwdForm));
            try {
                const res = await fetch('/api/profile/password', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                    },
                    body: JSON.stringify(data)
                });
                const result = await res.json();
                if (result.success) {
                    succ.textContent = 'Пароль успешно изменен!';
                    pwdForm.reset();
                } else {
                    err.textContent = result.error;
                }
            } catch (e) {
                err.textContent = 'Ошибка сервера';
            }
        });
    }

    // Enable 2FA
    const enable2faForm = document.getElementById('enable-2fa-form');
    if (enable2faForm) {
        enable2faForm.addEventListener('submit', async (e) => {
            e.preventDefault();
            const err = document.getElementById('2fa-error');
            err.textContent = '';
            
            const data = Object.fromEntries(new FormData(enable2faForm));
            try {
                const res = await fetch('/api/profile/2fa/enable', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                    },
                    body: JSON.stringify(data)
                });
                const result = await res.json();
                if (result.success) {
                    window.location.reload();
                } else {
                    err.textContent = result.error;
                }
            } catch (e) {
                err.textContent = 'Ошибка сервера';
            }
        });
    }

    // Disable 2FA
    const disable2faForm = document.getElementById('disable-2fa-form');
    if (disable2faForm) {
        disable2faForm.addEventListener('submit', async (e) => {
            e.preventDefault();
            try {
                const res = await fetch('/api/profile/2fa/disable', {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                    }
                });
                const result = await res.json();
                if (result.success) {
                    window.location.reload();
                }
            } catch (e) {
                alert('Ошибка при отключении 2FA');
            }
        });
    }
})();
</script>
