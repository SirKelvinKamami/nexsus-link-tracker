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
 *   npm ci && npm run production   (assets in public/)
 */
export default defineConfig({
    globalSetup: './globalSetup.ts',
    testDir: '.',
    timeout: 60_000,
    retries: process.env.CI ? 1 : 0,
    workers: 1,
    reporter: process.env.CI ? 'github' : 'list',
    use: {
        baseURL: 'http://127.0.0.1:8010',
        headless: true,
        screenshot: 'only-on-failure',
        trace: 'retain-on-failure',
    },
    webServer: {
        command: 'php -S 127.0.0.1:8010 ../../index.php',
        url: 'http://127.0.0.1:8010/api/v1/health/live',
        timeout: 120_000,
        cwd: __dirname,
        reuseExistingServer: false,
        env: { ...process.env, ...e2eEnv },
    },
});
