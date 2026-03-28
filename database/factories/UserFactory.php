<?php

namespace Database\Factories;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Hash;
use App\Enums\RoleUserEnum;

/**
 * @extends Factory<User>
 */
class UserFactory extends Factory
{
    protected $model = User::class;
    protected static string $password = 'sandi12345';

    protected static int $freelancerCounter = 1;
    protected static int $employerCounter = 1;

    public function definition(): array
    {
        return [
            'name'              => fake()->name(),
            'email'             => fake()->unique()->safeEmail(),
            'password'          => Hash::make(static::$password),
            'role'              => RoleUserEnum::FREELANCER,
            'company_name'      => null,
            'phone'             => $this->generatePhone(),
            'email_verified_at' => now(),
        ];
    }

    private function generatePhone(): string
    {
        $prefix = fake()->randomElement(['081', '085', '087', '089']);
        $remainingLength = 12 - strlen($prefix);

        return $prefix . fake()->numerify(str_repeat('#', $remainingLength));
    }

    public function employer(): static
    {
        return $this->state(function () {
            return [
                'role'         => RoleUserEnum::EMPLOYER,
                'email'        => 'employer' . self::$employerCounter++ . '@gmail.com',
                'company_name' => fake()->company(),
            ];
        });
    }

    public function freelancer(): static
    {
        return $this->state(function () {
            return [
                'role'         => RoleUserEnum::FREELANCER,
                'email'        => 'freelancer' . self::$freelancerCounter++ . '@gmail.com',
                'company_name' => null,
            ];
        });
    }
}
