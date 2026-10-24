<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Role;
use App\Models\Permission;

class RolePermissionTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $adminRole = Role::where('name', 'admin')->first();
        $staffRole = Role::where('name', 'staff')->first();

        $permissions = Permission::all();

        //Admin có toàn bộ quyền
        $adminRole->permissions()->sync($permissions);

        //Staff có 1 số quyền
        $staffPermissions = $permissions->whereIn('name', [
            'manage_products',
            'manage_contacts',
        ]);
        $staffRole->permissions()->sync($staffPermissions);
    }
}
