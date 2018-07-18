<?php

namespace League\Flysystem;

class Config
{
    /**
     * @var array
     */
    protected $settings = [];

    /**
     * @var Config
     */
    protected $fallback;

    /**
     * Constructor.
     *
     * @param array $settings
     */
    public function __construct(array $settings = [])
    {
        $this->settings = $settings;
    }

    /**
     * Get a setting.
     *
     * @param string $key
     * @param mixed  $default
     *
     * @return mixed config setting or default when not found
     */
    public function get($key, $default = null)
    {
        return $this->settings[$key] ?? $default;
    }

    /**
     * Check if an item exists by key.
     *
     * @param string $key
     *
     * @return bool
     */
    public function has($key)
    {
        return array_key_exists($key, $this->settings);
    }

    /**
     * Set a setting.
     *
     * @param string $key
     * @param mixed  $value
     *
     * @return $this
     */
    public function set($key, $value)
    {
        $this->settings[$key] = $value;

        return $this;
    }

    /**
     * Set the fallback.
     *
     * @param Config $fallback
     *
     * @return $this
     */
    public function setDefaults(Config $fallback)
    {
        $this->settings += $fallback->settings;

        return $this;
    }
}
