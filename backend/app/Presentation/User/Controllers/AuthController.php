<?php

namespace App\Presentation\User\Controllers;

use App\Application\User\DTO\RegisterUserDTO;
use App\Application\User\Services\AuthService;
use App\Http\Controllers\Controller;
use App\Presentation\User\Requests\RegisterUserRequest;
use App\Presentation\User\Resources\UserResource;

class AuthController extends Controller
{
    public function __construct(
        private AuthService $authService
    ) {}

    public function register(RegisterUserRequest $request)
    {
        $dto = new RegisterUserDTO(
            $request->validated('name'),
            $request->validated('email'),
            $request->validated('password')
        );

        $userEntity = $this->authService->register($dto);

        return response()->json([
            'success' => true,
            'message' => 'User registered successfully',
            'data' => new UserResource($userEntity),
        ], 201);
    }
}
