<?php

namespace App\Admin\Controllers;

use App\Core\Db;
use App\Core\Response;

class AdminController
{
    public function __construct()
    {
    }

    public function index(): array
    {
        return [
            'view' => 'main',
            'data' => [
                'db_tables' => self::getDbTables()
            ]
        ];
    }

    public function streamLogs(string $p): array
    {
        $logDir = __DIR__ . '/../../../storage/logs/';
        $datePart = date("d.m.y");

        $configs = [
            'access' => $logDir . $datePart . '.log',
            'rate' => $logDir . 'rate-limits/' . $datePart . '.log',
            'errors' => $logDir . 'errors/' . $datePart . '.log',
            'slow' => $logDir . 'slow-queries/' . $datePart . '.log' // Новый путь
        ];

        $response = [];

        foreach ($configs as $key => $path) {
            $response[$key] = ''; // Инициализируем пустотой
            if (!file_exists($path)) {
                $response[$key] = "FILE_NOT_FOUND: " . basename($path);
                continue;
            }

            if (!is_readable($path)) {
                $response[$key] = "PERMISSION_DENIED: " . basename($path);
                continue;
            }
            // Добавим отладку, если файл не читается
            if (file_exists($path) && is_readable($path)) {
                $handle = fopen($path, 'r');
                if ($handle) {
                    $readSize = ($key === 'access') ? 30720 : 5120;

                    fseek($handle, 0, SEEK_END);
                    $fileSize = ftell($handle);
                    $seek = max(0, $fileSize - $readSize);

                    fseek($handle, $seek);
                    $chunk = fread($handle, $readSize);

                    $response[$key] = base64_encode($chunk ?: '');
                    fclose($handle);
                }
            }
        }

        // Твой AJAX обработчик
        if (str_contains($p, '/stream_logs')) {
            header('Content-Type: application/json; charset=utf-8');
            echo json_encode($response);
            exit;
        }

        return ['data' => ['logs' => $response]];
    }

    public static function get_cpu_model()
    {
        if (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN') {
            $cpu = @shell_exec('wmic cpu get name');
            if ($cpu) {
                $lines = explode("\n", trim($cpu));
                return trim($lines[1] ?? 'Unknown');
            }
            return 'Unknown';
        }
        $cpu = "Unknown";
        if (is_readable('/proc/cpuinfo')) {
            $data = file_get_contents('/proc/cpuinfo');
            preg_match('/model name\s+:\s+(.*)/i', $data, $matches);
            $cpu = $matches[1] ?? 'Unknown';
        }
        return trim($cpu);
    }

    public static function total_ram_in_mb()
    {
        if (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN') {
            $mem = @shell_exec('wmic OS get TotalVisibleMemorySize /Value');
            if ($mem && preg_match('/TotalVisibleMemorySize=(\d+)/', $mem, $matches)) {
                return round($matches[1] / 1024);
            }
            return 0;
        }
        if (is_readable('/proc/meminfo')) {
            $mem = file_get_contents('/proc/meminfo');
            preg_match('/MemTotal:\s+(\d+)/', $mem, $matches);
            return ($matches[1] ?? 0) / 1024;
        }
        return 0;
    }

    public static function getUsedRam()
    {
        if (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN') {
            $mem = @shell_exec('wmic OS get FreePhysicalMemory,TotalVisibleMemorySize /Value');
            if ($mem && preg_match('/FreePhysicalMemory=(\d+)/', $mem, $free) && preg_match('/TotalVisibleMemorySize=(\d+)/', $mem, $total)) {
                $usedKb = $total[1] - $free[1];
                return round($usedKb / 1024);
            }
            return 0;
        }
        if (!is_readable('/proc/meminfo')) return 0;

        $mem = file_get_contents('/proc/meminfo');
        preg_match_all('/(\w+):\s+(\d+)/', $mem, $matches);
        $m = array_combine($matches[1], $matches[2]);

        // Total - (Free + Buffers + Cached)
        // SReclaimable добавляем, если он есть, так как это часть кеша
        $free = ($m['MemFree'] ?? 0) + ($m['Buffers'] ?? 0) + ($m['Cached'] ?? 0) + ($m['SReclaimable'] ?? 0);
        $total = ($m['MemTotal'] ?? 0);

        return round(($total - $free) / 1024);
    }

    public static function getDiskStats()
    {
        $total = disk_total_space('/');
        $free = disk_free_space('/');
        $used = $total - $free;

        return [
            'used' => round($used / 1024 / 1024 / 1024, 2),  // В ГБ
            'total' => round($total / 1024 / 1024 / 1024, 2)  // В ГБ
        ];
    }
// Отдельный метод для супер-быстрого ответа
    public static function getCpuOnly() {
        if (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN') {
            $cpu = @shell_exec('wmic cpu get loadpercentage /Value');
            if ($cpu && preg_match('/LoadPercentage=(\d+)/', $cpu, $matches)) {
                return (float)$matches[1];
            }
            return 0.0;
        }
        // -b: batch mode (без интерактивности)
        // -n 1: выполнить один цикл и выйти
        // -d: задержка (можно поставить 0.1, чтобы не ждать секунду)
        $cpu = shell_exec("top -bn2 -d 0.1 | grep 'Cpu(s)' | tail -n1 | awk '{print $2 + $4}'");
        return (float)trim($cpu ?: '0');
    }

    public function stats(string $p): array
    {
        // Отключаем ошибки, если функции недоступны
        $disk_total = @disk_total_space("/");
        $disk_free = @disk_free_space("/");
        
        $process_count = 0;
        if (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN') {
            $proc = @shell_exec('tasklist | find /C /V ""');
            $process_count = (int)trim($proc ?: '0');
        } else {
            $process_count = (int)@shell_exec('ps aux | wc -l');
        }

        $traffic = ['today' => 0, 'history' => []];
        if (isset($GLOBALS['redis'])) {
            try {
                $redis = $GLOBALS['redis'];
                $date = date('Y-m-d');
                $traffic['today'] = (int) $redis->pfcount("traffic:unique:today:$date");
                
                $historyKeys = [];
                $labels = [];
                $now = time();
                for ($i = 11; $i >= 0; $i--) {
                    $ts = $now - ($i * 300);
                    $min = floor(date('i', $ts) / 5) * 5;
                    $keyTime = date('Y-m-d:H', $ts) . ':' . str_pad((string)$min, 2, '0', STR_PAD_LEFT);
                    
                    $traffic['history'][] = [
                        'time' => date('H:i', $ts),
                        'hits' => (int) $redis->pfcount("traffic:unique:5min:$keyTime")
                    ];
                }
            } catch (\Exception $e) {}
        }

        $stats = [
            'os' => php_uname('s') . ' ' . php_uname('r'),
            'php_version' => PHP_VERSION,
            'traffic' => $traffic,
            'load_avg' => function_exists('sys_getloadavg') ? (sys_getloadavg()[0] ?? 0) : self::getCpuOnly() / 100,
            'disk_usage' => ($disk_total !== false) ? ($disk_total - $disk_free) : 0,
            'disk_total' => $disk_total ?: 0,
            'memory_usage' => memory_get_usage(true),
            'mysql_ok' => Db::query("SELECT VERSION()")->fetchObject()->{'VERSION()'},
            'processes' => $process_count, // Добавили количество процессов
            'redis_ok' => (isset($GLOBALS['redis']) && $GLOBALS['redis']->ping()->getPayload() === 'PONG'),
            'system_info' => [
                'cpu' => self::get_cpu_model(), // Функция ниже
                'cpu_usage' => self::getCpuOnly(),
                'ram' => [
                    'used' => self::getUsedRam(),  // В ГБ
                    'total' => round(self::total_ram_in_mb())  // В ГБ
                ],
                'disk' => self::getDiskStats(), // В ГБ
            ],
        ];

        // Если это AJAX-запрос, отдаем и выходим
        if (str_contains($p, '/system_stats')) {
            header('Content-Type: application/json');
            echo json_encode($stats);
            exit;
        }

        return ['data' => ['stats' => $stats]];
    }

    private function validateTable(string $table): string
    {
        $tables = array_map(function($t) { return $t->table_name; }, self::getDbTables());
        if (!in_array($table, $tables, true)) {
            \App\Core\Firewall::redirect('/admin');
        }
        return $table;
    }

    public function edit(string $p): array
    {
        $id = intval(explode('/edit/', $p)[1]);
        $t = $this->validateTable(explode('/', explode('/admin/', $p)[1])[0]); //table name
        $ok = '';
        $row = Db::query("SELECT * FROM `$t` WHERE id=$id")->fetchObject();
        if (isset($_POST['submit'])) {
            $sql = [];
            foreach ($_POST as $k => &$v) {
                if (in_array($k, ['submit', '_csrf'], true) || is_array($v)) continue;
                if (preg_match('/date|created_at|updated_at/', $k) && is_string($v) && str_contains($v, '-')) $v = $v ? $v : 0;
                
                $sql[] = "`$k`='" . Db::realEscapeString(trim($v)) . "'";
            }

            Db::query("UPDATE `$t` SET " . implode(', ', $sql) . " WHERE id='$id'");
            
            $ok = '<div class="alert alert-success">Successfully updated!</div>';
            $row = Db::query("SELECT * FROM `$t` WHERE id=$id")->fetchObject();
        }

        return [
            'view' => 'edit',
            'data' => [
                'id' => $id,
                'table_name' => $t,
                'columns' => Db::query("SHOW COLUMNS FROM `$t`"),
                'row' => $row,
                'ok' => $ok,
                'db_tables' => self::getDbTables()
            ]
        ];
    }

    public function delete(string $p): void
    {
        $id = intval(explode('/delete/', $p)[1]);
        $t = $this->validateTable(explode('/', explode('/admin/', $p)[1])[0]);
        Db::query("DELETE FROM `$t` WHERE id='$id'");
        header("Location: " . ($_SERVER['HTTP_REFERER'] ?? '/admin/'));
        exit;
    }

    public function spam(string $p): void
    {
        $id = intval(explode('/spam/', $p)[1]);
        $t = $this->validateTable(explode('/', explode('/admin/', $p)[1])[0]);
        Db::query("DELETE FROM `$t` WHERE id='$id'");
        // Db::query("INSERT IGNORE INTO spam (`ip`) VALUES ('" . Db::realEscapeString($_GET['ip'] ?? '') . "')");
        header("Location: " . ($_SERVER['HTTP_REFERER'] ?? '/admin/'));
        exit;
    }

    public function list(string $p): array
    {
        $t = $this->validateTable(explode('/admin/', $p)[1]);
        $pg = (int)($_GET['page'] ?? 1);
        $lim = 25;
        
        $columns = [];
        $defaultSort = null;
        $colsQuery = Db::query("SHOW COLUMNS FROM `$t`");
        foreach ($colsQuery as $col) {
            $columns[] = $col;
            if ($defaultSort === null) $defaultSort = $col['Field'];
            if ($col['Key'] === 'PRI') $defaultSort = $col['Field'];
        }

        $cnt = Db::query("SELECT count(*) as count FROM `$t`")->fetchObject()->count;
        $sort = !empty($_GET['sort']) ? preg_replace('/[^a-zA-Z0-9_]/', '', $_GET['sort']) : $defaultSort;
        $validColumns = array_column($columns, 'Field');
        if (!in_array($sort, $validColumns, true)) {
            $sort = $defaultSort;
        }
        $ord = (strtolower($_GET['order'] ?? '') === 'desc') ? 'ASC' : 'DESC';
        
        return [
            'view' => 'list',
            'data' => [
                'h1' => $t,
                'columns' => $columns,
                'rows' => Db::query("SELECT * FROM `$t` ORDER BY `{$sort}` {$ord} LIMIT $lim OFFSET " . (($pg * $lim) - $lim)),
                'pages' => ceil($cnt / $lim), 
                'current_sort' => $_GET['sort'] ?? '',
                'current_order' => strtolower($_GET['order'] ?? '') === 'desc' ? 'desc' : 'asc', 
                'db_tables' => self::getDbTables()
            ]
        ];
    }

    public static function getDbTables(): array
    {
        $result = Db::query("SELECT TABLE_NAME as table_name FROM information_schema.tables WHERE TABLE_SCHEMA = DATABASE()");
        $tables = [];
        while ($t = $result->fetchObject()) {
            $tables[] = $t;
        }
        return $tables;
    }
}
