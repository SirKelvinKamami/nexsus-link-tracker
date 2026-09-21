<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Rename littlelink_name -> handle and littlelink_description -> bio.
     *
     * Each rename gets its own Schema::table() call: SQLite rejects more than
     * one renameColumn/dropColumn per modification ("SQLite doesn't support
     * multiple calls to dropColumn / renameColumn in a single modification"),
     * which made the original single-closure version abort the whole migration
     * run on every sqlite install.
     *
     * Both directions are guarded on current column state so the migration is
     * a no-op when 2026_09_19_000001_add_handle_bio_compat already did the
     * rename (deploy order differs between fresh and previously-half-migrated
     * databases).
     */
    public function up(): void
    {
        $this->rename('littlelink_name', 'handle');
        $this->rename('littlelink_description', 'bio');
    }

    public function down(): void
    {
        $this->rename('handle', 'littlelink_name');
        $this->rename('bio', 'littlelink_description');
    }

    private function rename(string $from, string $to): void
    {
        if (! Schema::hasColumn('users', $from) || Schema::hasColumn('users', $to)) {
            return;
        }

        Schema::table('users', function (Blueprint $table) use ($from, $to) {
            $table->renameColumn($from, $to);
        });
    }
};
