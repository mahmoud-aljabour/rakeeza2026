<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;

final class AdminUserSeeder extends Seeder
{
    public function run(): void
    {
        $role = Role::query()->firstOrCreate([
            'name' => 'super-admin',
            'guard_name' => 'web',
        ]);

        $user = User::query()->updateOrCreate(
            ['email' => 'admin@rakeeza-ps.com'],
            [
                'name' => 'مدير ركيزة',
                'password' => 'password',
            ],
        );

        $user->assignRole($role);
    }
}
