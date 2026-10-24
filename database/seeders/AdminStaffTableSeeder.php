<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\User;

class AdminStaffTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        User::create([
            'name' => 'Admin',
            'email' => 'admin@example.com',
            'password' => bcrypt('123456'),
            'phone_number' => '0777777777',
            'status' => 'active',
            'avatar' => null,
            'address' => 'TP.HCM, Vietnam',
            'role_id' => 1,
            'created_at' => now(),
            'updated_at' => now(),
        ]);


        User::create([
            'name' => 'Staff',
            'email' => 'staff@example.com',
            'password' => bcrypt('123456'),
            'phone_number' => '0888888888',
            'status' => 'active',
            'avatar' => null,
            'address' => 'Long An, Vietnam',
            'role_id' => 2,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }
}
