<?php

use App\Models\User;
use Illuminate\Support\Facades\Hash;

test('super user seeder creates or updates the configured account', function () {
    config([
        'app.super_admin_emails' => ['events@wvdev.org'],
        'app.super_admin_seed_name' => 'WV Events Admin',
        'app.super_admin_seed_password' => 'SeedPassword123!',
    ]);

    $this->artisan('db:seed', [
        '--class' => 'Database\\Seeders\\SuperUserSeeder',
        '--no-interaction' => true,
    ])->assertSuccessful();

    $user = User::query()->where('email', 'events@wvdev.org')->first();

    expect($user)->not->toBeNull()
        ->and($user?->name)->toBe('WV Events Admin')
        ->and($user?->account_type)->toBe(User::ACCOUNT_TYPE_SUPER_ADMIN)
        ->and(Hash::check('SeedPassword123!', (string) $user?->password))->toBeTrue();
});
