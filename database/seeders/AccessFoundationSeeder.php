<?php

namespace Database\Seeders;

use App\Models\Department;
use App\Models\Role;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use RuntimeException;

class AccessFoundationSeeder extends Seeder
{
    public function run(): void
    {
        $superAdminRole = Role::updateOrCreate(
            ['slug' => 'super-admin'],
            [
                'name' => 'Super Admin',
                'description' => 'Memiliki akses penuh terhadap seluruh fitur dan konfigurasi sistem BAST.',
                'is_active' => true,
            ],
        );

        Role::updateOrCreate(
            ['slug' => 'admin'],
            [
                'name' => 'Admin',
                'description' => 'Mengelola operasional berita acara, arsip, pengguna, dan data master.',
                'is_active' => true,
            ],
        );

        Role::updateOrCreate(
            ['slug' => 'staff'],
            [
                'name' => 'Staff',
                'description' => 'Membuat dan mengelola berita acara sesuai hak akses yang diberikan.',
                'is_active' => true,
            ],
        );

        $systemDepartment = Department::updateOrCreate(
            ['code' => 'SYS'],
            [
                'name' => 'Administrasi Sistem',
                'description' => 'Unit default untuk akun administrator pada lingkungan pengembangan.',
                'is_active' => true,
            ],
        );

        $email = config('bast.super_admin.email');
        $password = config('bast.super_admin.password');

        if (! is_string($email) || $email === '') {
            throw new RuntimeException(
                'BAST super admin email configuration is invalid.',
            );
        }

        if (! is_string($password) || $password === '') {
            throw new RuntimeException(
                'BAST super admin password configuration is invalid.',
            );
        }

        User::updateOrCreate(
            [
                'email' => $email,
            ],
            [
                'name' => 'Super Admin BAST',
                'nip' => null,
                'email_verified_at' => now(),
                'position' => 'Administrator Sistem',
                'phone' => null,
                'status' => 'active',
                'role_id' => $superAdminRole->id,
                'department_id' => $systemDepartment->id,
                'password' => Hash::make($password),
            ],
        );
    }
}
