<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class SuperUserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        /** @var array<int, string> $superAdminEmails */
        $superAdminEmails = config('app.super_admin_emails', []);
        $superAdminEmail = $superAdminEmails[0] ?? null;

        if (! is_string($superAdminEmail) || trim($superAdminEmail) === '') {
            return;
        }

        User::query()->updateOrCreate(
            [
                'email' => $superAdminEmail,
            ],
            [
                'name' => (string) config('app.super_admin_seed_name', 'WV Events Admin'),
                'password' => Hash::make((string) config('app.super_admin_seed_password', 'ChangeThisBeforeProduction123!')),
                'account_type' => User::ACCOUNT_TYPE_SUPER_ADMIN,
                'email_verified_at' => now(),
            ],
        );
    }
}
