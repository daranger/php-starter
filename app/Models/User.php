<?php

declare(strict_types=1);

namespace App\Models;

class User
{
    public function __construct(
        public readonly int $id,
        public readonly string $email,
        public readonly string $password,
        public readonly ?string $name = null,
        public readonly ?string $google_id = null,
        public readonly ?string $avatar = null,
        public readonly ?string $two_factor_secret = null,
        public readonly ?string $created_at = null,
        public readonly ?string $updated_at = null
    ) {}
}
