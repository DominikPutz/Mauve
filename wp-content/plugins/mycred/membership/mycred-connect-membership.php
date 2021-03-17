<?php
/**
 * Class to connect mycred with membership
 * 
 * @since 1.0
 * @version 1.0
 */

// If this file is called directly, abort.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'myCRED_Connect_Membership' ) ) :
    Class myCRED_Connect_Membership {

        /**
		 * Construct
		 */
        public function __construct() {
            add_action('admin_menu', array($this,'mycred_membership_menu'));
            add_action('init',array($this,'add_styles'));
        }

        function add_styles() {

            wp_register_style('admin-subscription-css', plugins_url( 'assets/css/admin-subscription.css', myCRED_THIS ), array(), '1.0', 'all');
            wp_enqueue_style('admin-subscription-css');
        }

        /**
		 * Register membership menu
		 */
        public function mycred_membership_menu() {
            add_submenu_page( 'mycred', 'Membership', 'Membership<span class="mycred-membership-menu-label">New</span>', 'manage_options', 'mycred-membership',array($this,'mycred_membership_callback'));
        }

        /**
		 * Membership menu callback
		 */
        public function mycred_membership_callback() {
            $user_id = get_current_user_id();
            $this->mycred_save_license();
            $membership_key = get_option( 'mycred_membership_key' );
            if( !isset( $membership_key )  && !empty( $membership_key ) )
                $membership_key = '';
            ?>
            <div class="wrap">
                <h1><?php _e( 'myCRED Membership Club', 'mycred' ); ?></h1>
                <div class="mmc_welcome">
                    <div class="mmc_welcome_content">
						<form action="#" method="post">
							<div class="mmc_title"><?php _e( 'Welcome to myCRED Membership Club', 'mycred' ); ?></div>
							<input type="text" name="mmc_lincense_key" class="mmc_lincense_key" placeholder="<?php _e( 'Add Your Membership License', 'mycred' ); ?>" value="<?php echo $membership_key?>">
							<input type="submit" class="mmc_save_license button-primary" value="Save"/>
							<div class="mmc_license_link"><a href="#"><span class="dashicons dashicons-editor-help"></span><?php _e('Click here to get your Membership License','mycred') ?></a></div>
						</form>
                    </div>
                </div>
            </div>
            <?php
        }

        /**
		 * Saving user membership key
		 */
        public function mycred_save_license() {
            
            if( !isset($_POST['mmc_lincense_key']) ) return;

            $license_key = sanitize_text_field( $_POST['mmc_lincense_key'] );
            if( isset( $license_key ) ) {
                update_option( 'mycred_membership_key', $license_key );
            }
        }
    }
endif;

$myCRED_Connect_Membership = new myCRED_Connect_Membership();