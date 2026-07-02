<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Models\User;

class UserRepository extends BaseRepository
{
    protected function table(): string
    {
        return 'users';
    }

    protected function mapToModel(array $data): User
    {
        return new User(
            id: (int) $data['id'],
            email: $data['email'],
            password: $data['password'],
            name: $data['name'] ?? null,
            google_id: $data['google_id'] ?? null,
            avatar: $data['avatar'] ?? null,
            two_factor_secret: $data['two_factor_secret'] ?? null,
            created_at: $data['created_at'] ?? null,
            updated_at: $data['updated_at'] ?? null
        );
    }

    public function findByEmail(string $email): ?User
    {
        $stmt = $this->db->prepare("SELECT * FROM {$this->table()} WHERE email = :email LIMIT 1");
        $stmt->execute(['email' => $email]);
        
        $result = $stmt->fetch();
        if (!$result) {
            return null;
        }

        return $this->mapToModel($result);
    }
}
