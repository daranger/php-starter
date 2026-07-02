<div class="flex-column">


    <div class="dashboard">
        <div style="display: flex; flex-direction: column; gap: 24px;">
            <div class="panel">
                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 10px;">
                    <h2 style="margin: 0;">Трафик в реальном времени</h2>
                    <div style="font-size: 14px; font-weight: 500; color: #4b5563;">
                        Всего за сегодня: <span id="traffic-today-badge" class="badge" style="background:#0d6efd;">0</span>
                    </div>
                </div>
                <div style="height: 200px; width: 100%;">
                    <canvas id="trafficChart"></canvas>
                </div>
            </div>
            
            <div class="panel">
                <h2>Логи в реальном времени</h2>
                <div id="access-container" class="log-container"></div>
            </div>
            <div class="panel">
                <h2>Медленные запросы</h2>
                <div id="slow-container" class="log-container"></div>
            </div>
        </div>

        <div class="sidebar" style="display: flex; flex-direction: column; gap: 24px;">
            <div class="panel">
                <h2>Система (PHP <?= PHP_VERSION ?>)</h2>
                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 8px; margin-bottom: 15px;">
                    <div id="os-info" style="font-size: 12px; color: #6c757d; margin-bottom: 10px;">Загрузка данных...</div>
                    <div id="cpu-info" style="font-size: 12px; color: #6c757d; margin-bottom: 10px;">Загрузка данных...</div>
                </div>
                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 8px; margin-bottom: 15px;">
                    <div id="mysql-badge">MySQL: <span class="badge" id="mysql-badge" style="background:#a3a8a4">Unknown</span></div>
                    <div id="redis-badge">Redis: <span class="badge" style="background:#a3a8a4">Unknown</span></div>
                    <div>Processes: <span class="badge" id="proc-badge" style="background:#6c757d;">...</span></div>

                    <div>RAM (PHP Process): <span class="badge" id="php-ram-badge" style="background:#6c757d">...</span></div>
                </div>
                <div style="padding-bottom: 20px;">
                    <div style="padding-top: 10px; padding-bottom: 10px" id="disk-badge">Disk: <span class="badge" id="disk-badge" style="background:#996a43">...</span></div>
                    <div id="ram-badge">Ram: <span class="badge" id="ram-badge" style="background:#996a43">...</span></div>
                </div>
                <canvas id="loadChart" height="100"></canvas>

            </div>
            <div class="panel">
                <h2>Rate Limit</h2>
                <div id="rate-container" class="log-container"></div>
            </div>
            <div class="panel">
                <h2>Ошибки системы</h2>
                <div id="errors-container" class="log-container"></div>
            </div>
        </div>
    </div>


</div>


<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<style>
    :root {
        --primary: #0d6efd;
        --bg: #f8f9fa;
        --border: #dee2e6
    }

    .dashboard {
        display: grid;
        grid-template-columns:2fr 1fr;
        gap: 24px;
        margin: 0 auto;
    }

    h2 {
        font-size: 18px;
        margin-top: 0;
        border-bottom: 1px solid var(--border);
        padding-bottom: 10px;
        display: flex;
        justify-content: space-between
    }

    .badge {
        padding: 3px 8px;
        border-radius: 10px;
        color: #fff;
        font-size: 11px;
        font-weight: 700;
        text-transform: uppercase
    }

    .meta {
        color: #6c757d;
        font-size: 12px;
        margin-bottom: 5px
    }

    .raw-text {
        background: #f8d7da;
        font-family: SFMono-Regular, Consolas, 'Liberation Mono', Menlo, monospace;
        font-size: 14px;
        line-height: 1.6;
        padding: 14px;
        border-radius: 8px;
        margin-top: 10px;
        border-left: 4px solid #0000001a;
        word-break: break-all;
        white-space: pre-wrap
    }

    .log-row.is-error .raw-text {
        background-color: #fee2e2;
        color: #991b1b
    }

    .log-row.is-rate .raw-text {
        background-color: #fff7ed;
        color: #9a3412
    }

    .log-row.is-bot .raw-text {
        background-color: #feffcc;
        color: #856404
    }

    .log-row.is-user .raw-text {
        background-color: #e0f2fe;
        color: #075985
    }

    .raw-text:empty {
        display: none
    }

    .sidebar {
        display: flex;
        flex-direction: column;
        gap: 24px
    }

    .log-container {
        display: flex;
        flex-direction: column-reverse;
        gap: 0;
        border: 1px solid #dee2e6;
        border-radius: 8px;
        overflow: hidden;
        margin-top: 16px
    }

    .log-row {
        padding: 16px 20px;
        border-bottom: 1px solid #dee2e6;
        border-left: 6px solid #063f93;
        font-size: 16px;
        font-weight: 500;
        line-height: 1.5;
        transition: background-color .15s ease;
        animation: fadeIn .2s ease-out
    }

    .log-row:nth-child(odd) {
        background-color: #fff
    }

    .log-row:nth-child(even) {
        background-color: #0000000d
    }

    .log-row:hover {
        background-color: #00000014
    }

    .log-row:first-child {
        border-bottom: none
    }

    .log-row.is-bot {
        border-left-color: #ffc107
    }

    .log-row.is-rate {
        border-left-color: #fd7e14
    }

    .log-row.is-error {
        border-left-color: #dc3545
    }

    .badge {
        display: inline-block;
        padding: .35em .65em;
        font-size: 12px;
        font-weight: 700;
        line-height: 1;
        color: #fff;
        text-align: center;
        white-space: nowrap;
        vertical-align: baseline;
        border-radius: 50rem;
        margin-right: 10px;
        text-transform: uppercase;
        letter-spacing: .5px
    }

    .badge.user {
        background-color: #0d6efd
    }

    .badge.bot {
        background-color: #ffc107;
        color: #000
    }

    .badge.rate {
        background-color: #fd7e14
    }

    .badge.error {
        background-color: #dc3545
    }

    .meta {
        color: #6c757d;
        font-size: 15px;
        font-weight: 400
    }

    .log-row a {
        color: #1b52a4;
        text-decoration: none;
        font-weight: 600
    }

    .log-row a:hover {
        color: #0a58ca;
        text-decoration: underline
    }

    .ua {
        color: #6c757d;
        font-size: 14px;
        display: block;
        margin-top: 10px;
        padding-top: 8px;
        border-top: 1px dashed #dee2e6;
        font-weight: 400;
        word-break: break-all
    }

    .raw-text {
        white-space: pre-wrap;
        word-break: break-all;
        color: #842029;
        font-size: 15px;
        font-weight: 500;
        font-family: SFMono-Regular, Menlo, Monaco, Consolas, "Liberation Mono", "Courier New", monospace;
        background-color: #f8d7da;
        border: 1px solid #f5c2c7;
        padding: 12px;
        border-radius: 6px;
        margin-top: 10px
    }

    .panel {
        background: #fff;
        border: 1px solid var(--border);
        border-radius: 12px;
        padding: 20px;
        box-shadow: 0 .5rem 1rem #0000000d;
        display: flex;
        flex-direction: column;
        max-height: 700px
    }

    .log-container {
        flex-grow: 1;
        overflow-y: auto;
        display: flex;
        flex-direction: column-reverse;
        border: 1px solid #dee2e6;
        border-radius: 8px;
        padding: 5px
    }

    .log-container::-webkit-scrollbar {
        width: 8px
    }

    .log-container::-webkit-scrollbar-track {
        background: #f1f1f1;
        border-radius: 4px
    }

    .log-container::-webkit-scrollbar-thumb {
        background: #888;
        border-radius: 4px
    }

    .log-container::-webkit-scrollbar-thumb:hover {
        background: #555
    }

    .log-header {
        display: flex;
        align-items: center;
        gap: 10px;
        margin-bottom: 8px
    }

    .log-content {
        background: #f8f9fa;
        padding: 8px 12px;
        border-radius: 6px;
        font-family: 'SFMono-Regular', Consolas, monospace;
        font-size: 13px;
        color: #495057;
        word-break: break-all
    }

    .link-page {
        color: #0d6efd;
        text-decoration: none;
        font-weight: 600
    }

    .link-page:hover {
        text-decoration: underline
    }
    .ua {
        color: #6c757d;
        font-size: 14px;
        display: block;
        margin-top: 10px;
        padding-top: 8px;
        border-top: 1px dashed #dee2e6;
        font-weight: normal;
        word-break: break-all;
    }
</style>

<script>



    // 1. График нагрузки
    const ctx = document.getElementById('loadChart').getContext('2d');
    const loadChart = new Chart(ctx, {
        type: 'line',
        data: {
            labels: ['1m','50s', '40s', '30s', '20s', '10s'],
            datasets: [{
                label: 'Load AVG s',
                data: [0, 0, 0, 0, 0, 0],
                borderColor: '#0d6efd',
                tension: 0.4,
                fill: true,
                backgroundColor: 'rgba(13,110,253,0.1)'
            },
                {
                    label: 'CPU %',
                    data: [0, 0, 0, 0, 0, 0],
                    borderColor: '#dc3545', // Красный для CPU
                    backgroundColor: 'rgba(220,53,69,0.1)',
                    yAxisID: 'y1', // Вторая ось (важно!)
                    tension: 0.4,
                    fill: true
                }
            ]
        },
        options: {
            plugins: {legend: {display: false}}, scales: {
                y: { beginAtZero: true, max: 2, position: 'left' }, // Ось для Load
                y1: { beginAtZero: true, max: 100, position: 'right' } // Ось для CPU
            },
            animation: {
                duration: 500, // Плавная анимация обновления
                easing: 'linear'
            }
        }
    });

    // График трафика
    const trafficCtx = document.getElementById('trafficChart').getContext('2d');
    const trafficChart = new Chart(trafficCtx, {
        type: 'bar',
        data: {
            labels: [],
            datasets: [{
                label: 'Уники',
                data: [],
                backgroundColor: '#0d6efd',
                borderRadius: 4
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {legend: {display: false}},
            scales: {
                y: { beginAtZero: true, ticks: { precision: 0 } }
            },
            animation: { duration: 400 }
        }
    });

    function updateDiskUsage(data) {
        const disk = data.system_info.disk;
        const percent = Math.round((disk.used / disk.total) * 100);

        // Определяем цвет в зависимости от нагрузки
        let color = '#28a745'; // Зеленый (ок)
        if (percent > 70) color = '#ffc107'; // Желтый (предупреждение)
        if (percent > 90) color = '#dc3545'; // Красный (критично)

        const diskBadge = document.getElementById('disk-badge');
        diskBadge.innerHTML = `
        <div style="display: flex; align-items: center; gap: 10px;">
            <span style="font-size: 0.9em;">${disk.used} GB</span> <div class="progress-container">
                <div class="progress-bar" style="width: ${percent}%; background: ${color};"></div>
            </div>
            <span style="font-size: 0.9em;">${disk.total} GB </span>
        </div>
    `;
    }
    function updateRamUsage(data) {
        const ram = data.system_info.ram;
        const percent = Math.round((ram.used / ram.total) * 100);

        // Определяем цвет в зависимости от нагрузки
        let color = '#28a745'; // Зеленый (ок)
        if (percent > 70) color = '#ffc107'; // Желтый (предупреждение)
        if (percent > 90) color = '#dc3545'; // Красный (критично)

        const diskBadge = document.getElementById('ram-badge');
        diskBadge.innerHTML = `
        <div style="display: flex; align-items: center; gap: 10px;">
            <span style="font-size: 0.9em;">${ram.used} Mb</span> <div class="progress-container">
                <div class="progress-bar" style="width: ${percent}%; background: ${color};"></div>
            </div>
            <span style="font-size: 0.9em;">${ram.total} Mb </span>
        </div>
    `;
    }
    // 2. Системные данные
    async function updateSystem() {
        const res = await fetch('/admin/system_stats?_=' + Date.now());
        const data = await res.json();
        updateDiskUsage(data);
        updateRamUsage(data);
        document.getElementById('os-info').innerText = 'OS: ' + data.os;
        document.getElementById('cpu-info').innerText = 'CPU: ' + data.system_info.cpu;

        document.getElementById('php-ram-badge').innerText = (data.memory_usage / 1024 / 1024).toFixed(0) + 'MB';
        document.getElementById('mysql-badge').innerHTML =
            data.mysql_ok
                ? 'MySQL: <span class="badge" style="background:#28a745">'+data.mysql_ok+'</span>'
                : 'MySQL: <span class="badge" style="background:#dc3545">FAIL</span>';
        document.getElementById('redis-badge').innerHTML =
            data.redis_ok
                ? 'Redis: <span class="badge" style="background:#28a745">OK</span>'
                : 'Redis: <span class="badge" style="background:#dc3545">FAIL</span>';
        document.getElementById('proc-badge').innerText = data.processes;
        loadChart.data.datasets[0].data.push(data.load_avg);
        loadChart.data.datasets[0].data.shift();
        loadChart.data.datasets[1].data.push(data.system_info.cpu_usage);
        loadChart.data.datasets[1].data.shift();
        loadChart.update();
        
        if (data.traffic) {
            document.getElementById('traffic-today-badge').innerText = data.traffic.today;
            const history = data.traffic.history;
            trafficChart.data.labels = history.map(item => item.time);
            trafficChart.data.datasets[0].data = history.map(item => item.hits);
            trafficChart.update();
        }
    }

    // 3. Запуск всех процессов
    setInterval(updateSystem, 10000);
    updateSystem();

    // Здесь твой существующий код для логов...
    // (функции updateDashboard и checkNewFeedbacks запускаются аналогично)

    function flushRedis() {
        fetch('/admin/flush_redis').then(() => alert('Redis очищен!'));
    }
</script>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        console.log("=== Мониторинг запущен, HTML готов ===");

        // Изолируем переменные внутри функции, чтобы не было конфликтов имен
        const renderedLogs = new Set();

        // Безопасное декодирование Base64 в UTF-8 текст
        function decodeBase64Utf8(base64Str) {
            if (!base64Str) return '';
            try {
                const binaryString = atob(base64Str);
                const bytes = new Uint8Array(binaryString.length);
                for (let i = 0; i < binaryString.length; i++) {
                    bytes[i] = binaryString.charCodeAt(i);
                }
                return new TextDecoder('utf-8').decode(bytes);
            } catch (e) {
                console.error("Ошибка декодирования base64:", e);
                return '';
            }
        }

        // Поиск блоков по ключевому слову
        function parseBlocks(text, keyword) {
            if (!text) return [];
            const regex = new RegExp(`(${keyword}[\\s\\S]*?)(?=${keyword}|$)`, 'gi');
            const matches = text.match(regex);
            return matches || text.split("\n");
        }

        // Вспомогательная функция для безопасного вывода неформатированного лога
        function renderRawRow(container, text, maxLimit, customClass = '', badgeLabel = 'LOG', badgeClass = 'user') {
            if (!container) return;
            const row = document.createElement('div');
            row.className = `log-row ${customClass}`;
            row.innerHTML = `
            <div><span class="badge ${badgeClass}">${badgeLabel}</span></div>
            <div class="raw-text">${text}</div>
        `;
            container.appendChild(row);
            if (container.children.length > maxLimit) container.removeChild(container.children[0]);
        }

        function countryToFlag(code) {
            return code
                ? [...code].map(c => String.fromCodePoint(127397 + c.charCodeAt())).join('')
                : '';
        }

        function updateDashboard() {
            console.log("Отправка запроса на сервер...");

            // Кэш-бастер (?_=[время]) заставляет браузер каждый раз делать РЕАЛЬНЫЙ запрос
            fetch('/admin/stream_logs?_=' + Date.now(), {cache: "no-store"})
                .then(res => {
                    console.log("Ответ от сервера получен, статус:", res.status);
                    return res.json();
                })
                .then(data => {
                    if (data.error) {
                        console.error("Ошибка авторизации/доступа на бэкенде:", data.error);
                        return;
                    }

                    // 1. КОЛОНКА ACCESS (Трафик)
                    const accessContainer = document.getElementById('access-container');
                    if (data.access && accessContainer) {
                        try {
                            const text = decodeBase64Utf8(data.access);
                            const blocks = parseBlocks(text, 'time:');


                            blocks.forEach(block => {
                                const trimmed = block.trim();
                                if (!trimmed || renderedLogs.has(trimmed)) return;
                                renderedLogs.add(trimmed);

                                // Парсинг данных из текста
                                const time = (trimmed.match(/time:\s*([\d. :]+)/i) || [])[1] || 'N/A';
                                const page = (trimmed.match(/page:\s*([^\n\r|]+)/i) || [])[1] || '/';
                                const ua = (trimmed.match(/User-Agent:\s*([^\n\r|]+)/i) || [])[1] || 'Unknown';
                                const ip = (trimmed.match(/ip:\s*([\d.]+)/i) || [])[1] || '0.0.0.0';
                                const country = (trimmed.match(/country:\s*([A-Z]{2})/i) || [])[1] || '';

                                const isBot = /googlebot|yandexbot|bingbot/i.test(ua);
                                const flag = countryToFlag(country);

                                const row = document.createElement('div');
                                row.className = `log-row ${isBot ? 'is-bot' : ''}`;

                                // Формируем красивую верстку
                                row.innerHTML = `
                                        <div class="log-header">
                                            <span class="badge ${isBot ? 'bot' : 'user'}">${isBot ? 'BOT' : 'USER'}</span>
                                            <small class="meta">[${time}]</small>
                                            <span class="flag">${flag}</span>
                                            <strong style="color: #4b8eb3;">${ip}</strong>
                                            <a href="${page.split(' ')[0]}" target="_blank" class="link-page">${page.split(' ')[0]}</a>
                                        </div>
                                        <div>
                                            <span class="ua">UA: ${ua}</span>
                                        </div>
                                    `;
                                accessContainer.appendChild(row);
                                if (accessContainer.children.length > 10) accessContainer.removeChild(accessContainer.children[0]);
                            });
                        } catch (e) {
                            console.error("Ошибка парсинга Access логов:", e);
                        }
                    }

                    // 2. КОЛОНКА RATE LIMIT
                    const rateContainer = document.getElementById('rate-container');
                    if (data.rate && rateContainer) {
                        try {
                            const text = decodeBase64Utf8(data.rate);
                            const blocks = parseBlocks(text, 'time:');

                            blocks.forEach(block => {
                                const trimmed = block.trim();
                                if (!trimmed || renderedLogs.has(trimmed)) return;
                                renderedLogs.add(trimmed);

                                const timeMatch = trimmed.match(/time:\s*([\d.]+ в [\d:]+)/i);
                                const ipMatch = trimmed.match(/ip:\s*([\d.]+)/i);

                                if (!timeMatch || !ipMatch) {
                                    renderRawRow(rateContainer, trimmed, 2, 'is-rate', 'LIMIT', 'rate');
                                    return;
                                }

                                const row = document.createElement('div');
                                row.className = 'log-row is-rate';
                                row.innerHTML = `
                                <div><span class="badge rate">LIMIT</span><span class="meta">[${timeMatch[1]}]</span> <strong>Blocked IP: ${ipMatch[1]}</strong></div>
                                <div class="raw-text">${trimmed.replace(/time:.*|ip:.*/gi, '').trim()}</div>
                            `;
                                rateContainer.appendChild(row);
                                if (rateContainer.children.length > 2) rateContainer.removeChild(rateContainer.children[0]);
                            });
                        } catch (e) {
                            console.error("Ошибка парсинга Rate-limit логов:", e);
                        }
                    }

                    // 3. КОЛОНКА ERRORS
                    const errorsContainer = document.getElementById('errors-container');
                    if (data.errors && errorsContainer) {
                        try {
                            const text = decodeBase64Utf8(data.errors);
                            const blocks = parseBlocks(text, 'time:');

                            blocks.forEach(block => {
                                const trimmed = block.trim();
                                if (!trimmed || renderedLogs.has(trimmed)) return;
                                renderedLogs.add(trimmed);

                                renderRawRow(errorsContainer, trimmed, 2, 'is-error', 'ERROR', 'error');
                            });
                        } catch (e) {
                            console.error("Ошибка парсинга Error логов:", e);
                        }
                    }

                    const slowContainer = document.getElementById('slow-container');
                    if (data.slow && slowContainer) { // Было data.errors
                        try {
                            const text = decodeBase64Utf8(data.slow); // Было data.errors
                            const blocks = parseBlocks(text, 'time:');

                            blocks.forEach(block => {
                                const trimmed = block.trim();
                                if (!trimmed || renderedLogs.has(trimmed)) return;
                                renderedLogs.add(trimmed);

                                // Теперь используем slowContainer
                                renderRawRow(slowContainer, trimmed, 5, 'is-rate', 'SLOW', 'rate');
                            });
                        } catch (e) {
                            console.error("Ошибка парсинга slow логов:", e);
                        }
                    }
                })
                .catch(err => console.error("Критическая ошибка при обработке fetch/json:", err));
        }

        // Первый запуск
        updateDashboard();

        // Каждые 2 секунды
        setInterval(updateDashboard, 15500);
    });

</script>

<style>
    .progress-container {
        background: #e9ecef;
        border-radius: 10px;
        height: 12px;
        width: 100%;
        max-width: 80%; /* ограничим ширину, чтобы не растягивалось на весь экран */
        display: inline-block;
        overflow: hidden;
        vertical-align: middle;
    }
    .progress-bar {
        height: 100%;
        transition: width 0.5s ease-in-out; /* плавная анимация при обновлении */
    }
</style>