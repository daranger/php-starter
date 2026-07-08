<?php
declare(strict_types=1);

namespace App\Admin\Controllers;

use App\Core\Db;

class SettingsController
{
    public function index()
    {
        $settingsRaw = Db::query("SELECT * FROM settings ORDER BY setting_group ASC, setting_label ASC");
        
        $settingsByGroup = [];
        foreach ($settingsRaw as $s) {
            $group = $s['setting_group'];
            if (!isset($settingsByGroup[$group])) {
                $settingsByGroup[$group] = [];
            }
            $settingsByGroup[$group][] = $s;
        }

        // Gather System Info
        $systemInfo = [
            'PHP Version' => phpversion(),
            'OS' => php_uname('s') . ' ' . php_uname('r'),
            'Zend Engine' => zend_version(),
            'Memory Limit' => ini_get('memory_limit'),
            'Max Execution Time' => ini_get('max_execution_time') . 's',
            'Upload Max Filesize' => ini_get('upload_max_filesize'),
            'Post Max Size' => ini_get('post_max_size'),
            'Extensions Loaded' => count(get_loaded_extensions()),
        ];
        
        try {
            $dbVersion = Db::query("SELECT VERSION() as v")->fetchObject()->v;
            $systemInfo['Database Version'] = $dbVersion;
        } catch (\Throwable $e) {
            $systemInfo['Database Version'] = 'Unknown';
        }

        $result = Db::query("SELECT TABLE_NAME as table_name FROM information_schema.tables WHERE TABLE_SCHEMA = DATABASE()");
        $dbTables = [];
        while ($t = $result->fetchObject()) {
            $dbTables[] = $t;
        }

        return [
            'view' => 'settings',
            'data' => [
                'h1' => 'Settings',
                'settingsByGroup' => $settingsByGroup,
                'systemInfo' => $systemInfo,
                'db_tables' => $dbTables
            ]
        ];
    }

    public function save()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header("Location: /admin/settings");
            exit;
        }

        $postData = $_POST['settings'] ?? [];
        
        // Handling checkboxes (booleans). Since unchecked checkboxes don't send anything,
        // we first get all boolean settings from the DB to set them to '0' if they are missing in $_POST.
        $booleans = Db::query("SELECT setting_key FROM settings WHERE setting_type = 'boolean'");
        foreach ($booleans as $b) {
            $key = $b['setting_key'];
            if (!isset($postData[$key])) {
                $postData[$key] = '0';
            }
        }

        $pdo = \App\Core\Container::getInstance()->make(\PDO::class);
        $stmt = $pdo->prepare("UPDATE settings SET setting_value = :val WHERE setting_key = :key");

        // Handle File Uploads
        if (isset($_FILES['settings_file']['name']) && is_array($_FILES['settings_file']['name'])) {
            foreach ($_FILES['settings_file']['name'] as $key => $filename) {
                if ($_FILES['settings_file']['error'][$key] === UPLOAD_ERR_OK) {
                    $tmpName = $_FILES['settings_file']['tmp_name'][$key];
                    if ($key === 'site_favicon') {
                        $target = __DIR__ . '/../../../public/assets/favicon.png';
                        move_uploaded_file($tmpName, $target);
                        $postData[$key] = '/assets/favicon.png'; // Update DB value
                    }
                }
            }
        }

        foreach ($postData as $key => $value) {
            $stmt->execute(['val' => (string)$value, 'key' => $key]);
        }

        header("Location: /admin/settings?saved=1");
        exit;
    }
}
