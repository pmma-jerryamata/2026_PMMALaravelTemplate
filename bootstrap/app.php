<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware()
    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->render(function (\Illuminate\Http\Exceptions\ThrottleRequestsException $error, \Illuminate\Http\Request $request) {
            $seconds = (int) ($error->getHeaders()['Retry-After'] ?? 60);
            $message = "Too many attempts. Please try again in {$seconds} seconds.";

            if ($request->routeIs('contact.send') && ! $request->expectsJson()) {
                return response()->view('registrar.contact', ['contactCooldown' => $message], 429, $error->getHeaders());
            }

            return $request->expectsJson()
                ? response()->json(['message' => $message, 'retry_after' => $seconds], 429, $error->getHeaders())
                : response()->view('errors.429', compact('message', 'seconds'), 429, $error->getHeaders());
        });
        $exceptions->render(function (\PDOException $error, \Illuminate\Http\Request $request) {
            $code = (int) ($error->errorInfo[1] ?? 0);
            if (in_array($code, [1040, 1203, 1205, 1213, 1226, 2002, 2003, 2006, 2013], true)) {
                return \App\Support\BusyResponse::make($request);
            }
        });
    })->create();
