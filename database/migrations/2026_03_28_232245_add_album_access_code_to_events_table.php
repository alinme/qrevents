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
        Schema::table('events', function (Blueprint $table) {
            $table->string('album_access_code', 4)->nullable()->after('share_token')->unique();
        });

        DB::table('events')
            ->select(['id'])
            ->orderBy('id')
            ->chunkById(200, function ($events): void {
                foreach ($events as $event) {
                    DB::table('events')
                        ->where('id', $event->id)
                        ->update([
                            'album_access_code' => $this->generateAlbumAccessCode(),
                        ]);
                }
            });
    }

    public function down(): void
    {
        Schema::table('events', function (Blueprint $table) {
            $table->dropUnique(['album_access_code']);
            $table->dropColumn('album_access_code');
        });
    }

    private function generateAlbumAccessCode(): string
    {
        $characters = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';

        do {
            $code = '';

            for ($index = 0; $index < 4; $index += 1) {
                $code .= $characters[random_int(0, strlen($characters) - 1)];
            }
        } while (
            DB::table('events')
                ->where('album_access_code', Str::upper($code))
                ->exists()
        );

        return Str::upper($code);
    }
};
