<?php
namespace Hurrytimer;

/**
 * Fired during plugin activation
 *
 * @link       http://nabillemsieh.com
 * @since      1.0.0
 *
 * @package    Hurrytimer
 * @subpackage Hurrytimer/includes
 */

/**
 * Fired during plugin activation.
 *
 * This class defines all code necessary to run during the plugin's activation.
 *
 * @since      1.0.0
 * @package    Hurrytimer
 * @subpackage Hurrytimer/includes
 * @author     Nabil Lemsieh <contact@nabillemsieh.com>
 */
class Activator
{

    /**
     * Short Description. (use period)
     *
     * Long Description.
     *
     * @since    1.0.0
     */
    public static function activate()
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

        add_option('hurrytimer_db_version', HURRYT_DB_VERSION);
        add_option(HURRYT_OPTION_VERSION_KEY, HURRYT_VERSION);

    }

}
