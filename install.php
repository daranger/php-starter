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

$dbHost = readline("Database Host [localhost]: ") ?: 'localhost';
$dbPort = readline("Database Port [3306]: ") ?: '3306';
$dbName = readline("Database Name [php-starter]: ") ?: 'php-starter';
$dbUser = readline("Database User [root]: ") ?: 'root';

// Read password securely without echoing if possible (fallback to simple readline)
echo "Database Password: ";
if (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN') {
    $dbPass = readline();
} else {
    system('stty -echo');
    $dbPass = trim(fgets(STDIN));
    system('stty echo');
    echo "\n";
}

echo "\n--- Admin Account ---\n";
$adminEmail = '';
while (empty($adminEmail) || !filter_var($adminEmail, FILTER_VALIDATE_EMAIL)) {
    $adminEmail = readline("Admin Email: ");
}

$adminPassword = '';
while (empty($adminPassword)) {
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
