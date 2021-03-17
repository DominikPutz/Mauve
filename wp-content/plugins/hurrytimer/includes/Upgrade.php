<?php

namespace Hurrytimer;

class Upgrade
{
    static $instance;

    /**
     * @return Upgrade
     */
    public static function getInstance()
    {
        if (!static::$instance) {
            static::$instance = new static();
        }

        return static::$instance;
    }

    /**
     * Run upgrade.
     *
     * @return void
     */
    public function run()
    {
        $this->before();
        $this->upgradeDb();
        $this->upgradeCore();
        $this->buildCssFile();
        $this->after();
    }

    /**
     * Run before upgrade.
     *
     * @return void
     */
    public function before()
    {
        if (!$this->getInstalledPluginVersion()) {
            Activator::activate();
        }
    }

    /**
     * After upgrade
     *
     * @return void
     */
    public function after()
    {
        $this->updatePluginVersion();
    }

    /**
     * Upgrade code features.
     *
     * @return void
     */
    public function upgradeCore()
    {
        $this->backwardCompat();
    }

    /**
     * Re-build public CSS file.
     *
     * @return void
     */
    public function buildCssFile()
    {
        if (!$this->isCssBuilt()) {
            Campaign::generate_css();
        }
    }

    /**
     * Returns true if the public CSS file exits already.
     *
     * @return boolean
     */
    public function isCssBuilt()
    {
        return file_exists(HURRYT_DIR . 'assets/css/hurrytimer.css');
    }


    /**
     * Upgrade or created table.
     *
     * @return void
     */
    private function upgradeDb()
    {
        $ver = $this->getInstalledDbVersion();

        if (!$ver || $ver != HURRYT_DB_VERSION) {
            $this->createTables();
            $this->updateDbVersion();
        }
    }

    /**
     * Create custom db tables.
     *
     * @return void
     */
    public function createTables()
    {
        global $wpdb;

        $table = "{$wpdb->prefix}hurrytimer_evergreen";
        $charset_collate = $wpdb->get_charset_collate();

        $sql = "CREATE TABLE {$table} (
              id bigint(20) NOT NULL AUTO_INCREMENT,
              countdown_id bigint(20) unsigned NOT NULL,
              client_ip_address varchar(50) NOT NULL,
              expired tinyint(1) unsigned DEFAULT NULL,
              client_expires_at bigint(20) unsigned NOT NULL,
              destroy_at timestamp NULL DEFAULT NULL,
              PRIMARY KEY (id)
            ) {$charset_collate}";

        require_once ABSPATH . 'wp-admin/includes/upgrade.php';

        dbDelta($sql);
    }

    /**
     * Returns installed plugin version.
     *
     * @return string
     */
    private function getInstalledPluginVersion()
    {
        return get_option(HURRYT_OPTION_VERSION_KEY);
    }

    /**
     * Update plugin version.
     *
     * @return void
     */
    private function updatePluginVersion()
    {
        update_option(HURRYT_OPTION_VERSION_KEY, HURRYT_VERSION);
    }

    /**
     * Returns installed db version.
     *
     * @return string
     */
    private function getInstalledDbVersion()
    {
        return get_option('hurrytimer_db_version');
    }

    /**
     * Save new db version.
     *
     * @return void
     */
    private function updateDbVersion()
    {
        update_option('hurrytimer_db_version', HURRYT_DB_VERSION);
    }

    /**
     * Run global backward compatibility if applicable.
     *
     * @return void
     */
    private function backwardCompat()
    {
        $ver = $this->getInstalledPluginVersion();

        if (version_compare($ver, '2.0.0', '<')) {
            $this->buildCssFile();
        }

    }
}
