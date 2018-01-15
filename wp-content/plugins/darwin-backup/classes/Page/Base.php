<?php

abstract class AnyWay_Wordpress_Page_Base extends AnyWay_Wordpress_Plugin
{
    static $slug;
    static $title;
    static $menu;
    static $permissions = 'update_core';

    public $order = 0;

    protected $actions = array();
    protected $notices = array();
    protected $shutdown_handler_enabled = false;
    protected $in_shutdown_handler = false;

    public function add_notice()
    {
        $args = func_get_args();
        if (!$args)
            throw new Exception("At least notice message should be supplied");
        $this->notices[] = $args;
    }

    public function render_notices()
    {
        $result = "";
        foreach ($this->notices as $notice) {
            $message = array_shift($notice);
            array_unshift($notice, __($message, ANYWAY_TEXTDOMAIN));
            $result .= $this->render('notice.php', array(
                'message' => call_user_func_array("sprintf", $notice)
            ));
        }
        return $result;
    }

    public function init()
    {
        global $wp_scripts, $wp_styles;
        // all the pages will have own shutdown handler, but not all of them will use it due to $shutdown_handler_enabled
        register_shutdown_function(array($this, 'shutdown_handler'));

        $css_dependendencies = array();

        if (!defined('DOING_AJAX') || !DOING_AJAX) {
            $css_dependendencies[] = 'anyway-jquery-ui';
            wp_register_style("anyway-jquery-ui", "//ajax.googleapis.com/ajax/libs/jqueryui/" . $wp_scripts->registered['jquery-ui-core']->ver . "/themes/smoothness/jquery-ui.min.css");
        }

        if (!empty($wp_styles->registered['list-tables'])) {
            $css_dependendencies[] = 'list-tables';
        }

        wp_register_style("anyway", plugins_url('/css/style.css', ANYWAY_BASEFILE), $css_dependendencies, ANYWAY_VERSION);

        wp_register_script("anyway-error", plugins_url('/js/error.js', ANYWAY_BASEFILE), array('jquery'), ANYWAY_VERSION);
        wp_register_script("anyway-app", plugins_url('/js/app.js', ANYWAY_BASEFILE), array('jquery'), ANYWAY_VERSION);
        if (preg_match('/(?i)msie [5-8]/', $_SERVER['HTTP_USER_AGENT'])) {
            // if IE<=8
            wp_register_script(static::$slug, plugins_url('/js/' . static::$slug . ".js", ANYWAY_BASEFILE),
                array('jquery-ui-dialog', 'jquery-ui-progressbar', 'jquery-ui-tooltip', 'anyway-app', 'anyway-error', 'anyway-dismissible'), ANYWAY_VERSION);
        } else {
            // if IE>8
            wp_register_script("anyway-clipboard", plugins_url('/js/vendor/clipboard.min.js', ANYWAY_BASEFILE));
            wp_register_script(static::$slug, plugins_url('/js/' . static::$slug . ".js", ANYWAY_BASEFILE),
                array('jquery-ui-dialog', 'jquery-ui-progressbar', 'jquery-ui-tooltip', 'anyway-app', 'anyway-error', 'anyway-clipboard', 'anyway-dismissible'), ANYWAY_VERSION);
        }

        $this->add_action('wp_ajax_anyway_dismiss_notice_ajax', array($this, 'dismiss_notice_ajax'));
    }

    public function dismiss_notice_ajax($name, $interval = 0)
    {
        if (!$name) {
            $name = sanitize_text_field($_POST['name']);
            $interval = sanitize_text_field($_POST['interval']);
        }

        $name = $name . '-'. (defined('DARWIN_BACKUP_COPY_NUMBER') ? DARWIN_BACKUP_COPY_NUMBER : 1);

        $notifications = get_option('darwin_backup_notifications', array());
        if ($interval) {
            $interval = (0 == absint($interval)) ? 1 : $interval;
            $expiration = absint($interval) * DAY_IN_SECONDS;
            $notifications[$name] = (time() + $expiration);
        } else {
            $notifications[$name] = 'forever';
        }
        update_option('darwin_backup_notifications', $notifications);
        $this->sendSuccess();
    }

    public function clear_dismissed_notice_state($name)
    {
        $name = $name . '-'. (defined('DARWIN_BACKUP_COPY_NUMBER') ? DARWIN_BACKUP_COPY_NUMBER : 1);

        $notifications = get_option('darwin_backup_notifications', array());
        unset($notifications[$name]);
        update_option('darwin_backup_notifications', $notifications);
    }

    public function is_notice_active($name)
    {
        $name = $name . '-'. (defined('DARWIN_BACKUP_COPY_NUMBER') ? DARWIN_BACKUP_COPY_NUMBER : 1);

        $notifications = get_option('darwin_backup_notifications', array());

        $expiration = isset($notifications[$name])
            ? $notifications[$name]
            : 0;

        if ('forever' === $expiration) {
            return false;
        } elseif ($expiration >= time()) {
            $this->clear_dismissed_notice_state($name);
            return false;
        } else {
            return true;
        }
    }

    public function add_storage_notices()
    {
        if (AnyWay_Wordpress_Settings::get(AnyWay_Wordpress_Runner::OPTION) === AnyWay_Wordpress_WritableDirectoryDetector::PLUGIN_DIR_STORAGE) {
            $this->add_action('all_admin_notices', array($this, 'display_plugin_storage_notice'));
        } elseif (AnyWay_Wordpress_Settings::get(AnyWay_Wordpress_Runner::UNVERIFIED_OPTION) && $this->is_notice_active('unverified-storage')) {
            $this->add_action('all_admin_notices', array($this, 'display_unverified_notice'));
        }
        error_log($this->is_notice_active("no-cron"));
        if (defined('DISABLE_WP_CRON') && DISABLE_WP_CRON && $this->is_notice_active("no-cron")) {
            $this->add_action('all_admin_notices', array($this, 'display_cron_notice'));
        }
    }

    public function display_unverified_notice()
    {
        printf('<div darwin-data-dismissible="unverified-storage" class="notice notice-warning is-dismissible"><p>We were unable to verify whether you will be able to restore from backups on this server using generated links. Please don\'t dismiss this notification until you make sure backup links work for you, thank you</p></div>');
    }

    public function display_plugin_storage_notice()
    {
        printf('<div class="notice notice-error"><p>The only suitable directory for backups we\'ve found is the plugin\'s own directory. That means all the backups will be wiped out during next Darwin Backup plugin upgrade. Please download your backups and store them locally before upgrading Darwin Backup plugin</p></div>');
    }

    public function display_cron_notice()
    {
        printf('<div darwin-data-dismissible="no-cron" class="notice notice-warning is-dismissible"><p>Cron jobs are disabled on this server. Scheduled backups will not be peformed. See <a href="https://codex.wordpress.org/Editing_wp-config.php#Disable_Cron_and_Cron_Timeout">Disable Cron and Cron Timeout</a> article for reference</p></div>');
    }

    public function menu()
    {
        if (static::$menu) { // no need for permissions as they are passed to add_submenu_page();
            /* string $parent_slug, string $page_title, string $menu_title, string $capability, string $menu_slug, callable $function = '' */
            $hook = add_submenu_page(
                'darwin-backup',
                __(static::$title, ANYWAY_TEXTDOMAIN),
                static::$menu,
                static::$permissions,
                static::$slug, array($this, 'safe_display'));
            add_action('load-' . $hook, array($this, 'prerequisites'));
        }
    }

    public function add_action($tag, $function_to_add, $priority = 10, $accepted_args = 1)
    {
        if (current_user_can(static::$permissions)) {
            if (!isset($this->actions[$tag])) {
                $this->actions[$tag] = array();
            }
            $this->actions[$tag][] = array($function_to_add, $priority, $accepted_args);
            add_action($tag, array($this, 'safe_action'), $priority, $accepted_args);
        }
    }

    public function sort_actions($a, $b)
    {
        //php -r '$c = [10,1,2]; usort($c, function ($a, $b) { return $a - $b; }); var_dump($c);'
        return $a[1] - $b[1];
    }

    public function error_handler($errno, $errstr, $errfile, $errline)
    {
        if (!((E_ERROR | E_PARSE | E_CORE_ERROR | E_COMPILE_ERROR) & $errno)) {
            // This error code is not included in error_reporting
            return false;
        }
        $this->exception_handler(new ErrorException($errstr, 0, $errno, $errfile, $errline));
        return true;
    }

    public function exception_handler($e)
    {
        error_log($e);
        $message = $this->render_exception($e);

        $tag = current_filter();
        if (strpos($tag, 'wp_ajax_') === 0) {
            $this->sendError(array('message' => $message));
        } else {
            if (!wp_style_is('anyway', 'enqueued')) {
                echo "<style>";
                @include(ANYWAY_BASEDIR . '/css/style.css');
                echo "</style>";
            }
            if (!wp_script_is('anyway-clipboard', 'enqueued')) {
                echo "<script>";
                @include(ANYWAY_BASEDIR . '/js/vendor/clipboard.min.js');
                echo "</script>";
            }
            if (!wp_script_is('anyway-error', 'enqueued')) {
                echo "<script>";
                @include(ANYWAY_BASEDIR . '/js/error.js');
                echo "</script>";
            }
            echo $message;
        }
        die();
    }

    public function shutdown_handler()
    {
        if (!$this->in_shutdown_handler && $this->shutdown_handler_enabled) {
            $this->in_shutdown_handler = true;
            if (null !== ($last_error = error_get_last())) {
                $this->error_handler($last_error['type'], $last_error['message'], $last_error['file'], $last_error['line']);
            }
        }
    }

    public function safe_action()
    {
        $this->shutdown_handler_enabled = true;
        set_error_handler(array($this, 'error_handler'));
        set_exception_handler(array($this, 'exception_handler'));

        $tag = current_filter();
        if (!empty($this->actions[$tag])) {
            usort($this->actions[$tag], array($this, 'sort_actions'));
            foreach ($this->actions[$tag] as $args) {
                call_user_func_array($args[0], func_get_args());
            }
        }

        restore_error_handler();
        restore_exception_handler();
        $this->shutdown_handler_enabled = false;
    }

    public function safe_display()
    {
        $this->shutdown_handler_enabled = true;
        set_error_handler(array($this, 'error_handler'));
        set_exception_handler(array($this, 'exception_handler'));

        $this->display();

        restore_error_handler();
        restore_exception_handler();
        $this->shutdown_handler_enabled = false;
    }

    /**
     * @param Exception $e
     * @return string
     */
    public function render_exception($e)
    {
        $trace = @print_r($e, true);

        if (isset($_SERVER['DOCUMENT_ROOT']))
            $trace = preg_replace('#' . preg_quote($_SERVER['DOCUMENT_ROOT'], '#') . '#m', '', $trace);
        if (defined(ABSPATH))
            $trace = preg_replace('#' . preg_quote(ABSPATH, '#') . '#m', '', $trace);

        if (2 == count($parts = explode(':', DB_HOST))) {
            if (preg_match('/^\d+$/', $parts[1])) {
                $trace = preg_replace('/' . preg_quote(DB_HOST) . '/', '*****:port', $trace);
            } else {
                $trace = preg_replace('/' . preg_quote(DB_HOST) . '/', '*****:/socket', $trace);
            }
        } else {
            $trace = preg_replace('/' . preg_quote(DB_HOST) . '/', '*****', $trace);
        }
        $trace = preg_replace('/' . preg_quote(DB_USER) . '/', '*****', $trace);
        $trace = preg_replace('/' . preg_quote(DB_NAME) . '/', '*****', $trace);
        $trace = preg_replace('/' . preg_quote(DB_PASSWORD) . '/', '*****', $trace);

        /* @var Exception $exception */
        return $this->render('notice/error.php', array(
            'code' => $e->getCode(),
            'message' => $e->getMessage(),
            'stack' => $trace
        ));
    }

    public function prerequisites()
    {
        wp_enqueue_style("anyway"); // will pull all the dependencies
        wp_enqueue_script(static::$slug); // will pull all the dependencies
        $this->add_storage_notices();
    }

    abstract public function display();

}