<?php if ( ! defined( 'ABSPATH' ) ) exit;


/**
 * Class UM_myCRED_Member_Directory_Hooks
 */
class UM_myCRED_Member_Directory_Hooks extends myCRED_Hook {


	/**
	 * @var array
	 */
	var $um_hooks = array();


	/**
	 * UM_myCRED_Member_Directory_Hooks constructor.
	 *
	 * @param $hook_prefs
	 * @param $type
	 */
	function __construct( $hook_prefs, $type ) {

		$this->um_hooks = array(
			'member_search' => array(
				'title'  => __( 'Use Search', 'um-mycred' ),
				'action' => __( 'using search member form', 'um-mycred' ),
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
			'id'       => 'um-member-directory',
			'defaults' => $arr_defaults
		), $hook_prefs, $type );

	}

	/**
	 * Hook into WordPress
	 */
	public function run() {
		if ( $this->prefs['member_search']['creds'] != 0 ) {
			add_action( 'um_pre_directory_shortcode', array( $this,'award_points_directory_search' ), 10, 1 );
		}
	}

	/**
	 * Check if the user qualifies for points
	 */
	public function award_points_directory_search( $search_args ) {
		if ( ! isset( $user_id ) ) {
			$user_id = get_current_user_id();
		}

		// Check for exclusion
		if ( $this->core->exclude_user( $user_id ) ) {
			return;
		}

		// Limit
		if ( $this->over_hook_limit( 'member_search', 'member_search', $user_id ) ) {
			return;
		}

		if ( isset( $_REQUEST['um_search'] ) && ! empty( $search_args['search_fields'] ) ) {
			// Execute
			$this->core->add_creds(
				'member_search',
				$user_id,
				$this->prefs['member_search']['creds'],
				$this->prefs['member_search']['log'],
				0,
				'',
				$this->mycred_type
			);
		}
	}


	/**
	 * Add Settings
	 */
	public function preferences() {
		UM()->myCRED()->build_hook_widget( $this );
	}


	/**
	 * Sanitize Preferences
	 */
	public function sanitise_preferences( $data ) {
		return UM()->myCRED()->sanitise_preferences( $data, $this );
	}
}