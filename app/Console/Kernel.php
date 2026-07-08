<?php

declare(strict_types=1);

namespace App\Console;

use App\Core\Container;
use Exception;

class Kernel
{
    private array $commands = [
        Commands\MigrateCommand::class,
        Commands\ResetDatabaseCommand::class,
    ];

    public function handle(array $argv): void
    {
        array_shift($argv); // Убираем имя скрипта (console)
        
        $commandName = array_shift($argv);

        if (!$commandName) {
            $this->printHelp();
            return;
        }

        foreach ($this->commands as $commandClass) {
            $command = Container::getInstance()->make($commandClass);
            
            if ($command->getSignature() === $commandName) {
                try {
                    $command->handle($argv);
                } catch (Exception $e) {
                    echo "\033[31mError: {$e->getMessage()}\033[0m\n";
                    echo "{$e->getTraceAsString()}\n";
                }
                return;
            }
        }

        echo "\033[31mCommand '{$commandName}' not found.\033[0m\n";
    }

    private function printHelp(): void
    {
        echo "\033[32mPHP Starter Kit CLI\033[0m\n\n";
        echo "Available commands:\n";
        
        foreach ($this->commands as $commandClass) {
            $command = Container::getInstance()->make($commandClass);
            echo "  \033[33m{$command->getSignature()}\033[0m\t- {$command->getDescription()}\n";
        }
    }
}
