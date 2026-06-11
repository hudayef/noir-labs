<?php

namespace App\Domain\User\Entities;

class UserEntity
{
    public function __construct(
        public string $id,
        public string $name,
        public string $email,
        public bool $isActive,
        public \DateTimeImmutable $createdAt,
        public \DateTimeImmutable $updatedAt
    ) {}
}
