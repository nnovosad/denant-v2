<?php

class AnyWay_Helper_Filesystem
{
    const TEST_FILE_NAME = '.anyway';

    public static function check($settings = array())
    {
        if (!isset($settings['root']) || !($root = $settings['root'])) {
            throw new Exception("root is missing");
        }

        if (@touch($root . '/' . static::TEST_FILE_NAME)) {
            @unlink($root . '/' . static::TEST_FILE_NAME);
            return array(
                'root' => $root
            );
        } else {
            throw new Exception("Directory " . $root . " is not writeable");
        }
    }
}