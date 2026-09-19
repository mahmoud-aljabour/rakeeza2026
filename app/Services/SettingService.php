<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Setting;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Cache;

final class SettingService
{
    private const CACHE_KEY = 'rakeeza.settings';

    /**
     * @var list<string>
     */
    private const CONTACT_KEYS = ['phone', 'email', 'whatsapp'];

    /**
     * @return Collection<int, Setting>
     */
    public function list(): Collection
    {
        return Setting::query()->orderBy('key')->get();
    }

    public function get(string $key, ?string $default = null): ?string
    {
        $settings = $this->allAsArray();

        return $settings[$key] ?? $default;
    }

    /**
     * @return array<string, string|null>
     */
    public function allAsArray(): array
    {
        return Cache::rememberForever(self::CACHE_KEY, function (): array {
            return Setting::query()
                ->pluck('value', 'key')
                ->all();
        });
    }

    public function set(string $key, ?string $value): Setting
    {
        $setting = Setting::query()->updateOrCreate(
            ['key' => $key],
            ['value' => $value],
        );

        Cache::forget(self::CACHE_KEY);

        return $setting;
    }

    /**
     * @param  array<string, string|null>  $settings
     */
    public function setMany(array $settings): void
    {
        $allowed = array_keys(config('rakeeza.defaults'));

        foreach ($settings as $key => $value) {
            if (! in_array($key, $allowed, true)) {
                continue;
            }

            Setting::query()->updateOrCreate(
                ['key' => $key],
                ['value' => $value],
            );
        }

        Cache::forget(self::CACHE_KEY);
    }

    /**
     * @return array<string, string>
     */
    public function publicSite(): array
    {
        $defaults = config('rakeeza.defaults');
        $saved = $this->allAsArray();
        $site = [];
        $useEnglishCopy = app()->getLocale() === 'en';

        foreach ($defaults as $key => $default) {
            if ($useEnglishCopy && ! in_array($key, self::CONTACT_KEYS, true)) {
                $translated = trans('content.'.$key);
                $site[$key] = is_string($translated) && $translated !== 'content.'.$key
                    ? $translated
                    : $default;

                continue;
            }

            $value = $saved[$key] ?? $default;
            $site[$key] = is_string($value) && $value !== '' ? $value : $default;
        }

        return $site;
    }
}
