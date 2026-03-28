<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;
use App\Enums\RoleUserEnum;

class RoleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Barang kali ada cache, jadi dimitigasi Reset dulu si cache spatie sebelum buat role
        app()[PermissionRegistrar::class]->forgetCachedPermissions();

        foreach (RoleUserEnum::cases() as $role) {
            Role::findOrCreate($role->value, 'sanctum');
        }
    }
}
