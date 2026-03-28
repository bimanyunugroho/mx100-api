<?php

namespace Tests;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\TestCase as BaseTestCase;
use Illuminate\Foundation\Testing\WithFaker;
use Laravel\Sanctum\Sanctum;
use Spatie\Permission\Models\Role;

abstract class TestCase extends BaseTestCase
{
    use RefreshDatabase, WithFaker;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seedRoles();
    }

    protected function createEmployer(array $attributes = []): User
    {
        $user = User::factory()->employer()->create($attributes);
        $user->assignRole('employer');

        return $user;
    }

    protected function createFreelancer(array $attributes = []): User
    {
        $user = User::factory()->freelancer()->create($attributes);
        $user->assignRole('freelancer');

        return $user;
    }

    private function seedRoles(): void
    {
        Role::firstOrCreate(['name' => 'employer',   'guard_name' => 'sanctum']);
        Role::firstOrCreate(['name' => 'freelancer', 'guard_name' => 'sanctum']);
    }
}
