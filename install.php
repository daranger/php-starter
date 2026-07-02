<?php

declare(strict_types=1);

if (php_sapi_name() !== 'cli') {
    die("This script can only be run from the command line.\n");
}

require_once __DIR__ . '/vendor/autoload.php';

use App\Services\InstallerService;

echo "=====================================\n";
echo "    PHP-FR Automated Installer       \n";
echo "=====================================\n\n";

if (file_exists(__DIR__ . '/.env')) {
    echo "It looks like the application is already installed (.env exists).\n";
    echo "If you want to reinstall, please delete the .env file first.\n";
    exit(1);
}

$options = getopt('', [
    'host::',
    'port::',
    'db::',
    'user::',
    'pass::',
    'admin-email::',
    'admin-pass::',
    'help'
]);

if (isset($options['help'])) {
    echo "Usage: php install.php [options]\n\n";
    echo "Options:\n";
    echo "  --host=...         Database host (default: localhost)\n";
    echo "  --port=...         Database port (default: 3306)\n";
    echo "  --db=...           Database name (default: php-starter)\n";
    echo "  --user=...         Database user (default: root)\n";
    echo "  --pass=...         Database password\n";
    echo "  --admin-email=...  Admin account email\n";
    echo "  --admin-pass=...   Admin account password\n";
    exit(0);
}

$dbHost = $options['host'] ?? null;
if ($dbHost === null) $dbHost = readline("Database Host [localhost]: ") ?: 'localhost';

$dbPort = $options['port'] ?? null;
if ($dbPort === null) $dbPort = readline("Database Port [3306]: ") ?: '3306';

$dbName = $options['db'] ?? null;
if ($dbName === null) $dbName = readline("Database Name [php-starter]: ") ?: 'php-starter';

$dbUser = $options['user'] ?? null;
if ($dbUser === null) $dbUser = readline("Database User [root]: ") ?: 'root';

$dbPass = $options['pass'] ?? null;
if ($dbPass === null) {
    echo "Database Password: ";
    if (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN') {
        $dbPass = readline();
    } else {
        system('stty -echo');
        $dbPass = trim(fgets(STDIN));
        system('stty echo');
        echo "\n";
    }
}

if (!isset($options['admin-email'])) {
    echo "\n--- Admin Account ---\n";
}
$adminEmail = $options['admin-email'] ?? null;
while (empty($adminEmail) || !filter_var($adminEmail, FILTER_VALIDATE_EMAIL)) {
    if (isset($options['admin-email'])) {
        die("\n[ERROR] Invalid admin-email provided via flags.\n");
    }
    $adminEmail = readline("Admin Email: ");
}

$adminPassword = $options['admin-pass'] ?? null;
while (empty($adminPassword)) {
    if (isset($options['admin-pass'])) {
        die("\n[ERROR] Admin password cannot be empty.\n");
    }
    echo "Admin Password: ";
    if (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN') {
        $adminPassword = readline();
    } else {
        system('stty -echo');
        $adminPassword = trim(fgets(STDIN));
        system('stty echo');
        echo "\n";
    }
}

echo "\nInstalling...\n";

$result = InstallerService::install($dbHost, $dbPort, $dbName, $dbUser, $dbPass, $adminEmail, $adminPassword);

if ($result['success']) {
    echo "\n[OK] Installation completed successfully!\n";
    echo "[OK] You can now access your application.\n";
    exit(0);
} else {
    echo "\n[ERROR] Installation failed:\n";
    echo $result['error'] . "\n";
    exit(1);
}
