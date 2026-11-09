<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class LimitDatabaseConcurrency
{
    public function handle(Request $request, Closure $next)
    {
        if (!config('capacity.enabled') || $request->is('up', 'time')) return $next($request);
        $directory = config('capacity.directory');
        if (!is_dir($directory) && !@mkdir($directory, 0775, true) && !is_dir($directory)) {
            return \App\Support\BusyResponse::make($request);
        }
        $lease = null;
        for ($i = 0; $i < max(1, config('capacity.requests')); $i++) {
            $handle = @fopen($directory.'/slot-'.$i, 'c');
            if ($handle && flock($handle, LOCK_EX | LOCK_NB)) { $lease = $handle; break; }
            if ($handle) fclose($handle);
        }
        if (!$lease) return \App\Support\BusyResponse::make($request);
        // Hold through session persistence and terminable audit logging, not just controller execution.
        // OS locks are released even if a PHP worker exits unexpectedly; no expiring leases to overlap.
        $release = static function () use ($lease) {
            if (is_resource($lease)) { flock($lease, LOCK_UN); fclose($lease); }
        };
        app()->terminating($release);
        register_shutdown_function($release);
        return $next($request);
    }
}
