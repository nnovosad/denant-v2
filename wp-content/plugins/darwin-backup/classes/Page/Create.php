<?php

class AnyWay_Wordpress_Page_Create extends AnyWay_Wordpress_Page_Base
{
    static $slug = 'darwin-backup-create';
    static $title = 'Add New - Darwin Backup';
    static $menu = 'Add New';

    public $order = 2;
    public $events = array();

    public function init()
    {
        parent::init();
        $this->add_action('wp_ajax_anyway_start_ajax', array($this, 'start_ajax'));
        $this->add_action('wp_ajax_anyway_stop_ajax', array($this, 'stop_ajax'));
        $this->add_action('wp_ajax_anyway_next_step_ajax', array($this, 'next_step_ajax'));
    }

    public function start_ajax()
    {
        if (function_exists('session_status') && session_status() != constant('PHP_SESSION_ACTIVE') || session_id() != '')
            @session_write_close();

        ignore_user_abort(true); // let us finish the task

        $runner = new AnyWay_Wordpress_Runner();
        $runner->on(null, array($this, 'onEvent'));
        $runner->init(
            array(
                'phpsource' => ANYWAY_RESTOREFILE,
                'include-uploads' => !empty($_POST['include-uploads']) && ($_POST['include-uploads'] !== 'false'),
                'send_stats' => AnyWay_Wordpress_Settings::get('send-stats'),
                'frequency' => 'manual',
                'retain' => AnyWay_Wordpress_Settings::get('retain'),
                'send-mail' => AnyWay_Wordpress_Settings::get('send-mail'),
                'email' => AnyWay_Wordpress_Settings::get('email')
            )
        );
        $this->sendSuccess($this->events);
    }

    public function stop_ajax()
    {
        if (function_exists('session_status') && session_status() != constant('PHP_SESSION_ACTIVE') || session_id() != '')
            @session_write_close();

        ignore_user_abort(true); // let us finish the task

        if (isset($_POST['sid']) && ($sid = $_POST['sid'])) {
            try {
                $runner = new AnyWay_Wordpress_Runner($sid, (int)ini_get("max_execution_time") - 4);
                $runner->on(null, array($this, 'onEvent'));
                $runner->stop();
            } catch (Exception $e) {
                // do nothing if unable
                // will collect in a week
                error_log($e);
            }
            $this->sendSuccess($this->events);
            return;
        }

        throw new Exception("No sid provided");
    }

    public function onEvent()
    {
        $this->events[] = func_get_args();
    }

    public function next_step_ajax()
    {
        if (function_exists('session_status') && session_status() != constant('PHP_SESSION_ACTIVE') || session_id() != '')
            @session_write_close();

        ignore_user_abort(true); // let us finish the task

        $deadline = $this->start_time + 5;
        $max_execution_time = (int)ini_get("max_execution_time");
        // for fastcgi environments nxing might be set to 30 seconds timeout
        // even though max_execution_time might be longer
        // seem that some users get "Unable to lock state file" due to this
        if (empty($max_execution_time) || $max_execution_time <= 0 || $max_execution_time > 30) {
            $max_execution_time = 30;
        }
        $hardDeadline = $this->start_time + $max_execution_time - 6;
        if ($deadline > $hardDeadline) {
            $deadline = $hardDeadline;
        }

        $sid = isset($_GET['sid'])
            ? $_GET['sid']
            : @$_POST['sid'];

        if (!$sid)
            throw new Exception("No sid provided");

        $runner = new AnyWay_Wordpress_Runner($sid);
        $runner->on(null, array($this, 'onEvent'));
        $runner->nextStep($deadline, $hardDeadline);
        $this->sendSuccess($this->events);
    }

    public function display()
    {
        defined('ANYWAY_RUNNING') or define('ANYWAY_RUNNING', true);
        echo $this->render('create_page.php', array(
            'settings' => AnyWay_Wordpress_Settings::get()
        ));
    }
}

return new AnyWay_Wordpress_Page_Create();
