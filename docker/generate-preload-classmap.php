<?php

/**
 * Build-time helper (run by the Dockerfile's build stage, after composer
 * install): generates bootstrap/cache/preload-classmap.php — a
 * class => file map of the framework classes opcache should preload.
 *
 * Generated at build time (not runtime) so the FPM startup only reads a
 * file: scanning the vendor tree on every cold start would reintroduce the
 * latency we're removing.
 *
 * Only Illuminate/Symfony classes are preloaded: preloading APP classes
 * that a long-lived worker could redeclare after a code change is the
 * classic "cannot redeclare class" crash; framework classes in an
 * immutable image are safe. Traits/interfaces are skipped (opcache can
 * only compile classes).
 *
 * Usage: php docker/generate-preload-classmap.php
 */

if (PHP_SAPI !== 'cli') {
    exit("CLI only\n");
}

$base = dirname(__DIR__);
require $base . '/vendor/autoload.php';

$classMapPaths = require $base . '/vendor/composer/autoload_classmap.php';

$entries = [];
foreach ($classMapPaths as $class => $file) {
    if (!is_string($file) || !is_file($file)) {
        continue;
    }
    $norm = str_replace('\\', '/', $class);
    // Framework + Symfony only — see header.
    if (stripos($norm, 'Illuminate/') !== 0 && stripos($norm, 'Symfony/') !== 0) {
        continue;
    }
    $src = file_get_contents($file);
    // A class declaration (not trait/interface/enum) opcache can compile.
    if (preg_match('/\b(?:abstract\s+|final\s+)?class\s+' . preg_quote(basename(str_replace('\\', '/', $class)), '/') . '\b/', $src)) {
        $entries[$class] = $file;
    }
}

$out = $base . '/bootstrap/cache/preload-classmap.php';
@mkdir(dirname($out), 0775, true);
$bytes = file_put_contents(
    $out,
    "<?php\n\n// AUTO-GENERATED at build time by docker/generate-preload-classmap.php.\n// Do not edit: regenerate instead.\n\nreturn " . var_export($entries, true) . ";\n"
);

if ($bytes === false) {
    exit("failed to write {$out}\n");
}

echo 'preload classmap: ' . count($entries) . " framework classes -> {$out}\n";
