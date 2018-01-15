<?php
/**
 * Plugin Name: Darwin Backup
 * Plugin URI: https://wordpress.org/plugins/darwin-backup/
 * Plugin Author: Aleksandr Guidrevitch
 * Version: 1.2.25
 * Author: DarwinApps, Aleksandr Guidrevitch
 * Author URI: http://darwinapps.com/
 * Description: One click recovery from the worst-case scenarios. Backup, restore and duplicate sites of any size.
 */

define('NOT_OBFUSCATED', true);
define('ANYWAY_VERSION', '1.2.25');
defined('ANYWAY_TEXTDOMAIN') or define('ANYWAY_TEXTDOMAIN', 'darwin-backup');
defined('ANYWAY_WORDPRESS_OPTION') or define('ANYWAY_WORDPRESS_OPTION', 'darwin_backup');
defined('ANYWAY_SESSION_OUTDATED_INTERVAL') or define('ANYWAY_SESSION_OUTDATED_INTERVAL', 7200); // How long a backup might take
defined('ANYWAY_AUTH_COOKIE') or define('ANYWAY_AUTH_COOKIE', 'DARWINAUTH');


    define('ANYWAY_CLASSDIR', __DIR__);
    define('ANYWAY_BASEDIR', __DIR__);
    define('ANYWAY_BASEFILE', __FILE__);
    define('ANYWAY_RESTOREFILE', __DIR__ . '/restore/restore.php');
    defined('ANYWAY_ENVIRONMENT') or define('ANYWAY_ENVIRONMENT', 'production');
    

require(ANYWAY_CLASSDIR . '/AnyWay/Interface/IStateProvider.php');
require(ANYWAY_CLASSDIR . '/AnyWay/Interface/IRestoreTarget.php');
require(ANYWAY_CLASSDIR . '/AnyWay/Interface/IEventEmitter.php');
require(ANYWAY_CLASSDIR . '/AnyWay/Interface/ITask.php');
require(ANYWAY_CLASSDIR . '/AnyWay/Constants.php');
require(ANYWAY_CLASSDIR . '/AnyWay/EventEmitter.php');
require(ANYWAY_CLASSDIR . '/AnyWay/StateProvider/FileSystem.php');
require(ANYWAY_CLASSDIR . '/AnyWay/PhpEmbeddedFs.php');
require(ANYWAY_CLASSDIR . '/AnyWay/PhpEmbeddedFsArchive.php');
require(ANYWAY_CLASSDIR . '/AnyWay/DirectoryTraversal.php');
require(ANYWAY_CLASSDIR . '/AnyWay/FTP/Base.php');
require(ANYWAY_CLASSDIR . '/AnyWay/FTP/Pure.php');
require(ANYWAY_CLASSDIR . '/AnyWay/FTP/Sockets.php');
require(ANYWAY_CLASSDIR . '/AnyWay/RestoreTarget/FileSystem.php');
require(ANYWAY_CLASSDIR . '/AnyWay/RestoreTarget/FTP.php');
require(ANYWAY_CLASSDIR . '/AnyWay/RestoreTarget/Verify.php');
require(ANYWAY_CLASSDIR . '/AnyWay/Tasks/EstimateFs.php');
require(ANYWAY_CLASSDIR . '/AnyWay/Tasks/EstimatePhpEmbeddedFs.php');
require(ANYWAY_CLASSDIR . '/AnyWay/Tasks/EstimateDb.php');
require(ANYWAY_CLASSDIR . '/AnyWay/Tasks/Compress.php');
require(ANYWAY_CLASSDIR . '/AnyWay/Tasks/Mysqldump.php');
require(ANYWAY_CLASSDIR . '/AnyWay/Tasks/Bootstrap.php');
require(ANYWAY_CLASSDIR . '/AnyWay/Tasks/WordpressBootstrap.php');
require(ANYWAY_CLASSDIR . '/AnyWay/Tasks/EstimateBootstrap.php');
require(ANYWAY_CLASSDIR . '/AnyWay/Tasks/Decompress.php');
require(ANYWAY_CLASSDIR . '/AnyWay/Tasks/Mysqlrestore.php');
require(ANYWAY_CLASSDIR . '/AnyWay/Tasks/CopyFile.php');
require(ANYWAY_CLASSDIR . '/AnyWay/Tasks/RenameFile.php');
require(ANYWAY_CLASSDIR . '/AnyWay/Tasks/Finalize.php');
require(ANYWAY_CLASSDIR . '/AnyWay/Tasks/WordpressCleanup.php');
require(ANYWAY_CLASSDIR . '/AnyWay/Tasks/WordpressMail.php');
require(ANYWAY_CLASSDIR . '/AnyWay/Tasks/Retain.php');
require(ANYWAY_CLASSDIR . '/AnyWay/QueueManager.php');
require(ANYWAY_CLASSDIR . '/AnyWay/Planner/WordpressBackup.php');
require(ANYWAY_CLASSDIR . '/AnyWay/Planner/Restore.php');
require(ANYWAY_CLASSDIR . '/AnyWay/Planner/Verify.php');
require(ANYWAY_CLASSDIR . '/AnyWay/Runner/Base.php');
require(ANYWAY_CLASSDIR . '/AnyWay/Runner/Restore.php');
require(ANYWAY_CLASSDIR . '/AnyWay/Runner/Verify.php');
require(ANYWAY_CLASSDIR . '/AnyWay/Crypt/PasswordHash.php');
require(ANYWAY_CLASSDIR . '/AnyWay/Crypt/Base.php');
require(ANYWAY_CLASSDIR . '/AnyWay/Crypt/Rijndael.php');
require(ANYWAY_CLASSDIR . '/AnyWay/Crypt/AES.php');
require(ANYWAY_CLASSDIR . '/AnyWay/Helper/Mysql.php');
require(ANYWAY_CLASSDIR . '/AnyWay/Helper/Server.php');
require(ANYWAY_CLASSDIR . '/AnyWay/Helper/Filesystem.php');
require(ANYWAY_CLASSDIR . '/AnyWay/Helper/FTP.php');
require(__DIR__ . '/classes/Settings.php');
require(__DIR__ . '/classes/WritableDirectoryDetector.php');
require(__DIR__ . '/classes/StateProvider.php');
require(__DIR__ . '/classes/Runner.php');
require(__DIR__ . '/classes/Plugin.php');
require(__DIR__ . '/classes/Page/Base.php');

class AnyWay_Wordpress
{
    public $pages = array();
    public $tabs = array();
    public $targets = array();
    public $auto_order = 999;

    public function __construct()
    {

        if (is_multisite()) {
            add_action('network_admin_menu', array($this, 'menu'));
        } else {
            add_action('admin_menu', array($this, 'menu'));
        }
        add_action('admin_init', array($this, 'init'));
        add_action('admin_enqueue_styles', array($this, 'admin_enqueue_styles'));
        add_action('admin_enqueue_scripts', array($this, 'admin_enqueue_scripts'));

        $this->load();
    }

    public function admin_enqueue_styles() {
        global $wp_version;
        if ($wp_version < '3.8') {
            wp_enqueue_style("anyway-fallback", plugins_url('/css/fallback.css', ANYWAY_BASEFILE), null, ANYWAY_VERSION);
        }
    }

    public function admin_enqueue_scripts() {
        wp_enqueue_script("anyway-dismissible", plugins_url('/js/dismissible.js', ANYWAY_BASEFILE), array('jquery'), ANYWAY_VERSION);
    }

    public function load()
    {
        foreach (glob(__DIR__ . '/classes/Page/*') as $filename) {
            if ('Base.php' !== basename($filename)) {
                $page = require($filename);

                if (!$page instanceof AnyWay_Wordpress_Page_Base)
                    throw new Exception(get_class($page) . " is not of AnyWay_Wordpress_Page_Base");

                if (is_null($page->order))
                    $page->order = $this->auto_order++;

                //if (!$page->order)
                //    throw new Exception("Page order not set for " . get_class($page));

                $this->pages[$page->order] = $page;
            }
        }
        ksort($this->pages);
    }

    public function init()
    {
        foreach ($this->pages as $page) {
            /* @var AnyWay_Wordpress_Page_Base $page */
            $page->init();
        }
    }

    public function windows_not_supported()
    {
        printf('<div class="wrap"><div class="error"><p>Sorry to say that, but plugin does not currently work on Microsoft Windows. Not yet.</p></div></div>');
    }

    public function no_storage_dir()
    {
        printf('<div class="wrap"><div class="error"><p>Unable to find writable directory to store backups. Sorry.</p></div></div>');
    }

    public function menu()
    {
        if (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN') {
            add_menu_page(
                'Darwin Backup',
                'Darwin Backup',
                'update_core',
                'does-not-work',
                array($this, 'windows_not_supported'),
                'data:image/svg+xml;base64,PD94bWwgdmVyc2lvbj0iMS4wIiBlbmNvZGluZz0iVVRGLTgiIHN0YW5kYWxvbmU9Im5vIj8+PHN2ZyB3aWR0aD0iNDBweCIgaGVpZ2h0PSI0MHB4IiB2aWV3Qm94PSIwIDAgNDAgNDAiIHZlcnNpb249IjEuMSIgeG1sbnM9Imh0dHA6Ly93d3cudzMub3JnLzIwMDAvc3ZnIiB4bWxuczp4bGluaz0iaHR0cDovL3d3dy53My5vcmcvMTk5OS94bGluayI+ICAgICAgICA8dGl0bGU+QXJ0Ym9hcmQ8L3RpdGxlPiAgICA8ZGVzYz5DcmVhdGVkIHdpdGggU2tldGNoLjwvZGVzYz4gICAgPGRlZnM+PC9kZWZzPiAgICA8ZyBpZD0iUGFnZS0xIiBzdHJva2U9Im5vbmUiIHN0cm9rZS13aWR0aD0iMSIgZmlsbD0ibm9uZSIgZmlsbC1ydWxlPSJldmVub2RkIj4gICAgICAgIDxnIGlkPSJBcnRib2FyZCIgZmlsbD0iI0ZDMjM3MyI+ICAgICAgICAgICAgPGcgaWQ9ImRhcndpbmFwcHNfMV8iIHRyYW5zZm9ybT0idHJhbnNsYXRlKDAuMDAwMDAwLCAxMy4wMDAwMDApIj4gICAgICAgICAgICAgICAgPHBhdGggZD0iTTMzLjY1NjY4NDksMC4wNDUzOTEzMDQzIEwzMy42NTY2ODQ5LDE0LjgyMTUyMTcgTDM5LjUyNDkzMTEsMTQuODIxNTIxNyBMMzkuNTI0OTMxMSwwLjA0NTM5MTMwNDMgTDMzLjY1NjY4NDksMC4wNDUzOTEzMDQzIEwzMy42NTY2ODQ5LDAuMDQ1MzkxMzA0MyBMMzMuNjU2Njg0OSwwLjA0NTM5MTMwNDMgTDMzLjY1NjY4NDksMC4wNDUzOTEzMDQzIEwzMy42NTY2ODQ5LDAuMDQ1MzkxMzA0MyBaIE0xNy4xMDI1NTM1LDEwLjE5MDM0NzggTDIxLjAzNTI2NzUsMTQuNDM4MjE3NCBMMzIuNTEzNjAzNCw0LjYwMDkxMzA0IEwyOC41ODA4ODk1LDAuMzUzMDQzNDc4IEwxNy4xMDI1NTM1LDEwLjE5MDM0NzggTDE3LjEwMjU1MzUsMTAuMTkwMzQ3OCBMMTcuMTAyNTUzNSwxMC4xOTAzNDc4IEwxNy4xMDI1NTM1LDEwLjE5MDM0NzggTDE3LjEwMjU1MzUsMTAuMTkwMzQ3OCBaIE0wLjAyNDQwMjg2MjUsOC44NzUyNjA4NyBMMi4zODM3NzQzNiwxNC4wOTAyMTc0IEwxNi40ODA5MjI3LDguMTg4MDg2OTYgTDE0LjEyMTU1MTIsMi45NzMxMzA0MyBMMC4wMjQ0MDI4NjI1LDguODc1MjYwODcgTDAuMDI0NDAyODYyNSw4Ljg3NTI2MDg3IEwwLjAyNDQwMjg2MjUsOC44NzUyNjA4NyBMMC4wMjQ0MDI4NjI1LDguODc1MjYwODcgTDAuMDI0NDAyODYyNSw4Ljg3NTI2MDg3IFoiIGlkPSJTaGFwZSI+PC9wYXRoPiAgICAgICAgICAgIDwvZz4gICAgICAgIDwvZz4gICAgPC9nPjwvc3ZnPg=='
            );
            return;
        }

        $storageDir = null;
        try {
            $runner = new AnyWay_Wordpress_Runner();
            $storageDir = $runner->storageDir();
        } catch (Exception $e) {
            error_log($e);
        }

        if (!$storageDir) {
            add_menu_page(
                'Darwin Backup',
                'Darwin Backup',
                'update_core',
                'does-not-work',
                array($this, 'no_storage_dir'),
                'data:image/svg+xml;base64,PD94bWwgdmVyc2lvbj0iMS4wIiBlbmNvZGluZz0iVVRGLTgiIHN0YW5kYWxvbmU9Im5vIj8+PHN2ZyB3aWR0aD0iNDBweCIgaGVpZ2h0PSI0MHB4IiB2aWV3Qm94PSIwIDAgNDAgNDAiIHZlcnNpb249IjEuMSIgeG1sbnM9Imh0dHA6Ly93d3cudzMub3JnLzIwMDAvc3ZnIiB4bWxuczp4bGluaz0iaHR0cDovL3d3dy53My5vcmcvMTk5OS94bGluayI+ICAgICAgICA8dGl0bGU+QXJ0Ym9hcmQ8L3RpdGxlPiAgICA8ZGVzYz5DcmVhdGVkIHdpdGggU2tldGNoLjwvZGVzYz4gICAgPGRlZnM+PC9kZWZzPiAgICA8ZyBpZD0iUGFnZS0xIiBzdHJva2U9Im5vbmUiIHN0cm9rZS13aWR0aD0iMSIgZmlsbD0ibm9uZSIgZmlsbC1ydWxlPSJldmVub2RkIj4gICAgICAgIDxnIGlkPSJBcnRib2FyZCIgZmlsbD0iI0ZDMjM3MyI+ICAgICAgICAgICAgPGcgaWQ9ImRhcndpbmFwcHNfMV8iIHRyYW5zZm9ybT0idHJhbnNsYXRlKDAuMDAwMDAwLCAxMy4wMDAwMDApIj4gICAgICAgICAgICAgICAgPHBhdGggZD0iTTMzLjY1NjY4NDksMC4wNDUzOTEzMDQzIEwzMy42NTY2ODQ5LDE0LjgyMTUyMTcgTDM5LjUyNDkzMTEsMTQuODIxNTIxNyBMMzkuNTI0OTMxMSwwLjA0NTM5MTMwNDMgTDMzLjY1NjY4NDksMC4wNDUzOTEzMDQzIEwzMy42NTY2ODQ5LDAuMDQ1MzkxMzA0MyBMMzMuNjU2Njg0OSwwLjA0NTM5MTMwNDMgTDMzLjY1NjY4NDksMC4wNDUzOTEzMDQzIEwzMy42NTY2ODQ5LDAuMDQ1MzkxMzA0MyBaIE0xNy4xMDI1NTM1LDEwLjE5MDM0NzggTDIxLjAzNTI2NzUsMTQuNDM4MjE3NCBMMzIuNTEzNjAzNCw0LjYwMDkxMzA0IEwyOC41ODA4ODk1LDAuMzUzMDQzNDc4IEwxNy4xMDI1NTM1LDEwLjE5MDM0NzggTDE3LjEwMjU1MzUsMTAuMTkwMzQ3OCBMMTcuMTAyNTUzNSwxMC4xOTAzNDc4IEwxNy4xMDI1NTM1LDEwLjE5MDM0NzggTDE3LjEwMjU1MzUsMTAuMTkwMzQ3OCBaIE0wLjAyNDQwMjg2MjUsOC44NzUyNjA4NyBMMi4zODM3NzQzNiwxNC4wOTAyMTc0IEwxNi40ODA5MjI3LDguMTg4MDg2OTYgTDE0LjEyMTU1MTIsMi45NzMxMzA0MyBMMC4wMjQ0MDI4NjI1LDguODc1MjYwODcgTDAuMDI0NDAyODYyNSw4Ljg3NTI2MDg3IEwwLjAyNDQwMjg2MjUsOC44NzUyNjA4NyBMMC4wMjQ0MDI4NjI1LDguODc1MjYwODcgTDAuMDI0NDAyODYyNSw4Ljg3NTI2MDg3IFoiIGlkPSJTaGFwZSI+PC9wYXRoPiAgICAgICAgICAgIDwvZz4gICAgICAgIDwvZz4gICAgPC9nPjwvc3ZnPg=='
            );
            return;
        }

        /* $page_title, $menu_title, $capability, $menu_slug, $function = '', $icon_url = '', $position = NULL */
        foreach ($this->pages as $page) {
            if ($page instanceof AnyWay_Wordpress_Page_List) {
                $hook = add_menu_page(
                    AnyWay_Wordpress_Page_List::$title,
                    'Darwin Backup',
                    AnyWay_Wordpress_Page_List::$permissions,
                    AnyWay_Wordpress_Page_List::$slug,
                    null,
                    'data:image/svg+xml;base64,PD94bWwgdmVyc2lvbj0iMS4wIiBlbmNvZGluZz0iVVRGLTgiIHN0YW5kYWxvbmU9Im5vIj8+PHN2ZyB3aWR0aD0iNDBweCIgaGVpZ2h0PSI0MHB4IiB2aWV3Qm94PSIwIDAgNDAgNDAiIHZlcnNpb249IjEuMSIgeG1sbnM9Imh0dHA6Ly93d3cudzMub3JnLzIwMDAvc3ZnIiB4bWxuczp4bGluaz0iaHR0cDovL3d3dy53My5vcmcvMTk5OS94bGluayI+ICAgICAgICA8dGl0bGU+QXJ0Ym9hcmQ8L3RpdGxlPiAgICA8ZGVzYz5DcmVhdGVkIHdpdGggU2tldGNoLjwvZGVzYz4gICAgPGRlZnM+PC9kZWZzPiAgICA8ZyBpZD0iUGFnZS0xIiBzdHJva2U9Im5vbmUiIHN0cm9rZS13aWR0aD0iMSIgZmlsbD0ibm9uZSIgZmlsbC1ydWxlPSJldmVub2RkIj4gICAgICAgIDxnIGlkPSJBcnRib2FyZCIgZmlsbD0iI0ZDMjM3MyI+ICAgICAgICAgICAgPGcgaWQ9ImRhcndpbmFwcHNfMV8iIHRyYW5zZm9ybT0idHJhbnNsYXRlKDAuMDAwMDAwLCAxMy4wMDAwMDApIj4gICAgICAgICAgICAgICAgPHBhdGggZD0iTTMzLjY1NjY4NDksMC4wNDUzOTEzMDQzIEwzMy42NTY2ODQ5LDE0LjgyMTUyMTcgTDM5LjUyNDkzMTEsMTQuODIxNTIxNyBMMzkuNTI0OTMxMSwwLjA0NTM5MTMwNDMgTDMzLjY1NjY4NDksMC4wNDUzOTEzMDQzIEwzMy42NTY2ODQ5LDAuMDQ1MzkxMzA0MyBMMzMuNjU2Njg0OSwwLjA0NTM5MTMwNDMgTDMzLjY1NjY4NDksMC4wNDUzOTEzMDQzIEwzMy42NTY2ODQ5LDAuMDQ1MzkxMzA0MyBaIE0xNy4xMDI1NTM1LDEwLjE5MDM0NzggTDIxLjAzNTI2NzUsMTQuNDM4MjE3NCBMMzIuNTEzNjAzNCw0LjYwMDkxMzA0IEwyOC41ODA4ODk1LDAuMzUzMDQzNDc4IEwxNy4xMDI1NTM1LDEwLjE5MDM0NzggTDE3LjEwMjU1MzUsMTAuMTkwMzQ3OCBMMTcuMTAyNTUzNSwxMC4xOTAzNDc4IEwxNy4xMDI1NTM1LDEwLjE5MDM0NzggTDE3LjEwMjU1MzUsMTAuMTkwMzQ3OCBaIE0wLjAyNDQwMjg2MjUsOC44NzUyNjA4NyBMMi4zODM3NzQzNiwxNC4wOTAyMTc0IEwxNi40ODA5MjI3LDguMTg4MDg2OTYgTDE0LjEyMTU1MTIsMi45NzMxMzA0MyBMMC4wMjQ0MDI4NjI1LDguODc1MjYwODcgTDAuMDI0NDAyODYyNSw4Ljg3NTI2MDg3IEwwLjAyNDQwMjg2MjUsOC44NzUyNjA4NyBMMC4wMjQ0MDI4NjI1LDguODc1MjYwODcgTDAuMDI0NDAyODYyNSw4Ljg3NTI2MDg3IFoiIGlkPSJTaGFwZSI+PC9wYXRoPiAgICAgICAgICAgIDwvZz4gICAgICAgIDwvZz4gICAgPC9nPjwvc3ZnPg=='
                );
            }
            /* @var AnyWay_Wordpress_Page_Base $page */
            $page->menu();
        }
    }
}

$backup = new AnyWay_Wordpress();
