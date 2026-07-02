<?php

declare(strict_types=1);

namespace App\Contracts;

interface RepositoryInterface
{
    public function find(int $id): ?object;
    public function all(): array;
    public function create(array $data): int;
    public function update(int $id, array $data): bool;
    public function delete(int $id): bool;
}
