<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ServiceCategory extends Model
{
    protected static function booted(): void
    {
        static::saved(fn () => \Illuminate\Support\Facades\Cache::forget('helpdesk.services'));
        static::deleted(fn () => \Illuminate\Support\Facades\Cache::forget('helpdesk.services'));
    }
    private static function allServices(): array
    {
        return \Illuminate\Support\Facades\Cache::remember('helpdesk.services', 60, fn () => static::orderBy('id')->get()->keyBy('slug')->toArray());
    }
    protected $fillable = ['slug', 'title', 'icon', 'description', 'items', 'is_active'];
    protected function casts(): array { return ['items' => 'array', 'is_active' => 'boolean']; }
    public static function catalog(): array
    {
        return array_filter(static::allServices(), fn ($service) => $service['is_active']);
    }
    public static function label(string $slug): string
    {
        return static::allServices()[$slug]['title'] ?? $slug;
    }
}
