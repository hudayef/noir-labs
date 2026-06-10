<?php

namespace Tests\Unit\Application\User;

use App\Application\User\DTO\RegisterUserDTO;
use App\Application\User\Services\AuthService;
use App\Domain\User\Entities\UserEntity;
use App\Domain\User\Repositories\UserRepositoryInterface;
use Illuminate\Support\Facades\Hash;
use Mockery;
use PHPUnit\Framework\TestCase;

class AuthServiceTest extends TestCase
{
    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }

    public function test_it_registers_a_user_successfully()
    {
        $dto = new RegisterUserDTO('John Doe', 'john@example.com', 'password123');

        $expectedEntity = new UserEntity(
            '123e4567-e89b-12d3-a456-426614174000',
            'John Doe',
            'john@example.com',
            true,
            new \DateTimeImmutable(),
            new \DateTimeImmutable()
        );

        $mockRepo = Mockery::mock(UserRepositoryInterface::class);
        $mockRepo->shouldReceive('create')->once()->andReturn($expectedEntity);

        Hash::shouldReceive('make')->once()->with('password123')->andReturn('hashed_password');

        $authService = new AuthService($mockRepo);
        $result = $authService->register($dto);

        $this->assertEquals($expectedEntity->email, $result->email);
        $this->assertEquals($expectedEntity->name, $result->name);
    }
}
