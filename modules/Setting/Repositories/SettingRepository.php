<?php

namespace Modules\Setting\Repositories;

use ArrayAccess;
use Illuminate\Support\Collection;
use Modules\Setting\Entities\Setting;
use Modules\Setting\Support\SensitiveSetting;

class SettingRepository implements ArrayAccess
{
    /**
     * Collection of all settings.
     *
     * @var Collection
     */
    private $settings;

    /** @var array<string, mixed> */
    private array $sensitiveSettings = [];

    private bool $sensitiveSettingsLoaded = false;


    /**
     * Create a new repository instance.
     *
     * @param Collection $settings
     */
    public function __construct($settings)
    {
        $this->settings = $settings;
    }


    /**
     * Get all settings.
     *
     * @return array
     */
    public function all()
    {
        $this->loadSensitiveSettings();

        return array_merge($this->settings->all(), $this->sensitiveSettings);
    }


    /**
     * Determine if a setting is exists.
     *
     * @param string $key
     *
     * @return bool
     */
    public function offsetExists($key)
    {
        if (SensitiveSetting::isSensitive($key)) {
            $this->loadSensitiveSettings();

            return array_key_exists($key, $this->sensitiveSettings);
        }

        return $this->settings->has($key);
    }


    /**
     * Unset a setting by the given key.
     *
     * @param string $key
     *
     * @return Collection
     */
    public function offsetUnset($key)
    {
        if (SensitiveSetting::isSensitive($key)) {
            unset($this->sensitiveSettings[$key]);

            return $this->settings;
        }

        return $this->settings->forget($key);
    }


    /**
     * Get setting for the given key.
     *
     * @param string $key
     *
     * @return mixed
     */
    public function __get($key)
    {
        return $this->offsetGet($key);
    }


    /**
     * Set a key / value setting pair.
     *
     * @param string $key
     * @param mixed $value
     *
     * @return void
     */
    public function __set($key, $value)
    {
        $this->offsetSet($key, $value);
    }


    /**
     * Get setting for the given key.
     *
     * @param string $key
     *
     * @return mixed
     */
    public function offsetGet($key)
    {
        return $this->get($key);
    }


    /**
     * Get setting for the given key.
     *
     * @param string $key
     * @param mixed $default
     *
     * @return mixed
     */
    public function get($key, $default = null)
    {
        if (SensitiveSetting::isSensitive($key)) {
            $this->loadSensitiveSettings();

            return array_key_exists($key, $this->sensitiveSettings)
                ? $this->sensitiveSettings[$key]
                : $default;
        }

        if (! $this->settings->has($key)) {
            return $default;
        }

        $value = $this->settings->get($key);

        // Use default when the setting exists but is stored as null (not for 0/false/"").
        if ($value === null && func_num_args() === 2) {
            return $default;
        }

        return $value;
    }


    /**
     * Set a key / value setting pair.
     *
     * @param string $key
     * @param mixed $value
     *
     * @return void
     */
    public function offsetSet($key, $value)
    {
        $this->set([$key => $value]);
    }


    /**
     * Set the given settings.
     *
     * @param array $settings
     *
     * @return void
     */
    public function set($settings = [])
    {
        Setting::setMany($settings);
    }


    private function loadSensitiveSettings(): void
    {
        if ($this->sensitiveSettingsLoaded) {
            return;
        }

        $this->sensitiveSettings = Setting::query()
            ->whereIn('key', SensitiveSetting::keys())
            ->get()
            ->mapWithKeys(fn (Setting $setting) => [$setting->key => $setting->value])
            ->all();
        $this->sensitiveSettingsLoaded = true;
    }
}
