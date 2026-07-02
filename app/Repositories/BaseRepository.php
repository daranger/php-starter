<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Contracts\RepositoryInterface;
use PDO;

abstract class BaseRepository implements RepositoryInterface
{
    public function __construct(
        protected readonly PDO $db
    ) {}

    abstract protected function table(): string;
    abstract protected function mapToModel(array $data): object;

    public function find(int $id): ?object
    {
        $stmt = $this->db->prepare("SELECT * FROM {$this->table()} WHERE id = :id LIMIT 1");
        $stmt->execute(['id' => $id]);
        
        $result = $stmt->fetch();
        if (!$result) {
            return null;
        }

        return $this->mapToModel($result);
    }

    public function all(): array
    {
        $stmt = $this->db->query("SELECT * FROM {$this->table()}");
        $results = $stmt->fetchAll();
        
        return array_map(fn($row) => $this->mapToModel($row), $results);
    }

    public function create(array $data): int
    {
        $columns = implode(', ', array_keys($data));
        $placeholders = ':' . implode(', :', array_keys($data));
        
        $stmt = $this->db->prepare("INSERT INTO {$this->table()} ({$columns}) VALUES ({$placeholders})");
        $stmt->execute($data);
        
        return (int) $this->db->lastInsertId();
    }

    public function update(int $id, array $data): bool
    {
        $fields = [];
        foreach ($data as $key => $value) {
            $fields[] = "{$key} = :{$key}";
        }
        $setClause = implode(', ', $fields);
        
        $data['id'] = $id;
        
        $stmt = $this->db->prepare("UPDATE {$this->table()} SET {$setClause} WHERE id = :id");
        return $stmt->execute($data);
    }

    public function delete(int $id): bool
    {
        $stmt = $this->db->prepare("DELETE FROM {$this->table()} WHERE id = :id");
        return $stmt->execute(['id' => $id]);
    }
}
