<?php

namespace App\Application\User\Services;

use App\Application\User\DTO\RegisterUserDTO;
use App\Domain\User\Entities\UserEntity;
use App\Domain\User\Repositories\UserRepositoryInterface;
use Illuminate\Support\Facades\Hash;

class AuthService
{
    public function __construct(
        private UserRepositoryInterface $userRepository
    ) {}

    public function register(RegisterUserDTO $dto): UserEntity
    {
        $hashedPassword = Hash::make($dto->password);

        return $this->userRepository->create([
            'name' => $dto->name,
            'email' => $dto->email,
            'password_hash' => $hashedPassword,
            'is_active' => true,
        ]);
    }
}
