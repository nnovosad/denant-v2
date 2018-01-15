<?php

class AnyWay_Wordpress_StateProvider extends AnyWay_StateProvider_FileSystem
{
    const OPTION = 'state-dir-type';

    public function storageDir() {

        $type = AnyWay_Wordpress_Settings::get(self::OPTION);
        $detector = new AnyWay_Wordpress_WritableDirectoryDetector();
        $allowedTypes = array(
            AnyWay_Wordpress_WritableDirectoryDetector::WP_CONTENT_DIR_STORAGE,
            AnyWay_Wordpress_WritableDirectoryDetector::WP_UPLOADS_DIR_STORAGE,
            AnyWay_Wordpress_WritableDirectoryDetector::PLUGIN_DIR_STORAGE,
            AnyWay_Wordpress_WritableDirectoryDetector::TMP_DIR_STORAGE,
            AnyWay_Wordpress_WritableDirectoryDetector::UPLOAD_TMP_DIR_STORAGE
        );

        if (false !== ($newType = $detector->detect($type, $allowedTypes))) {
            if ($newType !== $type) {
                AnyWay_Wordpress_Settings::set(self::OPTION, $newType);
            }
            return $detector->getBaseDir($newType);
        }

        throw new Exception("Unable to find writable directory to store state");
    }

    public function destruct()
    {
        parent::destruct();

        // cleaning out outdated state files, don't call $this->_getTempDir()
        // as wp_upload_dir() and cache is not available during destruct !!!!
        $files = glob($this->dir . DIRECTORY_SEPARATOR . $this->prefix . "*" . $this->postfix);

        foreach ($files as $file) {
            if (is_file($file)) {
                if (time() - filemtime($file) >= ANYWAY_SESSION_OUTDATED_INTERVAL) {
                    @unlink($file);
                }
            }
        }
    }
}