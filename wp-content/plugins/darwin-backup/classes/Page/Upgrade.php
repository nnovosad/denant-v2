<?php

class AnyWay_Wordpress_Page_Upgrade extends AnyWay_Wordpress_Page_Base
{
    static $slug = 'darwin-backup-upgrade';

    public $order = null;

    public function init()
    {
        parent::init();
        $this->add_action('core_upgrade_preamble', array($this, 'display'));

        /*
        add_action('admin_action_update-selected', array($this, 'display'));
        add_action('admin_action_update-selected-themes', array($this, 'display'));
        add_action('admin_action_do-plugin-upgrade', array($this, 'display'));
        add_action('admin_action_do-theme-upgrade', array($this, 'display'));
        add_action('admin_action_do-theme-upgrade', array($this, 'display'));
        add_action('admin_action_upgrade-plugin', array($this, 'display'));
        add_action('admin_action_upgrade-theme', array($this, 'display'));
        add_action('admin_action_do-core-upgrade', array($this, 'display'));
        add_action('admin_action_do-core-reinstall', array($this, 'display'));

        add_action('core_upgrade_preamble', array($this, 'log'));
        add_action('admin_action_update-selected', array($this, 'log'));
        add_action('admin_action_update-selected-themes', array($this, 'log'));
        add_action('admin_action_do-plugin-upgrade', array($this, 'log'));
        add_action('admin_action_do-theme-upgrade', array($this, 'log'));
        add_action('admin_action_do-theme-upgrade', array($this, 'log'));
        add_action('admin_action_upgrade-plugin', array($this, 'log'));
        add_action('admin_action_upgrade-theme', array($this, 'log'));
        add_action('admin_action_do-core-upgrade', array($this, 'log'));
        add_action('admin_action_do-core-reinstall', array($this, 'log'));
        */
    }

    public function display()
    {
        wp_enqueue_style("anyway"); // will pull all the dependencies
        wp_enqueue_script(static::$slug); // will pull all the dependencies

        echo $this->render('upgrade.php', array(
            'settings' => AnyWay_Wordpress_Settings::get()
        ));
    }
}

return new AnyWay_Wordpress_Page_Upgrade();
