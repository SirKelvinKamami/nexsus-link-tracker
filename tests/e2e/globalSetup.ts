import { execSync } from 'node:child_process';
import { existsSync, rmSync } from 'node:fs';
import { join } from 'node:path';
import { e2eEnv } from './e2eEnv';

/**
 * Runs once before all tests: rebuilds the throwaway sqlite database so the
 * suite starts from a clean, fully seeded state on every run.
 */
export default function globalSetup() {
    const repoRoot = join(__dirname, '..', '..');
    const dbPath = e2eEnv.DB_DATABASE;

    if (existsSync(dbPath)) rmSync(dbPath);
    // Artisan expects the sqlite file to exist before it opens the connection.
    execSync('php -r "touch(\'database/e2e.sqlite\');"', { cwd: repoRoot, stdio: 'inherit' });

    const env = { ...process.env, ...e2eEnv };
    execSync('php artisan migrate:fresh --force', { cwd: repoRoot, env, stdio: 'inherit' });
    execSync('php artisan db:seed --force', { cwd: repoRoot, env, stdio: 'inherit' });
}
