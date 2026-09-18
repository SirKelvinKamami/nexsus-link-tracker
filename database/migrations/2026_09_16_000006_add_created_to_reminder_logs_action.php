<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // SQLite doesn't support ALTER TABLE for CHECK constraints.
        // Recreate the table with the expanded enum values.
        $connection = DB::connection();
        $driver = $connection->getDriverName();

        if ($driver === 'sqlite') {
            DB::statement("CREATE TABLE reminder_logs_new (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                reminder_id INTEGER NOT NULL,
                action TEXT NOT NULL CHECK (action IN ('sent', 'delivered', 'clicked', 'dismissed', 'snoozed', 'completed', 'failed', 'created')),
                metadata TEXT NULL,
                created_at TIMESTAMP NULL,
                updated_at TIMESTAMP NULL,
                FOREIGN KEY (reminder_id) REFERENCES reminders(id) ON DELETE CASCADE
            )");
            DB::statement("INSERT INTO reminder_logs_new SELECT * FROM reminder_logs");
            DB::statement("DROP TABLE reminder_logs");
            DB::statement("ALTER TABLE reminder_logs_new RENAME TO reminder_logs");
            DB::statement("CREATE INDEX reminder_logs_reminder_id_index ON reminder_logs (reminder_id)");
        } else {
            // MySQL: use standard change
            Schema::table('reminder_logs', function (Blueprint $table) {
                $table->enum('action', ['sent', 'delivered', 'clicked', 'dismissed', 'snoozed', 'completed', 'failed', 'created'])->change();
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        $connection = DB::connection();
        $driver = $connection->getDriverName();

        if ($driver === 'sqlite') {
            DB::statement("CREATE TABLE reminder_logs_new (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                reminder_id INTEGER NOT NULL,
                action TEXT NOT NULL CHECK (action IN ('sent', 'delivered', 'clicked', 'dismissed', 'snoozed', 'completed', 'failed')),
                metadata TEXT NULL,
                created_at TIMESTAMP NULL,
                updated_at TIMESTAMP NULL,
                FOREIGN KEY (reminder_id) REFERENCES reminders(id) ON DELETE CASCADE
            )");
            DB::statement("INSERT INTO reminder_logs_new SELECT * FROM reminder_logs");
            DB::statement("DROP TABLE reminder_logs");
            DB::statement("ALTER TABLE reminder_logs_new RENAME TO reminder_logs");
            DB::statement("CREATE INDEX reminder_logs_reminder_id_index ON reminder_logs (reminder_id)");
        } else {
            Schema::table('reminder_logs', function (Blueprint $table) {
                $table->enum('action', ['sent', 'delivered', 'clicked', 'dismissed', 'snoozed', 'completed', 'failed'])->change();
            });
        }
    }
};
