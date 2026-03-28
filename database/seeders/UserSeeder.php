<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use App\Enums\RoleUserEnum;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $employer1 = User::create([
            'name'              => 'Kurniawan',
            'email'             => 'maskurniawan@gmail.com',
            'password'          => Hash::make('maskurniawan12345'),
            'role'              => RoleUserEnum::EMPLOYER,
            'company_name'      => 'KOPERASI NUSANTARA',
            'phone'             => '081234567890',
            'email_verified_at' => now(),
        ]);
        $employer1->assignRole(RoleUserEnum::EMPLOYER);

        $employer2 = User::create([
            'name'              => 'Bimanyu',
            'email'             => 'bimanyu@gmail.com',
            'password'          => Hash::make('bimanyu12345'),
            'role'              => RoleUserEnum::EMPLOYER,
            'company_name'      => 'PT. BUANA VARIA KOMPUTAMA',
            'phone'             => '082345678901',
            'email_verified_at' => now(),
        ]);
        $employer2->assignRole(RoleUserEnum::EMPLOYER);

        // Random Employers — data dummy tambahan
        /* Untuk employer sepertinya lebih mudah langsung pakai seeder untuk testing
        tapi kalau mau ingin random juga bisa di uncommenct script ini

         * User::factory()
            ->employer()
            ->count(3)
            ->create()
            ->each(fn (User $user) => $user->assignRole(RoleUserEnum::EMPLOYER));

         * */

        // Random Freelancers
        User::factory()
            ->freelancer()
            ->count(5)
            ->create()
            ->each(fn (User $user) => $user->assignRole(RoleUserEnum::FREELANCER));
    }
}
