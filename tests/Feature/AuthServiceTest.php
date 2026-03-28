<?php

namespace Tests\Feature;

use App\DTOs\Auth\LoginDataDTO;
use App\DTOs\Auth\RegisterDataDTO;
use App\Enums\RoleUserEnum;
use App\Exceptions\InvalidCredentialsException;
use App\Exceptions\UnprocessableException;
use App\Repositories\Contracts\Auth\AuthRepositoryInterface;
use App\Services\Auth\AuthService;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Mockery\MockInterface;
use Tests\TestCase;
use Mockery;

class AuthServiceTest extends TestCase
{
    use RefreshDatabase, WithFaker;
    private AuthService $authService;
    private MockInterface $authRepository;

    protected function setUp(): void
    {
        parent::setUp();

        $this->authRepository = Mockery::mock(AuthRepositoryInterface::class);
        $this->authService    = new AuthService($this->authRepository);
    }

    public function test_register_berhasil_untuk_employer_dengan_company_name(): void
    {
        $data = new RegisterDataDTO(
            name:         'EMPLOYER TESTING',
            email:        'testingemployer@gmail.com',
            password:     'password123',
            role:         RoleUserEnum::EMPLOYER,
            company_name: 'PT. Testing',
        );

        $user = User::factory()->employer()->create();

        $this->authRepository
            ->shouldReceive('register')
            ->once()
            ->with($data)
            ->andReturn($user);

        $result = $this->authService->register($data);

        $this->assertArrayHasKey('user', $result);
        $this->assertArrayHasKey('token', $result);
        $this->assertEquals('Bearer', $result['token_type']);
    }

    public function test_register_berhasil_untuk_freelancer_tanpa_company_name(): void
    {
        $data = new RegisterDataDTO(
            name:     'FREELANCER TESTING',
            email:    'testingfreelancer@gmail.com',
            password: 'password123',
            role:     RoleUserEnum::FREELANCER,
        );

        $user = User::factory()->freelancer()->create();

        $this->authRepository
            ->shouldReceive('register')
            ->once()
            ->andReturn($user);

        $result = $this->authService->register($data);

        $this->assertTrue($result['token_type'] === 'Bearer');
    }

    public function test_register_gagal_jika_employer_tanpa_company_name(): void
    {
        $this->expectException(UnprocessableException::class);

        $data = new RegisterDataDTO(
            name:         'EMPLOYER TESTING',
            email:        'testingemployer@gmail.com',
            password:     'password123',
            role:         RoleUserEnum::EMPLOYER,
            company_name: null,
        );

        $this->authRepository->shouldNotReceive('register');

        $this->authService->register($data);
    }

    public function test_login_berhasil_dengan_kredensial_valid(): void
    {
        $data = new LoginDataDTO(
            email:    'testingemployer@gmail.com',
            password: 'password123',
        );

        $user = User::factory()->employer()->create();

        $this->authRepository
            ->shouldReceive('login')
            ->once()
            ->with($data)
            ->andReturn($user);

        $result = $this->authService->login($data);

        $this->assertArrayHasKey('user', $result);
        $this->assertArrayHasKey('token', $result);
        $this->assertSame($user, $result['user']);
    }

    public function test_login_gagal_dengan_kredensial_salah(): void
    {
        $this->expectException(InvalidCredentialsException::class);

        $data = new LoginDataDTO(
            email:    'salah@test.com',
            password: 'cumachecksalahnyaaja',
        );

        $this->authRepository
            ->shouldReceive('login')
            ->once()
            ->andReturn(null); // null = kredensial salah

        $this->authService->login($data);
    }

    public function test_logout_memanggil_repository_logout(): void
    {
        $user = User::factory()->employer()->create();

        $this->authRepository
            ->shouldReceive('logout')
            ->once()
            ->with($user);

        $this->authService->logout($user);
        $this->assertTrue(true);
    }

    public function test_logout_all_memanggil_repository_logout_all(): void
    {
        $user = User::factory()->employer()->create();

        $this->authRepository
            ->shouldReceive('logoutAll')
            ->once()
            ->with($user);

        $this->authService->logoutAll($user);
        $this->assertTrue(true);
    }

    protected function test_tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }
}
