<?php
namespace um_ext\um_mailchimp\admin\core;


if ( ! defined( 'ABSPATH' ) ) exit;


/**
 * This class handles all functions that changes data structures and moving files
 */
class Upgrade {
	var $update_versions;
	var $packages_dir;


	function __construct() {
		$this->packages_dir = plugin_dir_path( __FILE__ ) . 'packages' . DIRECTORY_SEPARATOR;

		$last_version_upgrade = $this->get_last_version_upgrade();

		if ( ! $last_version_upgrade || version_compare( $last_version_upgrade, um_mailchimp_version, '<' ) ) {
			add_action( 'admin_init', array( $this, 'packages' ), 10 );
		}
	}


	/**
	 * Load packages
	 */
	public function packages() {
		foreach( $this->get_update_versions() as $update_version=>$filename ) {

			if ( version_compare( $update_version, $this->get_last_version_upgrade(), '<=' ) )
				continue;

			if ( version_compare( $update_version, um_mailchimp_version, '>' ) )
				continue;

			$file_path = $this->packages_dir . $filename;

			if ( file_exists( $file_path ) ) {
				include_once( $file_path );
				$this->set_last_version_upgrade( $update_version );
			}
		}

		$this->set_last_version_upgrade( um_mailchimp_version );
	}


	/**
	 * Parse packages dir for packages files
	 */
	function get_update_versions() {
		$update_versions = array();
		$handle = opendir( $this->packages_dir );
		while ( false !== ( $filename = readdir( $handle ) ) ) {
			if ( $filename != '.' && $filename != '..' ) continue;
			$version = preg_replace( '/(.*?)\.php/i', '$1', $filename );

			$update_versions[ $version ] = $filename;
		}
		closedir( $handle );

		uksort( $update_versions, array( &$this, 'version_compare_sort' ) );

		return $update_versions;
	}


	/**
	 * Sort versions by version compare function
	 * @param $a
	 * @param $b
	 * @return mixed
	 */
	function version_compare_sort( $a, $b ) {
		return version_compare( $a, $b );
	}


	function get_last_version_upgrade() {
		return get_option( 'um_mailchimp_last_version_upgrade', '0.0.0' );
	}


	function set_last_version_upgrade( $version ) {
		update_option( 'um_mailchimp_last_version_upgrade', $version );
	}

}