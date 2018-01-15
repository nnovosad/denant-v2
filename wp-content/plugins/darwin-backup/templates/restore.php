<?php

if (!empty($_GET['rid']) && preg_match('/^\w+\.\w+$/', $_GET['rid']) && $files = glob('./20*' . $_GET['rid'] . '.php')) {
    if ((count($files) == 1) && (false !== ($handle = fopen($files[0], "rb"))) && (false !== ($header = fread($handle, 1024)))) {
        if (preg_match('/bootstrap.php:1024:(\d+)/', $header, $matches)) {
            if (false !== ($data = fread($handle, $matches[1]))) {
                $data = preg_replace('/__FILE__/', var_export($files[0], true), $data);
                ob_start();
                eval($data);
                $result = ob_get_clean();
                $result = preg_replace('/([\"\'])\?(\w+=.*?)(?=\1)/', '$1?$2&rid=' . str_replace('.', '%2E', $_GET['rid']), $result);
                echo $result;
                exit;
            }
        }
    }
}

header('HTTP/1.0 403 Forbidden');
echo 'Access Denied';
