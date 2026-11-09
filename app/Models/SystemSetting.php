<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SystemSetting extends Model
{
    protected static function booted(): void
    {
        static::saved(fn () => \Illuminate\Support\Facades\Cache::forget('helpdesk.branding'));
        static::deleted(fn () => \Illuminate\Support\Facades\Cache::forget('helpdesk.branding'));
    }
    public $timestamps = false;
    public $incrementing = false;
    protected $primaryKey = 'key';
    protected $keyType = 'string';
    protected $fillable = ['key', 'value'];

    public static function values(): array
    {
        return array_replace([
            'app_name' => config('app.name', 'My Application'), 'portal_subtitle' => 'Ticket Support Portal',
            'support_tagline' => 'Your campus support, in one place',
            'footer_credit' => 'Developed and Maintained by APP Information Technology Services Unit',
        ], \Illuminate\Support\Facades\Cache::remember('helpdesk.branding', 60, fn () => static::pluck('value', 'key')->all()));
    }
}
