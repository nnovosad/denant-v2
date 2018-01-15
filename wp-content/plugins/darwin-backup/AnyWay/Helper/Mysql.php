<?php

class AnyWay_Helper_Mysql
{

    /**
     * @param array $settings
     * @return mysqli
     * @throws Exception
     */
    public static function connect($settings = array(), $timeout = 2)
    {

        if (!isset($settings['db_host']) || !$settings['db_host']) {
            throw new Exception("db host is missing");
        }

        if (!isset($settings['db_user']) || !$settings['db_user']) {
            throw new Exception("db user is missing");
        }

        if (!isset($settings['db_name']) || !$settings['db_name']) {
            throw new Exception("db name is missing");
        }

        $password = isset($settings['db_password'])
            ? $settings['db_password']
            : '';

        $connection = mysqli_init();
        $connection->options(MYSQLI_OPT_CONNECT_TIMEOUT, $timeout);

        if (2 == count($parts = explode(":", $settings['db_host']))) {
            if (preg_match('/^\d+$/', $parts[1])) {
                @$connection->real_connect(
                    $parts[0],
                    $settings['db_user'],
                    $password,
                    $settings['db_name'],
                    $parts[1]
                );
            } else {
                @$connection->real_connect(
                    $parts[0],
                    $settings['db_user'],
                    $password,
                    $settings['db_name'],
                    null,
                    $parts[1]
                );
            }
        } else {
            @$connection->real_connect(
                $settings['db_host'],
                $settings['db_user'],
                $password,
                $settings['db_name']
            );
        }

        if ($connection->connect_errno) {
            throw new Exception($connection->connect_error);
        }

        return $connection;
    }

    public static function check($settings = array(), $timeout = 2)
    {
        if (static::connect($settings, $timeout)) {
            return $settings;
        }
        return null;
    }
}