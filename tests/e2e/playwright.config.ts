import { defineConfig } from '@playwright/test';
import { e2eEnv } from './e2eEnv';

/**
 * Browser E2E for the full Nexsus user flow.
 *
 * The webServer block boots a dedicated Laravel server on port 8010 with the
 * throwaway sqlite database built by globalSetup.ts, so these tests never
 * touch a developer's running server (port 8000) or its data.
 *
 * Prerequisites (run from repo root):
 *   composer install
 *   (assets ship prebuilt at the repo root: assets/, css/, js/ - no build step)
 */
// E2E_BASE_URL switches the suite from "boot my own isolated server" to
// "test whatever is running at this URL" (used by the nightly live-probe
// workflow against production).
const liveBase = process.env.E2E_BASE_URL;

export default defineConfig({
    testDir: '.',
    timeout: 60_000,
    retries: process.env.CI ? 1 : 0,
    workers: 1,
    reporter: process.env.CI ? 'github' : 'list',
    use: {
        baseURL: liveBase ?? 'http://127.0.0.1:8010',
        headless: true,
        screenshot: 'only-on-failure',
        trace: 'retain-on-failure',
    },
    // In live mode there is no local server to boot or wait for.
    webServer: liveBase
        ? undefined
        : {
        // The suite is fully self-bootstrapping: Playwright starts the
        // webServer and waits for its health URL BEFORE running any global
        // setup, so the database must be built inside the server command.
        // (A separate globalSetup can never run: readiness blocks it, and on
        // a fresh checkout readiness fails forever because the sqlite file is
        // empty.) cd to the repo root so relative paths resolve; DB_DATABASE
        // is already absolute (see e2eEnv.ts).
        command:
            "cd ../.. && php -r \"touch('database/e2e.sqlite');\" && php artisan migrate:fresh --force && php artisan db:seed --force && php -S 127.0.0.1:8010 -t . server.php",
        url: 'http://127.0.0.1:8010/api/v1/health/live',
        timeout: 120_000,
        cwd: __dirname,
        reuseExistingServer: false,
        env: { ...process.env, ...e2eEnv },
    },
});
