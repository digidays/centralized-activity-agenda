<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasColumn('clubs', 'owner_user_id')) {
            Schema::table('clubs', function (Blueprint $table) {
                $table->unsignedBigInteger('owner_user_id')->nullable()->after('type');
            });
        }

        if (! $this->indexExists('clubs', 'clubs_owner_user_id_index')) {
            Schema::table('clubs', function (Blueprint $table) {
                $table->index('owner_user_id', 'clubs_owner_user_id_index');
            });
        }

        if (! $this->foreignKeyExists('clubs', 'clubs_owner_user_id_foreign')) {
            Schema::table('clubs', function (Blueprint $table) {
                $table->foreign('owner_user_id', 'clubs_owner_user_id_foreign')
                    ->references('id')
                    ->on('users');
            });
        }
    }

    public function down(): void
    {
        if ($this->foreignKeyExists('clubs', 'clubs_owner_user_id_foreign')) {
            Schema::table('clubs', function (Blueprint $table) {
                $table->dropForeign('clubs_owner_user_id_foreign');
            });
        }

        if (Schema::hasColumn('clubs', 'owner_user_id')) {
            Schema::table('clubs', function (Blueprint $table) {
                $table->dropIndex('clubs_owner_user_id_index');
                $table->dropColumn('owner_user_id');
            });
        }
    }

    private function foreignKeyExists(string $tableName, string $constraintName): bool
    {
        $database = DB::getDatabaseName();
        $result = DB::selectOne(
            'SELECT 1 FROM information_schema.TABLE_CONSTRAINTS WHERE CONSTRAINT_SCHEMA = ? AND TABLE_NAME = ? AND CONSTRAINT_NAME = ? AND CONSTRAINT_TYPE = ? LIMIT 1',
            [$database, $tableName, $constraintName, 'FOREIGN KEY']
        );

        return $result !== null;
    }

    private function indexExists(string $tableName, string $indexName): bool
    {
        $database = DB::getDatabaseName();
        $result = DB::selectOne(
            'SELECT 1 FROM information_schema.STATISTICS WHERE TABLE_SCHEMA = ? AND TABLE_NAME = ? AND INDEX_NAME = ? LIMIT 1',
            [$database, $tableName, $indexName]
        );

        return $result !== null;
    }
};
