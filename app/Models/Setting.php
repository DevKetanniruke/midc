<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/** Key/value site settings (name, logo, company details) edited on the Settings page. */
class Setting extends Model
{
    public const KEYS = ['site_name', 'company_name', 'tagline', 'address', 'phone', 'email', 'website', 'logo', 'favicon'];

    protected $fillable = ['key', 'value'];

    /** Per-request copy so the many views that show the site name do not each hit the database. */
    private static ?array $loaded = null;

    /** @return array<string, ?string> every known key => stored value */
    public static function values(): array
    {
        if (self::$loaded === null) {
            try {
                self::$loaded = array_merge(array_fill_keys(self::KEYS, null), static::pluck('value', 'key')->all());
            } catch (\Throwable) {
                return array_fill_keys(self::KEYS, null); // table not migrated yet
            }
        }

        return self::$loaded;
    }

    /** @param array<string, ?string> $data */
    public static function put(array $data): void
    {
        foreach (array_intersect_key($data, array_flip(self::KEYS)) as $key => $value) {
            static::updateOrCreate(['key' => $key], ['value' => $value]);
        }

        self::$loaded = null;
    }

    /** What the views show: stored values with sensible fallbacks, plus ready-to-use URLs. */
    public static function site(): object
    {
        $v = self::values();
        $name = filled($v['site_name']) ? $v['site_name'] : config('app.name');

        return (object) [
            'name' => $name,
            'company' => filled($v['company_name']) ? $v['company_name'] : $name,
            'company_set' => filled($v['company_name']),
            'tagline' => $v['tagline'],
            'address' => $v['address'],
            'phone' => $v['phone'],
            'email' => $v['email'],
            'website' => $v['website'],
            'logo' => filled($v['logo']) ? asset($v['logo']) : null,
            'favicon' => filled($v['favicon']) ? asset($v['favicon']) : asset('favicon.ico'),
            /** One printable line: "address · phone · email · website" */
            'contact' => implode(' · ', array_filter([$v['address'], $v['phone'], $v['email'], $v['website']], 'filled')),
        ];
    }
}
