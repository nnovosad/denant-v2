<?php

class AnyWay_Wordpress_Plugin
{

    public $start_time;

    public function __construct()
    {
        $this->start_time = isset($_SERVER['REQUEST_TIME'])
            ? (float)$_SERVER['REQUEST_TIME']
            : microtime(true);
    }

    /*
    public function getPage($name)
    {
        $pages = apply_filters('anyway_list_pages', array()); // see index.php

        if (isset($pages[$name])) {
            return $pages[$name];
        }
        throw new Exception("Unknown page $name");
    }
    */

    public function sendJson($response)
    {
        @header('Content-Type: application/json;charset=' . get_option('blog_charset'));
        echo json_encode($response);
        exit();
    }

    public function sendSuccess($data = null)
    {
        $response = array('success' => true);

        if (isset($data))
            $response['data'] = $data;

        $this->sendJson($response);
    }

    public function sendError($data = null)
    {
        $response = array('success' => false);

        if (isset($data))
            $response['data'] = $data;

        $this->sendJson($response);
    }

    public function render($file, $vars = array())
    {
        global $wp_version;
        extract($vars);
        $version = preg_replace('/\D+/', '', $wp_version);
        $version = substr($version, 0, 2);
        $wp_version_class = '';
        for ($i = $version; $i >= 30; $i--) {
            $wp_version_class .= ' wp_' . $i;
        }

        ob_start();
        require(ANYWAY_BASEDIR . '/templates/' . $file);
        return ob_get_clean();
    }

}
