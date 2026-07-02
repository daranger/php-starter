<?php

declare(strict_types=1);

namespace App\Console;

abstract class Command
{
    protected string $signature = '';
    protected string $description = '';

    public function getSignature(): string
    {
        return $this->signature;
    }

    public function getDescription(): string
    {
        return $this->description;
    }

    abstract public function handle(array $args): void;

    protected function info(string $message): void
    {
        echo "\033[32m{$message}\033[0m\n";
    }

    protected function error(string $message): void
    {
        echo "\033[31m{$message}\033[0m\n";
    }

    protected function warning(string $message): void
    {
        echo "\033[33m{$message}\033[0m\n";
    }
}
