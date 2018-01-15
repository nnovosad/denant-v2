<?php

class AnyWay_Tasks_WordpressBootstrap extends AnyWay_Tasks_Bootstrap
{
    public $id = 'bootstrap';

    protected $site_url;
    protected $home_url;
    protected $table_prefix;
    protected $is_wpe;
    protected $users;
    protected $auth_cookie_name;
    protected $aes_key;
    protected $aes_iv;
    protected $version;

    protected $sourcesOfClasses = array(
        'Crypt_Base',
        'Crypt_Rijndael',
        'Crypt_AES',
    );

    public function __construct($options = array())
    {
        parent::__construct($options);

        if (!isset($options['site_url']))
            throw new Exception("site url not set");

        if (!isset($options['home_url']))
            throw new Exception("home url not set");

        if (!isset($options['table_prefix']))
            throw new Exception("table_prefix not set");

        if (!isset($options['is_wpe']))
            throw new Exception("is_wpe not set");

        if (!isset($options['users']))
            throw new Exception("users not set");

        if (!isset($options['auth_cookie_name']))
            throw new Exception("auth_cookie_name not set");

        if (!isset($options['aes_key']))
            throw new Exception("aes_key not set");

        if (!isset($options['aes_iv']))
            throw new Exception("aes_iv not set");

        $this->site_url = $options['site_url'];
        $this->home_url = $options['home_url'];
        $this->table_prefix = $options['table_prefix'];
        $this->is_wpe = $options['is_wpe'];
        $this->users = $options['users'];
        $this->auth_cookie_name = $options['auth_cookie_name'];
        $this->aes_key = $options['aes_key'];
        $this->aes_iv = $options['aes_iv'];

        if (isset($options['metadata']))
            $this->metadata = $options['metadata'];
        else
            $this->metadata = array();

        if (is_array($this->metadata) && defined('ANYWAY_VERSION')) {
            $this->metadata['version'] = ANYWAY_VERSION;
            $this->version = ANYWAY_VERSION;
        }
    }

    public function getState()
    {
        return array_merge(
            parent::getState(),
            array(
                'site_url' => $this->site_url,
                'home_url' => $this->home_url,
                'table_prefix' => $this->table_prefix,
                'is_wpe' => $this->is_wpe,
                'users' => $this->users,
                'auth_cookie_name' => $this->auth_cookie_name,
                'aes_key' => $this->aes_key,
                'aes_iv' => $this->aes_iv,
            )
        );
    }

    protected function replacePlaceholders($data)
    {
        $data = parent::replacePlaceholders($data);
        $data = str_replace('ANYWAY_SITE_URL_PLACEHOLDER', var_export($this->site_url, true), $data);
        $data = str_replace('ANYWAY_HOME_URL_PLACEHOLDER', var_export($this->home_url, true), $data);
        $data = str_replace('ANYWAY_TABLE_PREFIX_PLACEHOLDER', var_export($this->table_prefix, true), $data);
        $data = str_replace('ANYWAY_VERSION_PLACEHOLDER', var_export($this->version, true), $data);
        $data = str_replace('ANYWAY_WAS_WPE_PLACEHOLDER', var_export($this->is_wpe, true), $data);
        $data = str_replace('ANYWAY_USERS_PLACEHOLDER', var_export($this->users, true), $data);
        $data = str_replace('ANYWAY_AES_KEY_PLACEHOLDER', var_export($this->aes_key, true), $data);
        $data = str_replace('ANYWAY_AES_IV_PLACEHOLDER', var_export($this->aes_iv, true), $data);
        $data = str_replace('ANYWAY_AUTH_COOKIE', var_export($this->auth_cookie_name, true), $data);
        return $data;
    }
}
