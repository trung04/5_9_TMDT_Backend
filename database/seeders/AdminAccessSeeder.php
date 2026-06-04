<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class AdminAccessSeeder extends Seeder
{
    public function run(): void
    {
        DB::transaction(function (): void {
            $this->seedBootstrapAdmin();
        });
    }

    private function seedBootstrapAdmin(): User
    {
        $config = config('admin_access.super_admin');
        $email = Str::lower(trim((string) ($config['email'] ?? 'admin@shop.local')));
        $phone = trim((string) ($config['phone'] ?? '0900000001'));

        $admin = User::query()
            ->where('email', $email)
            ->orWhere('phone', $phone)
            ->first() ?? new User();

        $admin->forceFill([
            'full_name' => trim((string) ($config['name'] ?? 'Quản trị viên hệ thống')) ?: 'Quản trị viên hệ thống',
            'email' => $email,
            'phone' => $phone,
            'password_hash' => Hash::make((string) ($config['password'] ?? 'password123')),
            'role' => User::ROLE_ADMIN,
            'admin_role_id' => null,
            'created_by_admin_id' => null,
            'is_active' => true,
            'is_deleted' => false,
        ])->save();

        return $admin->refresh();
    }
}
