<?php

namespace App\Repositories\Eloquents\Auth;

use App\DTOs\Auth\LoginDataDTO;
use App\DTOs\Auth\RegisterDataDTO;
use App\Models\User;
use App\Repositories\Contracts\Auth\AuthRepositoryInterface;
use Illuminate\Support\Facades\Hash;
use Spatie\QueryBuilder\QueryBuilder;

class AuthRepository implements AuthRepositoryInterface
{

    public function register(RegisterDataDTO $registerDataDTO): User
    {
        $user = User::create([
            'name'         => $registerDataDTO->name,
            'email'        => $registerDataDTO->email,
            'password'     => $registerDataDTO->password,
            'role'         => $registerDataDTO->role,
            'company_name' => $registerDataDTO->company_name,
            'phone'        => $registerDataDTO->phone,
        ]);

        $user->assignRole($registerDataDTO->role->value);

        return $user;
    }

    public function login(LoginDataDTO $loginDataDTO): ?User
    {
        $user = $this->findByEmail($loginDataDTO->email);

        if (!$user || !Hash::check($loginDataDTO->password, $user->password)) {
            return null;
        }

        return $user;
    }

    public function logout(User $user): void
    {
        $user->currentAccessToken()->delete();
    }

    public function logoutAll(User $user): void
    {
        $user->tokens()->delete();
    }

    public function findByEmail(string $email): ?User
    {
        return QueryBuilder::for(User::class)
            ->where('email', $email)
            ->first();
    }
}
