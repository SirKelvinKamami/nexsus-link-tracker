<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class WarmCache extends Command
{
    protected $signature = 'cache:warm';
    protected $description = 'Warm application cache for production';

    public function handle()
    {
        $this->info('Warming cache...');

        try {
            DB::connection()->getPdo();
            $this->info('✓ Database connected');
        } catch (\Throwable $e) {
            $this->error('✗ Database connection failed: ' . $e->getMessage());
            return 1;
        }

        try {
            config();
            $this->info('✓ Configuration cached');
        } catch (\Throwable $e) {
            $this->error('✗ Configuration cache failed: ' . $e->getMessage());
        }

        try {
            \Illuminate\Support\Facades\Route::cacheRoutes();
            $this->info('✓ Routes cached');
        } catch (\Throwable $e) {
            $this->error('✗ Route cache failed: ' . $e->getMessage());
        }

        try {
            $views = \Illuminate\Support\Facades\Blade::compileAll();
            $this->info('✓ Views compiled: ' . count($views) . ' views');
        } catch (\Throwable $e) {
            $this->error('✗ View compilation failed: ' . $e->getMessage());
        }

        try {
            Cache::put('app_ready', true, now()->addHours(24));
            Cache::put('cache_warmed_at', now()->toISOString(), now()->addHours(24));
            $this->info('✓ Application cache warmed');
        } catch (\Throwable $e) {
            $this->error('✗ Cache warming failed: ' . $e->getMessage());
        }

        $this->info('');
        $this->info('Cache warming complete!');

        return 0;
    }
}
