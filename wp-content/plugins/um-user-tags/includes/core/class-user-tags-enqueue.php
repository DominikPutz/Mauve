<?php
namespace um_ext\um_user_tags\core;

if ( ! defined( 'ABSPATH' ) ) exit;


/**
 * Class User_Tags_Enqueue
 * @package um_ext\um_user_tags\core
 */
class User_Tags_Enqueue {


	/**
	 * User_Tags_Enqueue constructor.
	 */
	function __construct() {
		$priority = apply_filters( 'um_user_tags_enqueue_priority', 0 );

		add_action( 'wp_enqueue_scripts',  array( &$this, '_enqueue_scripts' ), $priority );
		add_action( 'admin_enqueue_scripts',  array( &$this, '_enqueue_scripts' ), $priority );
		add_action('wp_footer', array(&$this,'enable_tag_adding'),100);
	}


	/**
	 * Enqueue scripts
	 */
	function _enqueue_scripts() {
		$suffix = ( defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG || defined( 'UM_SCRIPT_DEBUG' ) ) ? '' : '.min';
		wp_register_script( 'um-user-tags', um_user_tags_url . 'assets/js/um-user-tags' . $suffix . '.js', array( 'jquery', 'select2', 'um_tipsy' ), um_user_tags_version, true );
		wp_register_script( 'um-user-tags-members', um_user_tags_url . 'assets/js/um-user-tags-members' . $suffix . '.js', array( 'jquery', 'wp-hooks', 'um_members' ), um_user_tags_version, true );
		wp_register_style( 'um-user-tags', um_user_tags_url . 'assets/css/um-user-tags' . $suffix . '.css', array( 'select2', 'um_tipsy' ), um_user_tags_version );
	}
	
	
	/*
	Change select2 if user can add tags
	*/
	function enable_tag_adding(){
		if ( um_user( 'user_tags_can_add' ) ){
		?>
		<script>
		jQuery(document).ready(function(){
			jQuery('.um-field-type_user_tags select').select2("destroy");
			jQuery(".um-field-user_tags select").each(function(){
					var $this = jQuery(this);
					$this.select2({
						tags: true,
						allowClear: true,
						minimumResultsForSearch: 10,
						maximumSelectionSize: parseInt( $this.attr('data-maxsize') )
					});
			});

		});
		</script>
		<?php
		}
	}
}
