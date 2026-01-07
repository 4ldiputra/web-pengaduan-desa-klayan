<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;

class AdminSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        User::Create([
            'name' => 'Admin Lapor Pak',
            'email'=> 'admin@laporpak.com',
            'password' => bcrypt('password')
            
        ])->assignRole('admin');
    }
}
