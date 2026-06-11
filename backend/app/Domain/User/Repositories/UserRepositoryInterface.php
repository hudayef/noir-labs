<?php

namespace App\Domain\User\Repositories;

use App\Domain\User\Entities\UserEntity;

interface UserRepositoryInterface
{
    public function findById(string $id): ?UserEntity;
    public function findByEmail(string $email): ?UserEntity;
    public function create(array $data): UserEntity;
}
