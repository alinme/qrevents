<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('event_guest_parties', function (Blueprint $table): void {
            $table->foreignId('event_table_id')
                ->nullable()
                ->after('table_name')
                ->constrained('event_tables')
                ->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('event_guest_parties', function (Blueprint $table): void {
            $table->dropConstrainedForeignId('event_table_id');
        });
    }
};
