<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('event_guest_parties', function (Blueprint $table): void {
            $table->string('table_name', 120)->nullable()->after('phone');
        });
    }

    public function down(): void
    {
        Schema::table('event_guest_parties', function (Blueprint $table): void {
            $table->dropColumn('table_name');
        });
    }
};
