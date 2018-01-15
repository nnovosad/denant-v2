<?php

class AnyWay_Schedule_Table extends WP_List_Table
{

    protected $sorted_schedules = array();

    function __construct($args = array())
    {
        parent::__construct($args);

        if (empty($args['sorted_schedules']))
            throw new Exception('sorted_schedules cannot be empty');

        $this->sorted_schedules = $args['sorted_schedules'];
    }

    function get_columns()
    {
        $columns = array(
            'cb' => '<input type="checkbox" />',
            'frequency' => __('Frequency', ANYWAY_TEXTDOMAIN),
            'include_uploads' => __('Backup uploads folder', ANYWAY_TEXTDOMAIN),
            'retain' => __('Number of backups to keep', ANYWAY_TEXTDOMAIN)
        );
        return $columns;
    }

    function prepare_items($schedule = array())
    {
        $columns = $this->get_columns();
        $sortable = $this->get_sortable_columns();
        $this->_column_headers = array($columns, array(), $sortable);

        $items = $schedule;
        usort($items, array($this, 'items_compare'));

        $per_page = 100;
        $current_page = $this->get_pagenum();
        $total_items = count($items);

        // only ncessary because we have sample data
        $this->items = array_slice($items, (($current_page - 1) * $per_page), $per_page);

        $this->set_pagination_args(array(
            'total_items' => $total_items,              //WE have to calculate the total number of items
            'per_page' => $per_page                     //WE have to determine how many items to show on a page
        ));
    }

    function column_default($item, $column_name)
    {
        switch ($column_name) {
            case 'frequency':
            case 'include_uploads':
            case 'retain':
                return $item[$column_name];
            default:
                return print_r($item, true); //Show the whole array for troubleshooting purposes
        }
    }

    function get_sortable_columns()
    {
        $sortable_columns = array(
            'frequency' => array('frequency', true),
            'include_uploads' => array('include_uploads', true),
            'retain' => array('retain', true),
        );
        return $sortable_columns;
    }

    function items_compare($a, $b)
    {
        // If no sort, default to title
        $orderby = (!empty($_GET['orderby'])) ? $_GET['orderby'] : 'frequency';
        // If no order, default to asc
        $order = (!empty($_GET['order'])) ? $_GET['order'] : 'asc';
        // Determine sort order
        switch ($orderby) {
            case 'frequency':
                $result = @$this->sorted_schedules[$a['frequency']]['interval'] - @$this->sorted_schedules[$b['frequency']]['interval'];
                break;
            case 'include_uploads':
                $result = (int)$a[$orderby] - (int)$b[$orderby];
                break;
            case 'retain':
                $result =
                    $a[$orderby] && $b[$orderby]
                        ? (int)$a[$orderby] - (int)$b[$orderby]
                        : $a[$orderby]
                        ? (int)$a[$orderby] - 100000
                        : 100000 - (int)$b[$orderby];
                break;
            default:
                $result = strcmp($a[$orderby], $b[$orderby]);
        }
        // Send final sort direction to usort
        return ($order === 'asc') ? $result : -$result;
    }

    function column_frequency($item)
    {
        $edit_link = sprintf('<a id="%s" data-json="%s" href="#" class="anyway-button-edit">%s</a>',
            $item['frequency'],
            htmlentities(json_encode($item)),
            __('Edit', ANYWAY_TEXTDOMAIN)
        );
        $delete_link = sprintf('<a href="?page=%s&action=%s&frequency=%s" class="anyway-button-delete">%s</a>',
            $_REQUEST['page'],
            'delete',
            $item['frequency'],
            __('Delete', ANYWAY_TEXTDOMAIN)
        );

        $actions = array(
            'edit' => $edit_link,
            'delete' => $delete_link,
        );

        $available_schedules = wp_get_schedules();
        foreach ($available_schedules as $key => $value) {
            if ($item['frequency'] == $key) {
                return $value['display'] . $this->row_actions($actions);
            }
        }
    }

    function column_include_uploads($item)
    {
        return $item['include_uploads']
            ? __('Yes', ANYWAY_TEXTDOMAIN)
            : __('No', ANYWAY_TEXTDOMAIN);
    }

    function column_retain($item)
    {
        return $item['retain']
            ? sprintf(__("%d latest backups", ANYWAY_TEXTDOMAIN), htmlentities($item['retain']))
            : __("Unlimited", ANYWAY_TEXTDOMAIN);
    }

    function get_bulk_actions()
    {
        $actions = array(
            'delete' => 'Delete'
        );
        return $actions;
    }

    function column_cb($item)
    {
        return sprintf(
            '<input type="checkbox" name="frequency[]" value="%s" />', $item['frequency']
        );
    }
}

class AnyWay_Wordpress_Page_Schedule extends AnyWay_Wordpress_Page_Base
{
    static $slug = 'darwin-backup-schedule';
    static $title = 'Schedule - Darwin Backup';
    static $menu = 'Schedule';

    public $order = 4;

    public function __construct()
    {
        parent::__construct();
        add_filter('cron_schedules', array($this, 'additional_schedules'), 99);
        add_action('darwin_scheduled_backup', array($this, 'scheduled_backup'), 0, 2);
        //$this->clear_scheduled_hook_all('darwin_scheduled_backup');
    }

    public function additional_schedules($schedules)
    {
        $keys = array();
        foreach ($schedules as $key => $schedule) {
            if ($schedule['interval'] < 3600 ||
                86400 * 7 == $schedule['interval'] ||
                86400 * ((365 * 4 + 1) / 48 == $schedule['interval']) ||
                2635200 == $schedule['interval']
            ) {
                $keys[] = $key;
            }
        }
        foreach ($keys as $key) {
            unset($schedules[$key]);
        }
        if (!isset($schedules['weekly']))
            $schedules['weekly'] = array('interval' => 86400 * 7, 'display' => __('Once Weekly', ANYWAY_TEXTDOMAIN));
        if (!isset($schedules['monthly']))
            $schedules['monthly'] = array('interval' => 86400 * ((365 * 4 + 1) / 48), 'display' => __('Once Monthly', ANYWAY_TEXTDOMAIN));
        return $schedules;
    }

    /**
     * @returns void
     */
    public function clear_scheduled_hook_all($hook)
    {
        $crons = _get_cron_array();
        if (empty($crons))
            return;
        foreach ($crons as $timestamp => &$cron) {
            if (isset($cron[$hook])) {
                unset($cron[$hook]);
            }
            if (empty($crons[$timestamp])) {
                unset($crons[$timestamp]);
            }
        }
        _set_cron_array($crons);
    }

    function schedule_compare($a, $b)
    {
        if ($a['interval'] == $b['interval']) {
            return 0;
        }
        return ($a['interval'] < $b['interval']) ? -1 : 1;
    }

    public function unschedule_backup($frequency)
    {
        $args = array($frequency, null);
        if ($timestamp = wp_next_scheduled('darwin_scheduled_backup', $args)) {
            wp_unschedule_event($timestamp, 'darwin_scheduled_backup', $args);
        }
    }

    // interval and target are just to avoid filtering duplicates
    public function scheduled_backup($frequency, $sid = null)
    {
        ignore_user_abort(true); // let us finish the task

        if (!$sid) {

            $schedule = AnyWay_Wordpress_Settings::get('schedule', array());

            if (empty($schedule[$frequency])) {
                $this->unschedule_backup($frequency);
                return;
            }

            $runner = new AnyWay_Wordpress_Runner();
            $runner->on('sid', function ($event, $value) use (&$sid) {
                $sid = $value;
            });

            $runner->init(
                array(
                    'phpsource' => ANYWAY_RESTOREFILE,
                    'include-uploads' => $schedule[$frequency]['include_uploads'],
                    'send_stats' => false,
                    'frequency' => $schedule[$frequency]['frequency'],
                    'retain' => $schedule[$frequency]['retain'],
                    'send-mail' => $schedule[$frequency]['send-mail'],
                    'email' => $schedule[$frequency]['email']
                )
            );
        }

        if ($sid) {

            $deadline = $this->start_time + 5;
            $max_execution_time = (int)ini_get("max_execution_time");
            if (empty($max_execution_time) || $max_execution_time <= 0 || $max_execution_time > 60) {
                $max_execution_time = 60;
            }
            $hardDeadline = $this->start_time + $max_execution_time - 6;
            if ($deadline > $hardDeadline) {
                $deadline = $hardDeadline;
            }

            $done = false;

            while (!$done && (microtime(true) < $hardDeadline)) {
                try {
                    $runner = new AnyWay_Wordpress_Runner($sid);
                    $runner->on('done', function ($event, $value) use (&$done) {
                        $done = true;
                    });
                    $runner->nextStep($deadline, $hardDeadline);
                } catch (Exception $e) {
                    error_log($e);
                    $done = true;
//                    if (!empty($runner))
//                        $runner->stop();
                }
            }

            if (!$done) {
                wp_schedule_single_event(time(), 'darwin_scheduled_backup', array($frequency, $sid));
            }
        }
    }

    public function get_sorted_schedules()
    {
        $schedules = wp_get_schedules();
        uasort($schedules, array($this, 'schedule_compare'));
        return $schedules;
    }

    public function display()
    {

        $table = new AnyWay_Schedule_Table(array(
            'sorted_schedules' => $this->get_sorted_schedules()
        ));


        $schedule = AnyWay_Wordpress_Settings::get('schedule', array());

        if (isset($_REQUEST['frequency']) && ($frequencies = $frequency = $_REQUEST['frequency'])) {

            // 1.1.0-1.1.2 backward compatibility
            $migrated = array();
            foreach ($schedule as $key => $value) {
                if ($key !== $value['frequency']) {
                    $migrated[$value['frequency']] = $value;
                    unset($schedule[$key]);
                }
            }
            $schedule = array_merge($schedule, $migrated);

            if ($table->current_action() == 'delete') {

                foreach ((array)$frequencies as $frequency) {
                    $this->unschedule_backup($frequency);
                    unset($schedule[$frequency]);
                }

            } else {

                if (isset($schedule[$frequency])) {
                    $this->unschedule_backup($frequency);
                }

                if (!isset($schedule[$frequency]))
                    $schedule[$frequency] = array();

                $schedule[$frequency] = array_merge($schedule[$frequency], array(
                    'frequency' => $frequency,
                    'include_uploads' => !empty($_POST['include_uploads']),
                    'retain' => empty($_POST['retain'])
                        ? null
                        : $_POST['retain'],
                ));

                $found_emails = array();
                if (isset($_POST['email']) && $email = trim($_POST['email'])) {
                    $emails = preg_split('/[ \t\n\r\0\x0B,;]+/', $email);
                    foreach ($emails as $email) {
                        if ($email) {
                            if (is_email($email)) {
                                $found_emails[] = $email;
                            } else {
                                $this->add_notice("Email address '%s' is invalid, removed from the list", $email);
                            }
                        }
                    }
                }

                if ($found_emails) {
                    $schedule[$frequency]['email'] = $found_emails;
                    $schedule[$frequency]['send-mail'] = !empty($_POST['send-mail']);
                } else {
                    $schedule[$frequency]['send-mail'] = false;
                }

                wp_schedule_event(time(), $frequency, 'darwin_scheduled_backup', array($frequency, null));
            }

            AnyWay_Wordpress_Settings::set('schedule', $schedule);
        }

        $table->prepare_items(array_values($schedule));

        echo $this->render_notices();
        echo $this->render('schedule_page.php', array(
            'table' => $table,
            'schedule' => $schedule,
            'available_schedules' => $this->get_sorted_schedules()
        ));
    }

}

return new AnyWay_Wordpress_Page_Schedule();
