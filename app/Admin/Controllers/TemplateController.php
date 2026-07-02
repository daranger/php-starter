<?php

declare(strict_types=1);

namespace App\Admin\Controllers;

use App\Core\Session;

class TemplateController
{
    private string $viewsDir = __DIR__ . '/../../../resources/views';

    public function index(): array
    {
        $files = $this->getTemplateFiles();
        
        $selectedFile = $_GET['file'] ?? '';
        $content = '';
        $hasBackup = false;

        if ($selectedFile && isset($files[$selectedFile])) {
            $filePath = $files[$selectedFile]['path'];
            if (file_exists($filePath)) {
                $content = file_get_contents($filePath);
                $backupPath = $this->getBackupPath($filePath);
                $hasBackup = file_exists($backupPath);
            }
        }

        return [
            'view' => 'templates',
            'data' => [
                'h1' => 'Template Editor',
                'files' => $files,
                'selectedFile' => $selectedFile,
                'content' => $content,
                'hasBackup' => $hasBackup,
                'db_tables' => AdminController::getDbTables() // We need db_tables for the sidebar layout
            ]
        ];
    }

    public function save(): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header("Location: /admin/templates");
            exit;
        }

        $selectedFile = $_POST['file'] ?? '';
        $content = $_POST['content'] ?? '';
        $files = $this->getTemplateFiles();

        if ($selectedFile && isset($files[$selectedFile])) {
            $filePath = $files[$selectedFile]['path'];
            $backupPath = $this->getBackupPath($filePath);

            // Create backup on first edit
            if (!file_exists($backupPath)) {
                copy($filePath, $backupPath);
            }

            file_put_contents($filePath, $content);
            Session::flash('success', 'Template saved successfully!');
            header("Location: /admin/templates?file=" . urlencode($selectedFile));
            exit;
        }

        Session::flash('error', 'Invalid file.');
        header("Location: /admin/templates");
        exit;
    }

    public function restore(): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header("Location: /admin/templates");
            exit;
        }

        $selectedFile = $_POST['file'] ?? '';
        $files = $this->getTemplateFiles();

        if ($selectedFile && isset($files[$selectedFile])) {
            $filePath = $files[$selectedFile]['path'];
            $backupPath = $this->getBackupPath($filePath);

            if (file_exists($backupPath)) {
                copy($backupPath, $filePath);
                Session::flash('success', 'Template restored to original successfully!');
            } else {
                Session::flash('error', 'No backup found.');
            }
            
            header("Location: /admin/templates?file=" . urlencode($selectedFile));
            exit;
        }

        header("Location: /admin/templates");
        exit;
    }

    private function getTemplateFiles(): array
    {
        $iterator = new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator($this->viewsDir));
        $files = [];
        
        foreach ($iterator as $file) {
            if ($file->isFile() && $file->getExtension() === 'php') {
                $path = $file->getRealPath();
                // Exclude backups and maybe the admin directory itself if we want
                if (!str_ends_with($path, '.original.php')) {
                    // Get relative path from views dir
                    $relativePath = str_replace(realpath($this->viewsDir) . DIRECTORY_SEPARATOR, '', $path);
                    $relativePath = str_replace('\\', '/', $relativePath); // Normalize slashes for Windows
                    
                    if (str_starts_with($relativePath, 'admin/')) {
                        continue;
                    }
                    
                    $files[$relativePath] = [
                        'name' => $relativePath,
                        'path' => $path
                    ];
                }
            }
        }

        ksort($files);
        return $files;
    }

    private function getBackupPath(string $filePath): string
    {
        return preg_replace('/\.php$/', '.original.php', $filePath);
    }
}
