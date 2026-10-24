<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\User;

class UsersTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        User::create([
            'name' => 'Nguyen Van A',
            'email' => 'nguyenvana@example.com',
            'password' => bcrypt('123456'),
            'phone_number' => '0123456789',
            'status' => 'pending',
            'avatar' => null,
            'address' => 'Da Nang, Vietnam',
            'role_id' => 1,
            'created_at' => now(),
            'updated_at' => now(),
        ]);


        User::create([
            'name' => 'Nguyen Van B',
            'email' => 'nguyenvanb@example.com',
            'password' => bcrypt('123456'),
            'phone_number' => '0987654321',
            'status' => 'pending',
            'avatar' => null,
            'address' => 'Long An, Vietnam',
            'role_id' => 2,
            'created_at' => now(),
            'updated_at' => now(),
        ]);


        User::create([
            'name' => 'Pham Tuan Duy',
            'email' => 'duypham@example.com',
            'password' => bcrypt('123456'),
            'phone_number' => '0938036120',
            'status' => 'pending',
            'avatar' => null,
            'address' => 'TP.HCM, Vietnam',
            'role_id' => 3,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }
}
