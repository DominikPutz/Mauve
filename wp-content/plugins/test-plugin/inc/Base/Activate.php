<?php
/**
 * @package Test-Plugin
 */

 namespace Inc\Base;

class Activate
{
	public static function activate()
    {
		flush_rewrite_rules();

        $default = array();

        if ( ! get_option( 'mauve_plugin' ) ) {
            update_option( 'mauve_plugin', $default );
        }

        if ( ! get_option( 'mauve_plugin_cpt' ) ) {
            update_option( 'mauve_plugin_cpt', $default );
        }

        if ( ! get_option( 'mauve_plugin_taxonomy' ) ) {
            update_option( 'mauve_plugin_taxonomy', $default );
        }
	}
}
