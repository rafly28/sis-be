<?php

namespace Database\Seeders;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class RolePermissionSeeder extends Seeder
{
    public function run()
    {
        // Roles
        $admin = Role::create(['name' => 'admin']);
        $guru = Role::create(['name' => 'guru']);
        $murid = Role::create(['name' => 'murid']);
        $orangTua = Role::create(['name' => 'orang_tua']);

        // Permissions
        Permission::create(['name' => 'manage users']);
        Permission::create(['name' => 'manage murid']);
        Permission::create(['name' => 'view murid']);

        // Assign ke role
        $admin->givePermissionTo(['manage users', 'manage murid', 'view murid']);
        $guru->givePermissionTo(['manage murid', 'view murid']);
        $murid->givePermissionTo(['view murid']);
        $orangTua->givePermissionTo(['view murid']);
    }
}
