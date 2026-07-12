<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class AdminSeeder extends Seeder
{
    public function run(): void
    {
        User::create([
            'name' => 'Administrator',
            'email' => 'admin@sekolah.com',
            'password' => Hash::make('admin123'),
            'role' => 'admin',
        ]);

        User::create([
            'name' => 'Staf Tata Usaha',
            'email' => 'staf@sekolah.com',
            'password' => Hash::make('staf123'),
            'role' => 'staff',
        ]);

        $this->command->info('Admin account: admin@sekolah.com / admin123');
        $this->command->info('Staff account:  staf@sekolah.com / staf123');
    }
}
