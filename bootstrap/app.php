<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->prepend(\App\Http\Middleware\LimitDatabaseConcurrency::class);
        $middleware->append(\App\Http\Middleware\RecordActivity::class);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->render(function (\PDOException $error, \Illuminate\Http\Request $request) {
            $code = (int) ($error->errorInfo[1] ?? 0);
            if (in_array($code, [1040, 1203, 1205, 1213, 1226, 2002, 2003, 2006, 2013], true)) {
                return \App\Support\BusyResponse::make($request);
            }
        });
    })->create();
