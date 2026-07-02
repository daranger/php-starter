<div style="max-width: 800px; margin: 0 auto; margin-top: 40px;">
    <h1 style="text-align: center; margin-bottom: 30px;">Часто задаваемые вопросы (FAQ)</h1>

    <div class="card" style="margin-bottom: 20px;">
        <h3 style="color: var(--color-primary); margin-bottom: 10px;">Для чего нужен этот Starter Kit?</h3>
        <p style="color: var(--color-text-muted);">Этот фреймворк предоставляет готовую архитектуру MVC с современными возможностями (роутинг, сервис-контейнер, ORM-подобные репозитории, API-авторизация), чтобы вы могли начать разрабатывать проекты, не тратя время на написание рутинного кода.</p>
    </div>

    <div class="card" style="margin-bottom: 20px;">
        <h3 style="color: var(--color-primary); margin-bottom: 10px;">Как добавить новый язык?</h3>
        <p style="color: var(--color-text-muted);">Просто создайте новый файл локализации в `resources/lang/` (например, `es.php`) и добавьте его в массив `available_locales` в файле конфигурации `config/app.php`.</p>
    </div>

    <div class="card" style="margin-bottom: 20px;">
        <h3 style="color: var(--color-primary); margin-bottom: 10px;">Как работает защита CSRF?</h3>
        <p style="color: var(--color-text-muted);">CSRF токен автоматически генерируется для каждой сессии. Middleware `CsrfMiddleware` проверяет его наличие во всех пишущих HTTP-методах (POST, PUT, DELETE). Для Ajax-запросов (Fetch API) он берется из метатега `csrf-token` в шапке сайта.</p>
    </div>

    <div class="card" style="margin-bottom: 20px;">
        <h3 style="color: var(--color-primary); margin-bottom: 10px;">Как работает 2FA?</h3>
        <p style="color: var(--color-text-muted);">При включении 2FA, генерируется TOTP секрет и сохраняется в базе данных. Во время входа пользователя API проверяет наличие секрета и, если он есть, переводит сессию в статус ожидания 2FA (возвращает `requires_2fa: true`).</p>
    </div>
</div>
