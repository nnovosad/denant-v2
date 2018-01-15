<?php

class AnyWay_Planner_WordpressBackup
{
    public static function buildInitialPlan($settings = array())
    {
        if (empty($settings['filename']))
            throw new Exception("Missing filename parameter");

        if (empty($settings['phpsource']))
            throw new Exception("Missing phpsource parameter");

        if (empty($settings['server_name']))
            throw new Exception("Missing server_name parameter");

        if (empty($settings['site_url']))
            throw new Exception("Missing site_url parameter");

        if (empty($settings['home_url']))
            throw new Exception("Missing home_url parameter");

        if (empty($settings['root']))
            throw new Exception("Missing root parameter");

        if (empty($settings['db_host']))
            throw new Exception("Missing db_host parameter");

        if (empty($settings['db_name']))
            throw new Exception("Missing db_name parameter");

        if (empty($settings['db_user']))
            throw new Exception("Missing db_user parameter");

        if (!isset($settings['db_password']))
            throw new Exception("Missing db_password parameter");

        if (!isset($settings['table_prefix']))
            throw new Exception("Missing table_prefix parameter");

        if (!isset($settings['send_stats']))
            throw new Exception("Missing send_stats parameter");

        if (!isset($settings['verify']))
            throw new Exception("Missing verify parameter");

        if (!isset($settings['metadata']))
            throw new Exception("Missing metadata parameter");

        if (!isset($settings['is_wpe']))
            throw new Exception("Missing is_wpe parameter");

        if (!isset($settings['users']))
            throw new Exception("Missing users parameter");

        if (!isset($settings['auth_cookie_name']))
            throw new Exception("Missing auth_cookie_name parameter");

        if (!isset($settings['aes_key']))
            throw new Exception("Missing aes_key parameter");

        if (!isset($settings['aes_iv']))
            throw new Exception("Missing aes_iv parameter");

        $result = array(
            array(
                'class' => 'AnyWay_Tasks_EstimateFs',
                'state' => array(
                    'root' => $settings['root'],
                    'exclude' => isset($settings['exclude'])
                        ? $settings['exclude']
                        : array(),
                )
            ),
            array(
                'class' => 'AnyWay_Tasks_EstimateDb',
                'state' => array(
                    'db_host' => $settings['db_host'],
                    'db_name' => $settings['db_name'],
                    'db_user' => $settings['db_user'],
                    'db_password' => $settings['db_password']
                )
            ),
            array(
                'class' => 'AnyWay_Tasks_EstimateBootstrap',
                'state' => array()
            ),
            array(
                'class' => 'AnyWay_Tasks_WordpressBootstrap',
                'state' => array(
                    'filename' => $settings['filename'],
                    'source' => $settings['phpsource'],
                    'server_name' => $settings['server_name'],
                    'site_url' => $settings['site_url'],
                    'home_url' => $settings['home_url'],
                    'root' => $settings['root'],
                    'db_host' => $settings['db_host'],
                    'db_name' => $settings['db_name'],
                    'db_user' => $settings['db_user'],
                    'db_password' => $settings['db_password'],
                    'table_prefix' => $settings['table_prefix'],
                    'send_stats' => $settings['send_stats'],
                    'users' => $settings['users'],
                    'auth_cookie_name' => $settings['auth_cookie_name'],
                    'aes_key' => $settings['aes_key'],
                    'aes_iv' => $settings['aes_iv'],
                    'metadata' => $settings['metadata'],
                    'is_wpe' => $settings['is_wpe'],
                )
            ),
            array(
                'class' => 'AnyWay_Tasks_Compress',
                'state' => array(
                    'filename' => $settings['filename'],
                    'section' => AnyWay_Constants::CODE_SECTION,
                    'directory' => array(
                        'root' => $settings['root'],
                        'exclude' => isset($settings['exclude'])
                            ? $settings['exclude']
                            : array()
                    )
                )
            ),
            array(
                'class' => 'AnyWay_Tasks_Mysqldump',
                'state' => array(
                    'filename' => $settings['filename'],
                    'db_host' => $settings['db_host'],
                    'db_name' => $settings['db_name'],
                    'db_user' => $settings['db_user'],
                    'db_password' => $settings['db_password']
                )
            )
        );

        if ($settings['verify']) {
            $result[] = array(
                'class' => 'AnyWay_Tasks_Decompress',
                'state' => array(
                    'adapter' => array(
                        'class' => 'AnyWay_RestoreTarget_Verify'
                    ),
                    'filename' => $settings['filename'],
                    'section' => AnyWay_Constants::CODE_SECTION,
                    'exclude' => array()
                )
            );

            $result[] = array(
                'class' => 'AnyWay_Tasks_Mysqlrestore',
                'state' => array(
                    'filename' => $settings['filename'],
                    'db_host' => $settings['db_host'],
                    'db_name' => $settings['db_name'],
                    'db_user' => $settings['db_user'],
                    'db_password' => $settings['db_password'],
                    'replacements' => array(),
                    'verifyOnly' => true
                )
            );
        }

        return $result;
    }
}
