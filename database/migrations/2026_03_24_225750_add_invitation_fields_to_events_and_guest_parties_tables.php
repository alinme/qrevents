<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('events', function (Blueprint $table): void {
            $table->string('public_invitation_token', 80)->nullable()->after('share_token');
            $table->json('invitation_settings')->nullable()->after('branding');
        });

        DB::table('events')
            ->select(['id'])
            ->orderBy('id')
            ->lazy()
            ->each(function (object $event): void {
                DB::table('events')
                    ->where('id', $event->id)
                    ->update([
                        'public_invitation_token' => Str::lower((string) Str::uuid()),
                    ]);
            });

        Schema::table('events', function (Blueprint $table): void {
            $table->unique('public_invitation_token');
        });

        Schema::table('event_guest_parties', function (Blueprint $table): void {
            $table->text('guest_names')->nullable()->after('notes');
            $table->string('meal_preference', 24)->nullable()->after('gift_amount');
            $table->text('response_notes')->nullable()->after('meal_preference');
            $table->string('response_ip_address', 45)->nullable()->after('response_notes');
            $table->text('response_user_agent')->nullable()->after('response_ip_address');
        });
    }

    public function down(): void
    {
        Schema::table('event_guest_parties', function (Blueprint $table): void {
            $table->dropColumn([
                'guest_names',
                'meal_preference',
                'response_notes',
                'response_ip_address',
                'response_user_agent',
            ]);
        });

        Schema::table('events', function (Blueprint $table): void {
            $table->dropUnique(['public_invitation_token']);
            $table->dropColumn([
                'public_invitation_token',
                'invitation_settings',
            ]);
        });
    }
};
