<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        $this->dropViews();

        DB::statement(<<<'SQL'
            CREATE VIEW vw_events_all AS
            SELECT
                event.id,
                event.name,
                event.organizer,
                event.start_date,
                event.description,
                event.location,
                event.url,
                event.app_id,
                COALESCE(u.name, c.name) AS app_name,
                c.source AS app_source,
                c.type AS app_type,
                c.owner_user_id,
                event.img,
                event.is_cancelled,
                event.created_at,
                event.updated_at
            FROM events event
            JOIN clubs c ON c.id = event.app_id
            LEFT JOIN users u ON u.id = c.owner_user_id
        SQL);

        DB::statement(<<<'SQL'
            CREATE VIEW vw_events_upcoming AS
            SELECT *
            FROM vw_events_all
            WHERE start_date >= CURRENT_DATE
        SQL);

        DB::statement(<<<'SQL'
            CREATE VIEW vw_events_past AS
            SELECT *
            FROM vw_events_all
            WHERE start_date < CURRENT_DATE
        SQL);

        DB::statement(<<<'SQL'
            CREATE VIEW vw_events_today AS
            SELECT *
            FROM vw_events_all
            WHERE start_date = CURRENT_DATE
        SQL);

        DB::statement(<<<'SQL'
            CREATE VIEW vw_events_undated AS
            SELECT *
            FROM vw_events_all
            WHERE start_date IS NULL
        SQL);

        DB::statement(<<<'SQL'
            CREATE VIEW vw_event_details AS
            SELECT
                event.id,
                event.name,
                event.organizer,
                event.start_date,
                event.description,
                event.location,
                event.url,
                event.app_id,
                COALESCE(u.name, c.name) AS app_name,
                c.source AS app_source,
                c.type AS app_type,
                c.owner_user_id,
                event.img,
                event.is_cancelled,
                event.created_at,
                event.updated_at,
                CASE
                    WHEN COUNT(t.id) = 0 THEN JSON_ARRAY()
                    ELSE JSON_ARRAYAGG(JSON_OBJECT('id', t.id, 'slug', t.slug))
                END AS tags
            FROM events event
            JOIN clubs c ON c.id = event.app_id
            LEFT JOIN users u ON u.id = c.owner_user_id
            LEFT JOIN event_tag et ON et.event_id = event.id
            LEFT JOIN tags t ON t.id = et.tag_id
            GROUP BY
                event.id,
                event.name,
                event.organizer,
                event.start_date,
                event.description,
                event.location,
                event.url,
                event.app_id,
                c.name,
                c.source,
                c.type,
                c.owner_user_id,
                u.name,
                event.img,
                event.is_cancelled,
                event.created_at,
                event.updated_at
        SQL);
    }

    public function down(): void
    {
        $this->dropViews();
    }

    private function dropViews(): void
    {
        DB::statement('DROP VIEW IF EXISTS vw_events_today');
        DB::statement('DROP VIEW IF EXISTS vw_events_undated');
        DB::statement('DROP VIEW IF EXISTS vw_events_upcoming');
        DB::statement('DROP VIEW IF EXISTS vw_events_past');
        DB::statement('DROP VIEW IF EXISTS vw_event_details');
        DB::statement('DROP VIEW IF EXISTS vw_events_all');
    }
};
