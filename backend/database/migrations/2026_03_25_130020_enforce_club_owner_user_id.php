<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        $unownedCount = DB::table('clubs')->whereNull('owner_user_id')->count();

        if ($unownedCount > 0) {
            throw new \RuntimeException("Cannot enforce NOT NULL on clubs.owner_user_id, {$unownedCount} clubs are still unowned.");
        }

        DB::statement('ALTER TABLE clubs MODIFY owner_user_id BIGINT UNSIGNED NOT NULL');
    }

    public function down(): void
    {
        DB::statement('ALTER TABLE clubs MODIFY owner_user_id BIGINT UNSIGNED NULL');
    }
};
