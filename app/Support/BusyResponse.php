<?php

namespace App\Support;

use Illuminate\Http\Request;

class BusyResponse
{
    public static function make(Request $request)
    {
        $message = 'The helpdesk is busy. Please wait a few seconds and try again. Your submission has not been confirmed; keep your form open.';
        $headers = ['Retry-After' => '5', 'Cache-Control' => 'no-store'];
        // Standalone response: never query branding, sessions, or authentication during an outage.
        return $request->expectsJson()
            ? response()->json(['message' => $message], 503, $headers)
            : response('<!doctype html><html lang="en"><meta name="viewport" content="width=device-width,initial-scale=1"><title>Helpdesk Busy</title><body style="font-family:Arial;background:#0c2340;color:white;padding:8%;max-width:650px"><h1>Helpdesk Temporarily Busy</h1><p>'.$message.'</p><p>Use your browser’s Back button to return to your form. Check tracking if you already received a ticket number.</p></body></html>', 503, $headers);
    }
}
