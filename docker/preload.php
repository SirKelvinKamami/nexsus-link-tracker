<?php

/**
 * opcache preload script (opcache.preload in opcache-prod.ini).
 *
 * Compiles the entire Illuminate framework into shared memory while the FPM
 * master starts, so the first request after a cold start skips the per-class
 * compile of ~1.5k framework files. Because the entrypoint opens the web
 * port before supervisord starts FPM, this compile cost lands in the
 * parallel boot window instead of the first user request.
 *
 * Guard rails:
 *  - CLI invocations (artisan, boot-tasks) run WITHOUT preload
 *    (opcache.enable_cli=0), so this file is never parsed there.
 *  - The classmap is generated at BUILD time (Dockerfile) so the runtime
 *    startup only reads a file — no composer scan.
 *  - Only Illuminate/Symfony classes are preloaded: preloading APP classes
 *    that a long-lived worker could redeclare after a code change is the
 *    classic "cannot redeclare class" crash; framework classes in an
 *    immutable image are safe.
 *  - Any preload failure must not stop FPM from serving: exceptions are
 *    caught and reported to stderr (preload silently degrades to normal
 *    per-class compilation).
 */

if (PHP_SAPI === 'cli' || !function_exists('opcache_compile_file')) {
    return;
}

try {
    $map = __DIR__ . '/../bootstrap/cache/preload-classmap.php';
    if (!is_file($map)) {
        error_log('opcache preload: classmap missing, skipping (run the build step)');
        return;
    }

    $classes = require $map;
    $loaded = 0;
    $failed = 0;

    foreach ($classes as $class => $file) {
        if (!is_string($class) || !is_string($file) || class_exists($class, false)) {
            continue;
        }
        if (opcache_compile_file($file)) {
            $loaded++;
        } else {
            $failed++;
        }
    }

    error_log("opcache preload: {$loaded} classes compiled into shared memory"
        . ($failed > 0 ? ", {$failed} failed" : '') . '.');
} catch (Throwable $e) {
    error_log('opcache preload failed (serving without preload): ' . $e->getMessage());
}
