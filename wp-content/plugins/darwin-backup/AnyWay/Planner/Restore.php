<?php

class AnyWay_Planner_Restore
{
    public static function buildInitialPlan($settings = array())
    {
        if (empty($settings['adapter']))
            throw new Exception("Adapter is missing");

        if (empty($settings['filename']))
            throw new Exception("Missing filename parameter");

        if (empty($settings['db_host']))
            throw new Exception("Missing db_host parameter");

        if (empty($settings['db_name']))
            throw new Exception("Missing db_name parameter");

        if (empty($settings['db_user']))
            throw new Exception("Missing db_user parameter");

        if (empty($settings['db_password']))
            throw new Exception("Missing db_password parameter");

        $plan = array(
            array(
                'class' => 'AnyWay_Tasks_EstimatePhpEmbeddedFs',
                'state' => array(
                    'filename' => $settings['filename'],
                )
            ),
            array(
                'class' => 'AnyWay_Tasks_Decompress',
                'state' => array(
                    'adapter' => $settings['adapter'],
                    'filename' => $settings['filename'],
                    'section' => AnyWay_Constants::CODE_SECTION,
                    'exclude' => $settings['exclude']
                )
            ),
            array(
                'class' => 'AnyWay_Tasks_Mysqlrestore',
                'state' => array(
                    'filename' => $settings['filename'],
                    'db_host' => $settings['db_host'],
                    'db_name' => $settings['db_name'],
                    'db_user' => $settings['db_user'],
                    'db_password' => $settings['db_password'],
                    'replacements' => $settings['replacements']
                )
            ),
            array(
                'class' => 'AnyWay_Tasks_WordpressCleanup',
                'state' => array(
                    'adapter' => $settings['adapter'],
                )
            )
        );
        return $plan;
    }
}
