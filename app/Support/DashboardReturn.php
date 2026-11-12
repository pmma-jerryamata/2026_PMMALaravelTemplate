<?php

namespace App\Support;

use Illuminate\Http\Request;

class DashboardReturn
{
    public static function remember(Request $request): void
    {
        $request->session()->put('helpdesk.return_to', $request->getRequestUri());
    }

    public static function url(string $fallback): string
    {
        $value = request()->input('_return_to', session('helpdesk.return_to'));
        if (! is_string($value) || preg_match('/[\\\\\x00-\x20]/', $value) || ! str_starts_with($value, '/') || str_starts_with($value, '//')) {
            return $fallback;
        }
        return in_array(parse_url($value, PHP_URL_PATH), ['/dashboard', '/dashboard/settings', '/dashboard/surveys', '/tech', '/support', '/admin/index'], true)
            ? $value : $fallback;
    }
}
