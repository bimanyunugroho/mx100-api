<?php

namespace App\Services\Auth;

use App\DTOs\Auth\LoginDataDTO;
use App\DTOs\Auth\RegisterDataDTO;
use App\Enums\RoleUserEnum;
use App\Exceptions\InvalidCredentialsException;
use App\Exceptions\UnprocessableException;
use App\Models\User;
use App\Repositories\Contracts\Auth\AuthRepositoryInterface;
use Illuminate\Support\Facades\DB;

class AuthService
{
    public function __construct(
        private readonly AuthRepositoryInterface $authRepository
    ) {}

    /**
     * Registrasi user baru.
     * employer wajib mengisi company_name.
     *
     * @return array{ user: User, token: string, token_type: string }
     * @throws UnprocessableException
     */
    public function register(RegisterDataDTO $registerDataDTO): array
    {
        if ($registerDataDTO->role === RoleUserEnum::EMPLOYER && empty($registerDataDTO->company_name)) {
            throw new UnprocessableException(
                'Data tidak valid',
                ['company_name' => ['Nama Perusahaan wajib diisi untuk role employer.']]
            );
        }

        $user = DB::transaction(function () use ($registerDataDTO): User {
            return $this->authRepository->register($registerDataDTO);
        });

        $token = $user->createToken('web')->plainTextToken;

        return [
            'user'       => $user,
            'token'      => $token,
            'token_type' => 'Bearer',
        ];
    }

    /**
     * Login user dengan email dan password.
     *
     * @return array{ user: User, token: string, token_type: string }
     * @throws InvalidCredentialsException
     */
    public function login(LoginDataDTO $loginDataDTO): array
    {
        $user = $this->authRepository->login($loginDataDTO);

        if (!$user) {
            throw new InvalidCredentialsException();
        }

        $token = $user->createToken($loginDataDTO->token_name)->plainTextToken;

        return [
            'user'       => $user,
            'token'      => $token,
            'token_type' => 'Bearer',
        ];
    }

    /**
     * Logout dari device saat ini — hapus token aktif saja.
     */
    public function logout(User $user): void
    {
        $this->authRepository->logout($user);
    }

    /**
     * Logout dari semua device — hapus semua token user.
     */
    public function logoutAll(User $user): void
    {
        $this->authRepository->logoutAll($user);
    }

    /**
     * Return user yang sedang login beserta roles-nya.
     */
    public function me(User $user): User
    {
        return $user->load('roles');
    }
}
