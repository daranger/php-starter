<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="<?= \App\Core\Session::token() ?>">
    <title><?= htmlspecialchars($title ?? 'PHP Starter Kit') ?></title>
    <link rel="stylesheet" href="/css/app.css?v=<?= time() ?>">
    <link rel="icon" href="/assets/favicon.png" type="image/png">
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Roboto:wght@400;500;700&display=swap">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.6.0/css/all.min.css">
    <script type="module" src="/js/app.js?v=<?= time() ?>" defer></script>
    <script>
        (function() {
            var theme = document.cookie.match(/theme=(light|dark)/);
            if (theme) {
                document.documentElement.setAttribute('data-theme', theme[1]);
            }
        })();
    </script>
</head>
<body>
    <header class="site-header">
        <div class="container header-inner">
            <a href="/" class="logo"><?=setting('app_name');?></a>
            
            <nav class="main-nav">
                <a href="/" class="nav-link"><?= __('nav_home') ?></a>
                <a href="/faq" class="nav-link"><?= __('nav_faq') ?></a>
                <a href="/components" class="nav-link">UI Components</a>
                <?php if (\App\Core\Session::has('user_id')): ?>
                    <a href="/profile" class="nav-link"><?= __('nav_profile') ?></a>
                <?php endif; ?>
                
                <div class="mobile-auth">
                    <?php if (\App\Core\Session::has('user_id')): ?>
                        <form action="/api/auth/logout" method="POST" class="m-0 d-inline-block">
                            <input type="hidden" name="_csrf" value="<?= \App\Core\Session::token() ?>">
                            <button type="submit" class="btn btn--danger w-100"><?= __('btn_logout') ?></button>
                        </form>
                    <?php else: ?>
                        <a href="#" data-modal-open="login-modal" class="btn btn--primary w-100 d-block"><?= __('btn_login') ?></a>
                    <?php endif; ?>
                </div>
            </nav>

            <button id="mobile-menu-toggle" class="btn mobile-menu-toggle-btn">
                <i class="fas fa-bars"></i>
            </button>

            <div class="header-actions">
                <!-- Theme Toggle -->
                <button id="theme-toggle" class="btn theme-toggle-btn"><i class="fas fa-moon"></i></button>
                
                <!-- Language Selector -->
                <select id="lang-selector" class="form__input lang-selector-select">
                    <?php foreach (config('app.available_locales') as $code => $name): ?>
                        <option value="<?= $code ?>" <?= ($_COOKIE['lang'] ?? config('app.locale')) === $code ? 'selected' : '' ?>>
                            <?= strtoupper($code) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
                <div class="desktop-auth desktop-auth-container">
                    <?php if (\App\Core\Session::has('user_id')): ?>
                        <form action="/api/auth/logout" method="POST" class="m-0">
                            <input type="hidden" name="_csrf" value="<?= \App\Core\Session::token() ?>">
                            <button type="submit" class="btn btn--danger"><?= __('btn_logout') ?></button>
                        </form>
                    <?php else: ?>
                        <a href="#" data-modal-open="login-modal" class="btn btn--primary"><?= __('btn_login') ?></a>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </header>

    <main class="site-main">
        <div class="container">
            <?php if ($error = \App\Core\Session::getFlash('error')): ?>
                <div class="alert alert--error alert-global">
                    <?= htmlspecialchars($error) ?>
                </div>
            <?php endif; ?>

            <?= $content ?? '' ?>
        </div>
    </main>

    <footer class="site-footer">
        <div class="container footer-inner">
            <div>&copy; <?= date('Y') ?> PHP Starter Kit. All rights reserved.</div>
            
            <div class="social-footer">
                <?php if ($f = setting('social_facebook')): ?>
                    <a href="<?= htmlspecialchars($f) ?>" class="social-link facebook" target="_blank" rel="noopener noreferrer"><i class="fab fa-facebook-f"></i></a>
                <?php endif; ?>
                <?php if ($t = setting('social_twitter')): ?>
                    <a href="<?= htmlspecialchars($t) ?>" class="social-link twitter" target="_blank" rel="noopener noreferrer"><i class="fab fa-x-twitter"></i></a>
                <?php endif; ?>
                <?php if ($i = setting('social_instagram')): ?>
                    <a href="<?= htmlspecialchars($i) ?>" class="social-link instagram" target="_blank" rel="noopener noreferrer"><i class="fab fa-instagram"></i></a>
                <?php endif; ?>
                <?php if ($g = setting('social_github')): ?>
                    <a href="<?= htmlspecialchars($g) ?>" class="social-link github" target="_blank" rel="noopener noreferrer"><i class="fab fa-github"></i></a>
                <?php endif; ?>
                <?php if ($y = setting('social_youtube')): ?>
                    <a href="<?= htmlspecialchars($y) ?>" class="social-link youtube" target="_blank" rel="noopener noreferrer"><i class="fab fa-youtube"></i></a>
                <?php endif; ?>
                <?php if ($v = setting('social_vk')): ?>
                    <a href="<?= htmlspecialchars($v) ?>" class="social-link vk" target="_blank" rel="noopener noreferrer"><i class="fab fa-vk"></i></a>
                <?php endif; ?>
            </div>

            <?php if (admin()): ?>
                <div class="footer-time">t: <?= isset($_SERVER['REQUEST_TIME_FLOAT']) ? round(microtime(true) - $_SERVER['REQUEST_TIME_FLOAT'], 3) : '0.000' ?>s</div>
            <?php endif; ?>
        </div>
    </footer>

    <?php require __DIR__ . '/partials/auth_modals.php'; ?>
    <script type="module" src="/js/auth.js?v=<?= time() ?>" defer></script>
    <!-- Mobile Menu Toggle Script -->
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const toggle = document.getElementById('mobile-menu-toggle');
            const nav = document.querySelector('.main-nav');
            if(toggle && nav) {
                toggle.addEventListener('click', function() {
                    nav.classList.toggle('active');
                });
                
                // Close menu when clicking a nav link
                const navLinks = nav.querySelectorAll('.nav-link');
                navLinks.forEach(link => {
                    link.addEventListener('click', () => {
                        nav.classList.remove('active');
                    });
                });
            }
        });
    </script>
</body>
</html>
