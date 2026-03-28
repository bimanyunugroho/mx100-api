<?php

namespace App\Repositories\Contracts\Auth;

use App\DTOs\Auth\LoginDataDTO;
use App\DTOs\Auth\RegisterDataDTO;
use App\Models\User;

interface AuthRepositoryInterface
{
    public function register(RegisterDataDTO $registerDataDTO): User;

    public function login(LoginDataDTO $loginDataDTO): ?User;

    public function logout(User $user): void;

    public function logoutAll(User $user): void;

    public function findByEmail(string $email): ?User;
}
