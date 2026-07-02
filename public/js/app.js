// Import and initialize navigation
import './navigation.js?v=1.0.2';
import { http } from './http.js';

function initThemeToggle() {
    const themeBtn = document.getElementById('theme-toggle');
    if (!themeBtn || themeBtn.dataset.initialized) return;
    
    themeBtn.addEventListener('click', () => {
        const currentTheme = document.documentElement.getAttribute('data-theme');
        let targetTheme = 'light';
        
        if (currentTheme === 'light') {
            targetTheme = 'dark';
        } else if (currentTheme === 'dark') {
            targetTheme = 'light';
        } else {
            if (window.matchMedia && window.matchMedia('(prefers-color-scheme: dark)').matches) {
                targetTheme = 'light';
            } else {
                targetTheme = 'dark';
            }
        }

        document.documentElement.setAttribute('data-theme', targetTheme);
        document.cookie = `theme=${targetTheme};path=/;max-age=${60 * 60 * 24 * 365}`;
        
        const icon = themeBtn.querySelector('i');
        if (icon) {
            icon.className = targetTheme === 'dark' ? 'fas fa-sun' : 'fas fa-moon';
        }
    });
    
    // Set initial icon
    const currentTheme = document.documentElement.getAttribute('data-theme') || (window.matchMedia('(prefers-color-scheme: dark)').matches ? 'dark' : 'light');
    const icon = themeBtn.querySelector('i');
    if (icon) {
        icon.className = currentTheme === 'dark' ? 'fas fa-sun' : 'fas fa-moon';
    }
    
    themeBtn.dataset.initialized = '1';
}

function initLangSelector() {
    const langSelect = document.getElementById('lang-selector');
    if (!langSelect || langSelect.dataset.initialized) return;

    langSelect.addEventListener('change', (e) => {
        const lang = e.target.value;
        document.cookie = `lang=${lang};path=/;max-age=${60 * 60 * 24 * 365}`;
        window.location.reload();
    });
    langSelect.dataset.initialized = '1';
}

function initApp() {
    initThemeToggle();
    initLangSelector();
}

// Global Event Delegation for forms (only needs to be bound once on document.body)
if (!window.appFormsInitialized) {
    document.body.addEventListener('submit', async (e) => {
        if (e.target.matches('.js-ajax-form')) {
            e.preventDefault();
            const form = e.target;
            const url = form.getAttribute('action');
            const method = form.getAttribute('method') || 'POST';
            const formData = new FormData(form);

            const submitBtn = form.querySelector('[type="submit"]');
            if (submitBtn) submitBtn.disabled = true;

            try {
                const response = await http.request(url, method, formData);
                
                if (response.redirect) {
                    window.location.href = response.redirect;
                } else if (response.message) {
                    // Пример показа уведомления (можно заменить на кастомный тост)
                    alert(response.message);
                }
            } catch (error) {
                if (error.errors) {
                    // Обработка ошибок валидации
                    Object.keys(error.errors).forEach(field => {
                        const input = form.querySelector(`[name="${field}"]`);
                        if (input) {
                            input.classList.add('is-invalid');
                            const feedback = form.querySelector(`.invalid-feedback[data-for="${field}"]`);
                            if (feedback) feedback.textContent = error.errors[field][0];
                        }
                    });
                } else {
                    alert(error.error || 'Произошла ошибка при отправке формы');
                }
            } finally {
                if (submitBtn) submitBtn.disabled = false;
            }
        }
    });

    // Очистка ошибок при вводе
    document.body.addEventListener('input', (e) => {
        if (e.target.classList.contains('is-invalid')) {
            e.target.classList.remove('is-invalid');
            const form = e.target.closest('form');
            if (form) {
                const feedback = form.querySelector(`.invalid-feedback[data-for="${e.target.name}"]`);
                if (feedback) feedback.textContent = '';
            }
        }
    });

    window.appFormsInitialized = true;
}

// Global Event Delegation for UI Components
if (!window.uiComponentsInitialized) {
    document.body.addEventListener('click', (e) => {
        // Dropdowns
        if (e.target.closest('.js-dropdown-toggle')) {
            const toggle = e.target.closest('.js-dropdown-toggle');
            const menu = toggle.nextElementSibling;
            // Close others
            document.querySelectorAll('.dropdown-menu.active').forEach(m => {
                if (m !== menu) m.classList.remove('active');
            });
            if (menu) menu.classList.toggle('active');
            return;
        }

        // Close dropdowns if clicked outside
        if (!e.target.closest('.dropdown')) {
            document.querySelectorAll('.dropdown-menu.active').forEach(menu => {
                menu.classList.remove('active');
            });
        }

        // Tabs
        if (e.target.closest('.tab-btn')) {
            const btn = e.target.closest('.tab-btn');
            const tabsHeader = btn.closest('.tabs-header');
            if (!tabsHeader) return;
            const tabsContent = tabsHeader.nextElementSibling;
            if (!tabsContent) return;
            
            tabsHeader.querySelectorAll('.tab-btn').forEach(b => b.classList.remove('active'));
            tabsContent.querySelectorAll('.tab-pane').forEach(p => p.classList.remove('active'));
            
            btn.classList.add('active');
            const targetPane = tabsContent.querySelector('#' + btn.dataset.tab);
            if (targetPane) targetPane.classList.add('active');
        }

        // Toasts
        if (e.target.closest('.js-show-toast')) {
            const btn = e.target.closest('.js-show-toast');
            showToast(btn.dataset.message, btn.dataset.type);
        }
    });

    window.uiComponentsInitialized = true;
}

// Toast functionality (Global)
window.showToast = function(message, type = 'success') {
    let container = document.getElementById('toast-container');
    if (!container) {
        container = document.createElement('div');
        container.id = 'toast-container';
        container.className = 'toast-container';
        document.body.appendChild(container);
    }

    const toast = document.createElement('div');
    toast.className = `toast toast--${type}`;
    toast.innerHTML = message;
    
    container.appendChild(toast);
    
    // Trigger reflow
    toast.offsetHeight;
    toast.classList.add('active');
    
    setTimeout(() => {
        toast.classList.remove('active');
        setTimeout(() => {
            toast.remove();
            if (container.children.length === 0) container.remove();
        }, 300);
    }, 3000);
};


// Run initialization
document.addEventListener('DOMContentLoaded', initApp);
document.addEventListener('app:navigated', initApp);
