class HttpClient {
    constructor() {
        this.csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';
    }

    async request(url, method = 'GET', data = null, headers = {}) {
        const options = {
            method,
            credentials: 'same-origin',
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'X-CSRF-TOKEN': this.csrfToken,
                ...headers
            }
        };

        if (data && (method === 'POST' || method === 'PUT' || method === 'PATCH')) {
            if (data instanceof FormData) {
                options.body = data;
                // Не устанавливаем Content-Type для FormData, браузер сам установит boundary
            } else {
                options.headers['Content-Type'] = 'application/json';
                options.body = JSON.stringify(data);
            }
        }

        try {
            const response = await fetch(url, options);
            
            // Если сессия протухла
            if (response.status === 401 || response.status === 419) {
                window.location.reload();
                return null;
            }

            const contentType = response.headers.get('content-type');
            if (contentType && contentType.includes('application/json')) {
                const json = await response.json();
                if (!response.ok) throw json;
                return json;
            }
            
            if (!response.ok) throw new Error(response.statusText);
            return response.text();

        } catch (error) {
            console.error('HTTP Request Error:', error);
            throw error;
        }
    }

    get(url, headers = {}) {
        return this.request(url, 'GET', null, headers);
    }

    post(url, data, headers = {}) {
        return this.request(url, 'POST', data, headers);
    }

    put(url, data, headers = {}) {
        return this.request(url, 'PUT', data, headers);
    }

    delete(url, data = null, headers = {}) {
        return this.request(url, 'DELETE', data, headers);
    }
}

export const http = new HttpClient();
