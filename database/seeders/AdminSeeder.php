<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class AdminSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        User::create([
            'name' => 'Admin',
            'email' => 'admin@moksham.com',
            'password' => Hash::make('Admin@123'),
            'role' => 'admin',
            'status' => true,
            // 'created_by' => 1,
            // 'updated_by' => 1,
        ]);
    }
}
