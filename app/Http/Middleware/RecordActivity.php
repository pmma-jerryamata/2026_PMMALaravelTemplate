<?php

namespace App\Http\Middleware;

use App\Models\ActivityLog;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class RecordActivity
{
    public function handle(Request $request, Closure $next): Response
    {
        return $next($request);
    }

    public function terminate(Request $request, Response $response): void
    {
        try {
            $this->record($request, $response);
        } catch (\Throwable $error) {
            // Authentication may itself be unavailable when the database is exhausted.
            \Illuminate\Support\Facades\Log::error('Activity recording unavailable', ['exception_type' => $error::class]);
        }
    }

    private function record(Request $request, Response $response): void
    {
        $route = $request->route();
        $name = $route?->getName();
        // Exclude clock polling and static files, while retaining every application operation.
        if (!$name || $name === 'time' || $name === 'up' || str_starts_with($name, 'storage.')) return;
        $user = $request->user();
        $actor = $request->attributes->get('audit_actor', $user ? ['id' => $user->id, 'name' => $user->name, 'login' => $user->username ?: $user->email] : null);
        $outcome = $response->getStatusCode() >= 400 ? 'failed' : 'success';
        if ($response instanceof \Symfony\Component\HttpFoundation\RedirectResponse && $name !== 'logout' && str_ends_with(parse_url($response->getTargetUrl(), PHP_URL_PATH) ?? '', '/login')) $outcome = 'failed';
        if ($response->isRedirection() && !$request->isMethod('GET') && $request->hasSession() && $request->session()->has('errors') && !$request->attributes->get('audit_success')) $outcome = 'failed';
        if ($name === 'login.submit') $outcome = $request->attributes->get('audit_success') ? 'success' : 'failed';
        $operation = match ($name) {
            'login.submit' => 'auth.login', 'logout' => 'auth.logout',
            'tickets.store', 'dashboard.tickets.store' => 'ticket.create',
            'dashboard.tickets.update' => 'ticket.update',
            'dashboard.tickets.destroy' => 'ticket.delete',
            'dashboard.tickets.action' => 'ticket.action',
            'track' => $request->isMethod('POST') ? 'ticket.track' : 'page.track',
            'dashboard.settings.save' => 'settings.'.$route->parameter('type').($route->parameter('id') ? '.update' : '.create'),
            'dashboard.settings.general' => 'settings.general.update',
            'dashboard.settings.profile' => 'account.profile.update',
            default => $name.'.view',
        };
        $details = ['route' => $name];
        // Only explicitly approved metadata is recorded; never serialize request bodies.
        foreach (['ticket', 'technician', 'id', 'type', 'slug'] as $key) {
            $value = $route->parameter($key);
            if ($value instanceof \Illuminate\Database\Eloquent\Model) $value = $value->getKey();
            if (is_scalar($value)) $details[$key] = mb_substr((string) $value, 0, 120);
        }
        if ($request->attributes->has('audit_reference')) $details['reference'] = $request->attributes->get('audit_reference');
        if ($request->attributes->has('audit_resource')) $details['resource'] = $request->attributes->get('audit_resource');
        if ($name === 'login.submit' && !$actor) {
            $login = $request->input('email');
            if (is_string($login)) $details['attempted_login'] = mb_substr($login, 0, 254);
        }
        if (!$request->isMethod('GET')) {
            $allowed = ['name','email','department_id','category','subject','description','priority','status','technician_id','code','is_active','user_access','username','phone','position','specialization','bio','photo','title','slug','icon','items','app_name','portal_subtitle','support_tagline','footer_credit'];
            $details['submitted_fields'] = array_values(array_intersect($allowed, array_keys($request->all())));
        }
        try {
            ActivityLog::create([
                'user_id' => $actor['id'] ?? null, 'actor_name' => mb_substr($actor['name'] ?? 'Guest / Requestee', 0, 255),
                'actor_login' => isset($actor['login']) ? mb_substr($actor['login'], 0, 255) : null,
                'ip_address' => $request->ip(), 'operation' => $operation, 'outcome' => $outcome,
                'method' => $request->method(), 'path' => mb_substr('/'.$request->path(), 0, 500),
                'status_code' => $response->getStatusCode(), 'details' => $details,
            ]);
        } catch (\Throwable $error) {
            // A logging failure must not undo an already completed support request.
            \Illuminate\Support\Facades\Log::error('Activity log write failed', ['route' => $name, 'exception_type' => $error::class]);
        }
    }
}
