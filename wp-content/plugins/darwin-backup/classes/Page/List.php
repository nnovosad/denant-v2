<?php


if (!class_exists('WP_List_Table')) {
    require_once(ABSPATH . 'wp-admin/includes/class-wp-list-table.php');
}

class AnyWay_List_Table extends WP_List_Table
{

    function get_columns()
    {
        $columns = array(
            'cb' => '<input type="checkbox" />',
            'date' => __('Date', ANYWAY_TEXTDOMAIN),
            'size' => __('Size', ANYWAY_TEXTDOMAIN),
            'wp_version' => __('Wordpress Version', ANYWAY_TEXTDOMAIN)
        );
        return $columns;
    }

    function get_recovery_points()
    {
        $runner = new AnyWay_Wordpress_Runner();
        return $runner->listBackups();
    }

    function prepare_items()
    {
        $columns = $this->get_columns();
        $hidden = array();
        $sortable = $this->get_sortable_columns();
        $this->_column_headers = array($columns, $hidden, $sortable);

        $items = $this->get_recovery_points();
        usort($items, array($this, 'usort_reorder'));

        $per_page = 10;
        $current_page = $this->get_pagenum();
        $total_items = count($items);

        // only ncessary because we have sample data
        $this->items = array_slice($items, (($current_page - 1) * $per_page), $per_page);

        $this->set_pagination_args(array(
            'total_items' => $total_items,                  //WE have to calculate the total number of items
            'per_page' => $per_page                     //WE have to determine how many items to show on a page
        ));
    }

    function column_default($item, $column_name)
    {
        switch ($column_name) {
            case 'date':
            case 'size':
            case 'wp_version':
                return $item[$column_name];
            default:
                return print_r($item, true); //Show the whole array for troubleshooting purposes
        }
    }

    function get_sortable_columns()
    {
        $sortable_columns = array(
            'date' => array('date', false),
            'size' => array('size', true),
            'wp_version' => array('wp_version', true),
        );
        return $sortable_columns;
    }

    function usort_reorder($a, $b)
    {
        // If no sort, default to title
        $orderby = (!empty($_GET['orderby'])) ? $_GET['orderby'] : 'date';
        // If no order, default to asc
        $order = (!empty($_GET['order'])) ? $_GET['order'] : 'desc';
        // Determine sort order
        switch ($orderby) {
            case 'size':
                $result = $a[$orderby] - $b[$orderby];
                break;
            case 'date':
                $result = $a[$orderby]->getTimestamp() - $b[$orderby]->getTimestamp();
                break;
            default:
                $result = strcmp($a[$orderby], $b[$orderby]);
        }
        // Send final sort direction to usort
        return ($order === 'asc') ? $result : -$result;
    }

    function column_date($item)
    {
        $runner = new AnyWay_Wordpress_Runner();
        $actions = array(
            'download' => sprintf('<a href="?page=%s&action=%s&sid=%s" class="anyway-button-download">' . __('Download', ANYWAY_TEXTDOMAIN) . '</a>', $_REQUEST['page'], 'download', $item['sid']),
            'mail' => sprintf('<a href="#" class="anyway-button-mail-dialog" data-sid="%s">' . __('Email me the link', ANYWAY_TEXTDOMAIN) . '</a>', $item['sid'], $item['sid']),
            'clipboard' => sprintf('<a href="#" class="anyway-button-clipboard" data-clipboard-text="%s">' . __('Copy link to clipboard', ANYWAY_TEXTDOMAIN) . '</a>', $runner->generateStoreLink($item['sid'], true)),
            'restore' => sprintf('<a target="_blank" href="%s" class="anyway-button-restore">' . __('Restore', ANYWAY_TEXTDOMAIN) . '</a>', $runner->generateStoreLink($item['sid'], true)),
            'delete' => sprintf('<a href="?page=%s&action=%s&sid=%s" class="anyway-button-delete">' . __('Delete', ANYWAY_TEXTDOMAIN) . '</a>', $_REQUEST['page'], 'delete', $item['sid']),
        );

        if (preg_match('/(?i)msie [5-8]/', $_SERVER['HTTP_USER_AGENT'])) {
            unset($actions['clipboard']);
        }

        $frequency = sprintf("<span class=\"label\">%s</span>",
            isset($item['frequency'])
                ? __($item['frequency'], ANYWAY_TEXTDOMAIN)
                : __('manual', ANYWAY_TEXTDOMAIN));
        $uploads = !empty($item['uploads_included'])
            ? sprintf("<span class=\"label\">%s</span>", __('uploads', ANYWAY_TEXTDOMAIN))
            : "";

        $labels = join(" ", array($frequency, $uploads));
        /* @var DateTime $date */
        $date = $item['date'];

        if ($tzstring = get_option('timezone_string')) {
        } elseif ($gmt_offset = (int)get_option('gmt_offset')) {
            $seconds = $gmt_offset * 60 * 60;
            $tzstring = timezone_name_from_abbr('', $seconds, 1);
            // Workaround for bug #44780
            if ($tzstring === false)
                $tzstring = timezone_name_from_abbr('', $seconds, 0);
        }

        try {
            $timezone = new DateTimeZone($tzstring);
            $date->setTimezone($timezone);
        } catch (Exception $e) {
        }

        return sprintf('%s %s %s', $date->format("j F Y H:i:s") . ", " . human_time_diff($date->getTimestamp(), time()) . ' ago ', $labels, $this->row_actions($actions));
    }

    function column_size($item)
    {
        return $item['size_human'];
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
            '<input type="checkbox" name="sid[]" value="%s" />', $item['sid']
        );
    }
}

class AnyWay_Wordpress_Page_List extends AnyWay_Wordpress_Page_Base
{
    static $slug = 'darwin-backup';
    static $title = 'All Backups - Darwin Backup';
    static $menu = 'All Backups';

    public $order = 1;

    public function __construct()
    {
        parent::__construct();
        add_action('plugins_loaded', array($this, 'plugins_loaded'));
    }

    public function plugins_loaded()
    {
        if (isset($_REQUEST['action']) && ($_REQUEST['action'] == 'download') && isset($_REQUEST['sid']) && current_user_can(static::$permissions)) {
            $runner = new AnyWay_Wordpress_Runner();
            if (false !== ($filename = $runner->getBackupFilename($_REQUEST['sid']))) {
                @ini_set('zlib.output_compression', 'Off');
                if (function_exists('session_status') && session_status() != constant('PHP_SESSION_ACTIVE') || session_id() != '')
                    @session_write_close();
                @set_time_limit(0);
                header('Content-Type: application/octet-stream');
                header('Content-Disposition: attachment; filename=' . basename($filename));
                header('Content-Length: ' . filesize($filename));
                while (@ob_end_clean()) ;
                flush();
                readfile($filename);
                exit;
            }
        }
    }

    public function display()
    {
        $table = new AnyWay_List_Table();

        if ($table->current_action() == 'delete') {
            $runner = new AnyWay_Wordpress_Runner();
            $runner->deleteBackups($_REQUEST['sid']);
        }

        $table->prepare_items();
        echo $this->render('list_page.php', array(
            'table' => $table
        ));
    }
}

return new AnyWay_Wordpress_Page_List();
