<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Core\Request;
use App\Core\Response;
use App\Services\InstallerService;

class InstallController
{
    public function handle(Request $request): Response
    {
        if ($request->method() === 'POST') {
            return $this->setup($request);
        }

        return $this->index();
    }

    private function index(): Response
    {
        $html = view('install');
        return new Response($html);
    }

    private function setup(Request $request): Response
    {
        $dbHost = $_POST['db_host'] ?? 'localhost';
        $dbPort = $_POST['db_port'] ?? '3306';
        $dbName = $_POST['db_name'] ?? 'php-starter';
        $dbUser = $_POST['db_user'] ?? 'root';
        $dbPass = $_POST['db_pass'] ?? '';
        
        $adminEmail = $_POST['admin_email'] ?? '';
        $adminPassword = $_POST['admin_password'] ?? '';

        if (empty($dbHost) || empty($dbName) || empty($dbUser) || empty($adminEmail) || empty($adminPassword)) {
            return Response::json(['success' => false, 'error' => 'Please fill in all required fields.']);
        }

        $result = InstallerService::install($dbHost, $dbPort, $dbName, $dbUser, $dbPass, $adminEmail, $adminPassword);

        return Response::json($result);
    }
}
