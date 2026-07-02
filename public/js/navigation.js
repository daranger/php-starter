// Custom AJAX Navigation System

class Navigation {
    constructor() {
        this.mainContainerSelector = 'main.site-main';
        this.mainContainer = document.querySelector(this.mainContainerSelector);
        this.abortController = null;
        this.isNavigating = false;
        
        if (!this.mainContainer) {
            console.warn('Navigation: Main container not found.');
            return;
        }

        this.init();
    }

    init() {
        // Intercept clicks on links
        document.body.addEventListener('click', (e) => {
            const link = e.target.closest('a');
            if (!link) return;

            // Only intercept internal links
            if (this.shouldIntercept(link)) {
                e.preventDefault();
                this.navigate(link.href);
            }
        });

        // Handle History API (Back/Forward buttons)
        window.addEventListener('popstate', (e) => {
            if (e.state && e.state.ajaxNav) {
                this.navigate(window.location.href, false);
            } else {
                // If there's no state, it might be the initial page load, or outside our system
                this.navigate(window.location.href, false);
            }
        });

        // Save initial state
        window.history.replaceState({ ajaxNav: true }, document.title, window.location.href);
    }

    shouldIntercept(link) {
        // Exclude links with target="_blank"
        if (link.target === '_blank') return false;
        
        // Exclude external domains
        if (link.hostname !== window.location.hostname) return false;
        
        // Exclude anchor links on the same page
        if (link.getAttribute('href').startsWith('#')) return false;
        
        // Exclude explicit "no-ajax" classes or attributes
        if (link.hasAttribute('data-no-ajax') || link.classList.contains('no-ajax')) return false;

        // Exclude download links
        if (link.hasAttribute('download')) return false;

        return true;
    }

    async navigate(url, pushState = true) {
        if (this.abortController) {
            this.abortController.abort();
        }
        this.abortController = new AbortController();

        this.startLoading();

        try {
            const response = await fetch(url, {
                headers: {
                    'Accept': 'application/json',
                    'X-AJAX-Nav': '1',
                    'X-Requested-With': 'XMLHttpRequest'
                },
                credentials: 'same-origin',
                cache: 'no-cache',
                signal: this.abortController.signal
            });

            if (!response.ok) {
                throw new Error(`HTTP error! status: ${response.status}`);
            }

            const data = await response.json();

            // Update URL
            if (pushState) {
                window.history.pushState({ ajaxNav: true }, data.title, url);
            }

            // Update Title
            document.title = data.title;

            // Update Content
            this.updateContent(data.content);

            // Re-execute scripts
            this.executeScripts(this.mainContainer);

            // Fire custom event so other components can re-initialize
            document.dispatchEvent(new CustomEvent('app:navigated', { detail: { url } }));

        } catch (error) {
            if (error.name === 'AbortError') {
                console.log('Navigation aborted');
            } else {
                console.error('Navigation failed:', error);
                // Fallback to normal navigation
                window.location.href = url;
            }
        } finally {
            this.stopLoading();
        }
    }

    updateContent(htmlContent) {
        // Container inside site-main is usually .container
        // The server returns $content, which is inside <main class="site-main"><div class="container">$content</div></main>
        // Wait, looking at layout.php, $content is wrapped in <div class="container"> inside <main>.
        // BUT helpers.php returns EXACTLY $content.
        // So we need to find the container inside <main>.
        const innerContainer = this.mainContainer.querySelector('.container');
        if (innerContainer) {
            // First, remove existing alerts if any (they are siblings of content)
            const existingAlert = innerContainer.querySelector('.alert');
            if (existingAlert) existingAlert.remove();
            
            // Actually, helpers.php returns just the included view output. 
            // In layout.php, the flash alert is BEFORE $content.
            // Let's just replace the innerHTML of the container. 
            // Wait, if flash messages exist, they won't be in the AJAX response. That's fine.
            innerContainer.innerHTML = htmlContent;
        } else {
            this.mainContainer.innerHTML = htmlContent;
        }
    }

    executeScripts(container) {
        const scripts = container.querySelectorAll('script');
        scripts.forEach(oldScript => {
            const newScript = document.createElement('script');
            Array.from(oldScript.attributes).forEach(attr => {
                newScript.setAttribute(attr.name, attr.value);
            });
            newScript.textContent = oldScript.textContent;
            oldScript.parentNode.replaceChild(newScript, oldScript);
        });
    }

    startLoading() {
        this.isNavigating = true;
        this.mainContainer.style.opacity = '0.5';
        this.mainContainer.style.transition = 'opacity 0.2s ease';
        document.body.style.cursor = 'wait';
    }

    stopLoading() {
        this.isNavigating = false;
        this.mainContainer.style.opacity = '1';
        document.body.style.cursor = 'default';
    }
}

// Initialize immediately
window.AppNavigation = new Navigation();
