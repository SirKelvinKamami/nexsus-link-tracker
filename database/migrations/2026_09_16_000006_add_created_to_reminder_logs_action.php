<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /** Action values reminder_logs must accept after this migration. */
    private const ACTIONS = [
        'sent', 'delivered', 'clicked', 'dismissed',
        'snoozed', 'completed', 'failed', 'created',
    ];

    /** Action values the column had before 'created' was introduced. */
    private const LEGACY_ACTIONS = [
        'sent', 'delivered', 'clicked', 'dismissed',
        'snoozed', 'completed', 'failed',
    ];

    public function up(): void
    {
        $this->applyActions(self::ACTIONS);
    }

    public function down(): void
    {
        $this->applyActions(self::LEGACY_ACTIONS);
    }

    /**
     * Rewrite the reminder_logs.action allow-list for the current driver.
     *
     * Every branch uses raw DDL on purpose. The previous implementation called
     * $table->enum('action', ...)->change() for the non-sqlite case, which
     * routes through Doctrine DBAL. Doctrine has no "enum" type registered, so
     * introspecting the column threw "Unknown column type \"enum\" requested"
     * and the migration died -- on PRODUCTION (pgsql), not just MySQL. Because
     * docker/entrypoint.sh runs `php artisan migrate --force || true`, that
     * failure was swallowed and every LATER migration silently never ran,
     * leaving the deployed database permanently half-migrated.
     */
    private function applyActions(array $actions): void
    {
        if (! Schema::hasTable('reminder_logs')) {
            return;
        }

        $driver = DB::connection()->getDriverName();
        $quoted = implode(', ', array_map(fn ($a) => "'" . $a . "'", $actions));

        if ($driver === 'pgsql') {
            // Laravel models enum() as varchar + CHECK on Postgres, so the
            // allow-list is swapped by replacing the constraint.
            DB::statement('ALTER TABLE reminder_logs DROP CONSTRAINT IF EXISTS reminder_logs_action_check');
            DB::statement("ALTER TABLE reminder_logs ADD CONSTRAINT reminder_logs_action_check CHECK (action::text = ANY (ARRAY[$quoted]::text[]))");

            return;
        }

        if ($driver === 'sqlite') {
            // SQLite cannot ALTER a CHECK constraint; the table is rebuilt.
            // Column list is explicit (never SELECT *) so the copy does not
            // depend on column ordering.
            DB::statement("CREATE TABLE reminder_logs_new (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                reminder_id INTEGER NOT NULL,
                action TEXT NOT NULL CHECK (action IN ($quoted)),
                metadata TEXT NULL,
                created_at TIMESTAMP NULL,
                updated_at TIMESTAMP NULL,
                FOREIGN KEY (reminder_id) REFERENCES reminders(id) ON DELETE CASCADE
            )");
            DB::statement('INSERT INTO reminder_logs_new (id, reminder_id, action, metadata, created_at, updated_at)
                SELECT id, reminder_id, action, metadata, created_at, updated_at FROM reminder_logs');
            DB::statement('DROP TABLE reminder_logs');
            DB::statement('ALTER TABLE reminder_logs_new RENAME TO reminder_logs');
            DB::statement('CREATE INDEX reminder_logs_reminder_id_index ON reminder_logs (reminder_id)');

            return;
        }

        if ($driver === 'mysql' || $driver === 'mariadb') {
            DB::statement("ALTER TABLE reminder_logs MODIFY COLUMN action ENUM($quoted) NOT NULL");
        }

        // Any other driver: leave the column as-is rather than guess at DDL.
    }
};
