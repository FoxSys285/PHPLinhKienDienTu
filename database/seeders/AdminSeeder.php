<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class AdminSeeder extends Seeder
{
    public function run(): void
    {
        $email = config('admin.email');
        $password = config('admin.password');

        if (! is_string($email) || trim($email) === '' || ! is_string($password) || strlen($password) < 12) {
            return;
        }

        $admin = User::query()->firstOrNew(['email' => $email]);
        $admin->forceFill([
            'name' => config('admin.name', 'Quản trị viên'),
            'email' => $email,
            'password' => Hash::make($password),
            'role' => 'Admin',
            'is_blocked' => false,
        ])->save();
    }
}
