<?php

class AnyWay_Helper_Server
{
    public static function is_ssl() {
        $isSecure = false;
        if (isset($_SERVER['HTTPS']) && ($_SERVER['HTTPS'] == 'on' || $_SERVER['HTTPS'] == '1')) {
            $isSecure = true;
        } elseif (isset($_SERVER['SERVER_PORT']) && ($_SERVER['SERVER_PORT'] == '443')) {
            $isSecure = true;
        } elseif (!empty($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] == 'https') {
            $isSecure = true;
        } elseif (!empty($_SERVER['HTTP_X_FORWARDED_SSL']) && ($_SERVER['HTTP_X_FORWARDED_SSL'] == 'on' || $_SERVER['HTTP_X_FORWARDED_SSL'] == '1')) {
            $isSecure = true;
        } elseif (!empty($_SERVER['HTTP_X_WPE_SSL'])) {
            $isSecure = true;
        }
        return $isSecure;
    }
}