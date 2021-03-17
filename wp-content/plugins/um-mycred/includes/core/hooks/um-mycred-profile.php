<?php if ( ! defined( 'ABSPATH' ) ) exit;


/**
 * Class UM_myCRED_Profile_Hooks
 */
class UM_myCRED_Profile_Hooks extends myCRED_Hook {


	/**
	 * @var array
	 */
	var $um_hooks = array();


	/**
	 * UM_myCRED_Profile_Hooks constructor.
	 *
	 * @param $hook_prefs
	 * @param $type
	 */
	function __construct( $hook_prefs, $type ) {

		$this->um_hooks = array(
			'profile_photo' => array(
				'title'  => __( 'Upload Profile Photo', 'um-mycred' ),
				'action' => __( 'adding profile photo', 'um-mycred' ),
			),
			'remove_profile_photo' => array(
				'title'  => __( 'Remove Profile Photo', 'um-mycred' ),
				'action' => __( 'removing profile photo', 'um-mycred' ),
				'deduct' => true,
			),
			'cover_photo' => array(
				'title'  => __( 'Upload Cover Photo', 'um-mycred' ),
				'action' => __( 'adding cover photo', 'um-mycred' ),
			),
			'remove_cover_photo' => array(
				'title'  => __( 'Remove Cover Photo', 'um-mycred' ),
				'action' => __( 'removing cover photo', 'um-mycred' ),
				'deduct' => true,
			),
			'update_profile' => array(
				'title'  => __( 'Update Profile', 'um-mycred' ),
				'action' => __( 'updating profile', 'um-mycred' ),
			)
		);

		$arr_defaults = array();

		foreach ( $this->um_hooks as $hook => $k ) {

			$arr_defaults[ $hook ] = array(
				'creds'             => 1,
				'log'               => "%plural% for {$k['action']}.",
				'limit'             => '0/x',
				'um_hook'           => $hook,
				'notification_tpl'  => '',
			);

		}

		parent::__construct( array(
			'id'       => 'um-user-profile',
			'defaults' => $arr_defaults
		), $hook_prefs, $type );

	}


	/**
	 * Hook into WordPress
	 */
	public function run() {

		if ( $this->prefs['profile_photo']['creds'] != 0 ) {
			add_action( 'um_before_upload_db_meta_profile_photo', array( $this, 'award_points_putting_profile_photo' ), 1 );
		}

		if ( $this->prefs['cover_photo']['creds'] != 0 ) {
			add_action( 'um_before_upload_db_meta_cover_photo', array( $this, 'award_points_putting_cover_photo' ), 1 );
		}

		if ( $this->prefs['update_profile']['creds'] != 0 ) {
			add_action( 'um_user_pre_updating_profile', array( $this, 'award_points_updating_profile' ), 1 );
		}

		if ( $this->prefs['remove_profile_photo']['creds'] != 0 ) {
			add_action( 'um_after_remove_profile_photo', array( $this, 'deduct_when_user_remove_photo' ), 1 );
		}

		if ( $this->prefs['remove_cover_photo']['creds'] != 0 ) {
			add_action( 'um_after_remove_cover_photo', array( $this, 'deduct_when_user_remove_cover' ), 1 );
		}

	}

	/**
	 * Check if the user qualifies for points
	 *
	 * @param $user_id
	 */
	public function award_points_putting_profile_photo( $user_id ) {

		if ( ! $user_id  ) {
			$user_id = get_current_user_id();
		}

		// Check for exclusion
		if ( $this->core->exclude_user( $user_id ) ) {
			return;
		}

		// Limit
		if ( $this->over_hook_limit( 'profile_photo', 'profile_photo', $user_id ) ) {
			return;
		}

		// Execute
		$this->core->add_creds(
			'profile_photo',
			$user_id,
			$this->prefs['profile_photo']['creds'],
			$this->prefs['profile_photo']['log'],
			0,
			'',
			$this->mycred_type
		);
	}


	/**
	 * Check if the user qualifies for points
	 *
	 * @param $user_id
	 */
	public function award_points_putting_cover_photo( $user_id ) {
		
		if ( ! $user_id  ) {
			$user_id = get_current_user_id();
		}

		// Check for exclusion
		if ( $this->core->exclude_user( $user_id ) ) {
			return;
		}

		// Limit
		if ( $this->over_hook_limit( 'cover_photo', 'cover_photo', $user_id ) ) {
			return;
		}

		// Execute
		$this->core->add_creds(
			'cover_photo',
			$user_id,
			$this->prefs['cover_photo']['creds'],
			$this->prefs['cover_photo']['log'],
			0,
			'',
			$this->mycred_type
		);
	}


	/**
	 * Check if the user qualifies for points
	 *
	 * @param $changes
	 */
	public function award_points_updating_profile( $changes ) {
		$user_id = get_current_user_id();

		if ( um_is_core_page('register') ) {
			return;
		}

		if ( is_admin() )  {
			return;
		}
		
		// Check for exclusion
		if ( $this->core->exclude_user( $user_id ) ) {
			return;
		}

		// Limit
		if ( $this->over_hook_limit( 'update_profile', 'update_profile', $user_id ) ) {
			return;
		}

		$changed = array();
		um_fetch_user( $user_id );

		foreach ( $changes as $k => $v ) {
			$value = um_user( $k );
			if ( $value !== $v || is_array( $value ) && is_array( $v ) && count( array_intersect( $value, $v ) ) > 0 ) {
				$changed[ $k ] = $v;
			}
		}

		if ( isset( $changed['mycred_default'] ) ) {
			unset( $changed['mycred_default'] );
		}

		if ( ! empty( $changed ) ) {
			// Execute
			$this->core->add_creds(
				'update_profile',
				$user_id,
				$this->prefs['update_profile']['creds'],
				$this->prefs['update_profile']['log'],
				0,
				'',
				$this->mycred_type
			);
		}

	}


	/**
	 * Check if the user qualifies for points
	 *
	 * @param $user_id
	 */
	public function deduct_when_user_remove_photo( $user_id ) {

		if ( ! $user_id  ) {
			$user_id = get_current_user_id();
		}

		// Check for exclusion
		if ( $this->core->exclude_user( $user_id ) ) {
			return;
		}

		// Limit
		if ( $this->over_hook_limit( 'remove_profile_photo', 'profile_photo', $user_id ) ) {
			return;
		}

		$creds = -1 * abs( $this->prefs['remove_profile_photo']['creds'] );

		// Execute
		$this->core->add_creds(
			'profile_photo',
			$user_id,
			$creds,
			$this->prefs['remove_profile_photo']['log'],
			0,
			'',
			$this->mycred_type
		);
	}


	/**
	 * Check if the user qualifies for points
	 *
	 * @param $user_id
	 */
	public function deduct_when_user_remove_cover( $user_id ) {

		if ( ! $user_id  ) {
			$user_id = get_current_user_id();
		}

		// Check for exclusion
		if ( $this->core->exclude_user( $user_id ) ) {
			return;
		}

		// Limit
		if ( $this->over_hook_limit( 'remove_cover_photo', 'cover_photo', $user_id ) ) {
			return;
		}

		$creds = -1 * abs( $this->prefs['remove_cover_photo']['creds'] );

		// Execute
		$this->core->add_creds(
			'cover_photo',
			$user_id,
			$creds,
			$this->prefs['remove_cover_photo']['log'],
			0,
			'',
			$this->mycred_type
		);
	}


	/**
	 * Add Settings
	 */
	public function preferences() {
		UM()->myCRED()->build_hook_widget( $this );
	}


	/**
	 * Sanitize Preferences
	 * 
	 * @param $data
	 *
	 * @return mixed
	 */
	public function sanitise_preferences( $data ) {
		return UM()->myCRED()->sanitise_preferences( $data, $this );
	}
}