<?php

class AnyWay_Wordpress_Settings_Data
{

    protected $settings;
    protected $loaded = false;

    public function __construct()
    {
        $settings = get_option(ANYWAY_WORDPRESS_OPTION);
        $this->settings = static::apply_defaults($settings);
    }

    public function flush()
    {
        update_option(ANYWAY_WORDPRESS_OPTION, $this->settings);
    }

    public function get($name = null, $default = null)
    {
        if ($name === null)
            return $this->settings;

        if (is_array($name)) {
            $current = array();
            foreach ($name as $key => $value) {
                $current[$key] = isset($this->settings[$key])
                    ? $this->settings[$key]
                    : $default;
            }
            return $current;
        } else {
            return isset($this->settings[$name])
                ? $this->settings[$name]
                : $default;
        }
    }

    public function set($name, $value = null)
    {
        if (is_array($name)) {
            $current = array();
            foreach ($name as $key => $value) {
                $current[$key] = $this->get($name);
                $this->settings[$key] = $value;
            }
            $this->flush();
            return $current;
        } else {
            $current = $this->get($name);
            $this->settings[$name] = $value;
            $this->flush();
            return $current;
        }
    }
    public function delete($name)
    {
        if (is_array($name)) {
            $current = array();
            foreach ($name as $key) {
                $current[$key] = $this->get($name);
                unset($this->settings[$key]);
            }
            $this->flush();
            return $current;
        } else {
            $current = $this->get($name);
            unset($this->settings[$name]);
            $this->flush();
            return $current;
        }
    }

    public static function apply_defaults($settings = array())
    {
        if (!is_array($settings)) {
            $settings = array();
        }

        if (empty($settings['email'])) {
            $current_user = wp_get_current_user();
            if (($current_user instanceof WP_User) && $current_user->user_email) {
                $settings['email'] = array($current_user->user_email);
            }
        }

        return array_merge(array(
            'capture-update' => true,
            'send-stats' => false,
            'include-uploads' => false,
            'retain' => 3,
            'copy-number' => 0,
            'send-mail' => false
        ), $settings);
    }
}

/**
 * Class AnyWay_Wordpress_Settings
 * @method static get($name = null, $default = null)
 * @method static set($name, $value)
 */
class AnyWay_Wordpress_Settings
{
    protected static $data;

    public static function __callStatic($name, $arguments)
    {
        if (!static::$data) {
            static::$data = new AnyWay_Wordpress_Settings_Data();
        }
        return call_user_func_array(array(static::$data, $name), $arguments);
    }

}