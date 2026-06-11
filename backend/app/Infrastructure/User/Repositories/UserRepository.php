<?php

namespace App\Infrastructure\User\Repositories;

use App\Domain\User\Entities\UserEntity;
use App\Domain\User\Repositories\UserRepositoryInterface;
use App\Infrastructure\User\Models\User;

class UserRepository implements UserRepositoryInterface
{
    public function findById(string $id): ?UserEntity
    {
        $user = User::find($id);
        if (!$user) return null;
        return $this->toEntity($user);
    }

    public function findByEmail(string $email): ?UserEntity
    {
        $user = User::where('email', $email)->first();
        if (!$user) return null;
        return $this->toEntity($user);
    }

    public function create(array $data): UserEntity
    {
        $user = User::create($data);
        return $this->toEntity($user);
    }

    private function toEntity(User $user): UserEntity
    {
        return new UserEntity(
            $user->id,
            $user->name,
            $user->email,
            (bool) $user->is_active,
            new \DateTimeImmutable($user->created_at),
            new \DateTimeImmutable($user->updated_at)
        );
    }
}
