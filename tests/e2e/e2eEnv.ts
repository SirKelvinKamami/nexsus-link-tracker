import { join } from 'node:path';

/**
 * Single source of truth for the environment the E2E server and setup run
 * with. Deliberately overrides any developer .env values so the suite is
 * deterministic: its own sqlite file, database drivers, open registration.
 */
export const e2eEnv: Record<string, string> = {
    DB_CONNECTION: 'sqlite',
    DB_DATABASE: join(__dirname, '..', '..', 'database', 'e2e.sqlite'),
    SESSION_DRIVER: 'database',
    CACHE_DRIVER: 'database',
    QUEUE_CONNECTION: 'database',
    ALLOW_REGISTRATION: 'true',
    MANUAL_USER_VERIFICATION: 'false',
    APP_ENV: 'testing',
    APP_DEBUG: 'false',
    LOG_CHANNEL: 'stderr',
};
