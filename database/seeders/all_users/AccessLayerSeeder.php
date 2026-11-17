<?php

namespace Database\Seeders\all_users;

use App\Models\AllUsers\User;
use App\Models\PublicSettings\Permission;
use App\Models\PublicSettings\Role;
use App\Support\PermissionCatalog;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class AccessLayerSeeder extends Seeder
{
    public function run(): void
    {
        PermissionCatalog::sync();

        $adminRole = Role::query()->firstOrCreate(
            ['slug' => 'admin'],
            ['name' => 'مدير النظام', 'description' => 'صلاحيات كاملة'],
        );
        $adminRole->permissions()->sync(Permission::query()->pluck('id'));

        User::query()->updateOrCreate(
            ['email' => 'admin@admin.com'],
            [
                'name' => 'مدير Rasedo',
                'username' => 'admin',
                'password' => Hash::make('password'),
                'status' => 'active',
                'role_id' => $adminRole->id,
                'two_factor_enabled' => true,
                'email_verified_at' => now(),
            ],
        );
    }
}
