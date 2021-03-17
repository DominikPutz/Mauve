<?php if ( ! defined( 'ABSPATH' ) ) exit;


/**
 * Class UM_myCRED
 */
class UM_myCRED {

	/**
	 * @var string
	 */
	public $action = '';


	/**
	 * @var
	 */
	private static $instance;


	/**
	 * @return UM_myCRED
	 */
	static public function instance() {
		if ( is_null( self::$instance ) ) {
			self::$instance = new self();
		}
		return self::$instance;
	}


	/**
	 * UM_myCRED constructor.
	 */
	function __construct() {
		// Global for backwards compatibility.
		$GLOBALS['um_mycred'] = $this;
		add_filter( 'um_call_object_myCRED', array( &$this, 'get_this' ) );
		add_filter( 'um_settings_default_values', array( &$this, 'default_settings' ), 10, 1 );

		$this->enqueue();
		$this->account();
		$this->member_directory();

		add_action( 'plugins_loaded', array( &$this, 'init' ), 0 );
		add_action( 'plugins_loaded', array( &$this,'init_hooks' ), 1 );
		add_action( 'plugins_loaded', array( &$this,'load_hooks' ), 2 );

		add_filter( 'um_notification_modify_entry_mycred_custom_notification', array( &$this, 'um_mycred_custom_notification' ), 2, 99 );
		add_filter( 'um_rest_get_auser', array( &$this, 'rest_get_auser' ), 10, 3 );
	}

	/**
	 * @return $this
	 */
	function get_this() {
		return $this;
	}


	/**
	 * @param array $defaults
	 *
	 * @return array
	 */
	function default_settings( $defaults ) {
		$defaults = array_merge( $defaults, $this->setup()->settings_defaults );
		return $defaults;
	}


	function rest_get_auser( $response, $field, $user_id ) {
		if ( 'mycred_points' == $field ) {
			$response['mycred_points'] = number_format( (int)get_user_meta( $user_id, 'mycred_default', true ), 2 );
		}

		return $response;
	}


	/**
	 * @return um_ext\um_mycred\core\myCRED_Setup()
	 */
	function setup() {
		if ( empty( UM()->classes['um_mycred_setup'] ) ) {
			UM()->classes['um_mycred_setup'] = new um_ext\um_mycred\core\myCRED_Setup();
		}
		return UM()->classes['um_mycred_setup'];
	}


	/**
	 * @return um_ext\um_mycred\core\myCRED_Enqueue()
	 */
	function enqueue() {
		if ( empty( UM()->classes['um_mycred_enqueue'] ) ) {
			UM()->classes['um_mycred_enqueue'] = new um_ext\um_mycred\core\myCRED_Enqueue();
		}
		return UM()->classes['um_mycred_enqueue'];
	}


	/**
	 * @return um_ext\um_mycred\core\myCRED_Account()
	 */
	function account() {
		if ( empty( UM()->classes['um_mycred_account'] ) ) {
			UM()->classes['um_mycred_account'] = new um_ext\um_mycred\core\myCRED_Account();
		}
		return UM()->classes['um_mycred_account'];
	}


	/**
	 * @return um_ext\um_mycred\core\myCRED_Member_Directory()
	 */
	function member_directory() {
		if ( empty( UM()->classes['um_mycred_member_directory'] ) ) {
			UM()->classes['um_mycred_member_directory'] = new um_ext\um_mycred\core\myCRED_Member_Directory();
		}
		return UM()->classes['um_mycred_member_directory'];
	}


	/***
	***	@Init
	***/
	function init() {
		// Actions
		require_once um_mycred_path . 'includes/core/actions/um-mycred-bbpress.php';
		require_once um_mycred_path . 'includes/core/actions/um-mycred-tabs.php';
		require_once um_mycred_path . 'includes/core/actions/um-mycred-admin.php';

		// Filters
		require_once um_mycred_path . 'includes/core/filters/um-mycred-fields.php';
		require_once um_mycred_path . 'includes/core/filters/um-mycred-settings.php';
		require_once um_mycred_path . 'includes/core/filters/um-mycred-tabs.php';
		require_once um_mycred_path . 'includes/core/filters/um-mycred-search.php';
	}


	function load_hooks() {
		do_action( 'um_mycred_load_hooks' );

		require_once um_mycred_path . 'includes/core/hooks/um-mycred-account.php';
		require_once um_mycred_path . 'includes/core/hooks/um-mycred-login.php';
		require_once um_mycred_path . 'includes/core/hooks/um-mycred-member-directory.php';
		require_once um_mycred_path . 'includes/core/hooks/um-mycred-profile.php';
		require_once um_mycred_path . 'includes/core/hooks/um-mycred-register.php';
	}


	/**
	 * Init myCRED Custom Hooks
	 *
	 * @return um_ext\um_mycred\core\hooks\Init()
	 */
	function init_hooks() {
		if ( empty( UM()->classes['um_mycred_hooks_init'] ) ) {
			UM()->classes['um_mycred_hooks_init'] = new um_ext\um_mycred\core\hooks\Init();
		}
		return UM()->classes['um_mycred_hooks_init'];
	}


	/**
	 * @param $hook_callback
	 */
	function build_hook_widget( $hook_callback ) {
		require um_mycred_path . 'includes/admin/templates/hook-widget.php';
	}


	/**
	 * @param $data
	 * @param $hook_callback
	 *
	 * @return mixed
	 */
	function sanitise_preferences( $data, $hook_callback ) {
		$new_data = $data;

		foreach ( $hook_callback->um_hooks as $hook => $k ) {
			// Apply defaults if any field is left empty
			$new_data[ $hook ]['creds'] = ( ! empty( $data[ $hook ]['creds'] ) ) ? $data[ $hook ]['creds'] : $hook_callback->defaults[ $hook ]['creds'];
			$new_data[ $hook ]['log'] = ( ! empty( $data[ $hook ]['log'] ) ) ? sanitize_text_field( $data[ $hook ]['log'] ) : $hook_callback->defaults[ $hook ]['log'];
			$limit = ( ! empty( $data[ $hook ]['limit'] ) ) ? sanitize_text_field( $data[ $hook ]['limit'] ) : $hook_callback->defaults[ $hook ]['limit'];
			$new_data[ $hook ]['limit_by'] = ( ! empty( $data[ $hook ]['limit_by'] ) ) ? sanitize_text_field( $data[ $hook ]['limit_by'] ) : $hook_callback->defaults[ $hook ]['limit_by'];

			$new_data[ $hook ]['notification_tpl'] = ( ! empty( $data[ $hook ]['notification_tpl'] ) ) ? sanitize_text_field( $data[ $hook ]['notification_tpl'] ) : $hook_callback->defaults[ $hook ]['notification_tpl'];


			if ( $limit != '' ) {
				$new_data[ $hook ]['limit'] = $limit . '/' . $new_data[ $hook ]['limit_by'];
				unset( $new_data[ $hook ]['limit_by'] );
			}

		}

		$new_data = apply_filters( 'um_mycred_sanitise_pref', $new_data );

		return $new_data;
	}


	/**
	 * Show badges all
	 *
	 * @param int $template
	 *
	 * @return string
	 */
	function show_badges_all( $template = 1 ) {
		global $mycred;

		if ( ! function_exists( 'mycred_get_users_badges' ) ) {
			return '';
		}

		wp_enqueue_script( 'um_mycred' );
		wp_enqueue_style( 'um_mycred' );

		$size = UM()->options()->get( 'mycred_badge_size' );

		if ( $template == 1 ) {
		
			return do_shortcode( '[mycred_badges title="0" requires="0" show="main" width="' . $size . '" height="' . $size . '"]' );
		
		} elseif ( $template == 2 ) {
			$output = '';

			$all_badges = mycred_get_badge_ids();
			$point_types = mycred_get_types( true );
			$references = mycred_get_all_references();

			if ( ! empty( $all_badges ) ) {
				$output = UM()->get_template( 'badges.php', um_mycred_plugin, compact(
					'mycred',
					'size',
					'all_badges',
					'point_types',
					'references'
				));
			}

			return $output;
		}

		return '';
	}


	/**
	 * Show badges of user
	 *
	 * @param $user_id
	 *
	 * @return string
	 */
	function show_badges( $user_id ) {

		if ( ! function_exists( 'mycred_get_users_badges' ) ) {
			return '';
		}

		wp_enqueue_script( 'um_mycred' );
		wp_enqueue_style( 'um_mycred' );

		$output = '';
		$size = UM()->options()->get( 'mycred_badge_size' );

		$users_badges = mycred_get_users_badges( $user_id );
		$all_badges = mycred_get_badge_ids();
		$user_points = $this->get_points_clean( $user_id );

		if ( ! empty( $all_badges  ) ) {

			$output .= '<span class="um-badges">';
			$current_assigned_badges = 0;
			foreach ( $all_badges as $badge_id ) {
				$image_identification = false;

				if ( array_key_exists( $badge_id, $users_badges ) ) {
					$level = $users_badges[ $badge_id ];
					$badge = mycred_get_badge( $badge_id, $level );
					$image_identification = $badge->levels[ $level ]['image_url'];

					if ( $badge->levels[ $level ]['attachment_id'] > 0 ) {
						$image_identification = $badge->levels[ $level ]['attachment_id'];
						$level_title = $badge->levels[ $level ]['label'];

						if ( ! empty( $level_title  ) ) {
							$badge->title = "{$badge->title} - {$level_title}";
						} else {
							$level++;
							$badge->title = "{$badge->title} - Level {$level}";
						}
					}
				}

				if ( $image_identification ) {
					$image_url = wp_get_attachment_url( $image_identification );
					$title = apply_filters( 'um_mycred_badge_loop_title', $badge->title, $badge );

					$output .= '<span class="the-badge">';
					$output .= '<img src="' . esc_url( $image_url ) . '" title="' . esc_attr( $title ) . '" alt="' . esc_attr( $title ) . '" width="' . esc_attr( $size ) . '" height="' . esc_attr( $size ) . '" class="mycred-badge earned um-tip-n" />';
					$output .= '</span>';
					$current_assigned_badges++;
				}

			} // endforeach

			$output .= '</span>';

			if ( $current_assigned_badges <= 0 ) {
				$output = '';
			}
		}

		return $output;
	}


	/***
	***	@Get points
	***/
	function get_points( $user_id, $value = null ) {
		if ( !$value ) {
			$value = get_user_meta( $user_id, 'mycred_default', true );
		}
		if ( $value > 0 ) {
			$value = number_format_i18n( $value, UM()->options()->get( 'mycred_decimals' ) );
		} else {
			$value = number_format_i18n( 0, UM()->options()->get( 'mycred_decimals' ) );
		}
		$value = sprintf( _n( '%s point', '%s points', $value, 'um-mycred' ),  $value );

		return $value;
	}


	/***
	***	@Get points clean
	***/
	function get_points_clean( $user_id, $value = null ) {
		if ( !$value ) {
			$value = get_user_meta( $user_id, 'mycred_default', true );
		}
		return $value;
	}


	/***
	***	@transfer points
	***/
	function transfer( $from, $to, $amount ) {

		do_action('um_mycred_credit_balance_transfer', $to, $amount, $from );

		mycred_add( 'um-transfer-credit', $to, $amount, '%plural% received!' );
		mycred_subtract( 'um-transfer-charge', $from, $amount, '%plural% sent!' );

		delete_option( "um_cache_userdata_{$to}" );
		delete_option( "um_cache_userdata_{$from}" );
	}


	/***
	***	@add points
	***/
	function add( $user_id, $add, $args = array() ) {

		if( $add == 'mycred_um_social_connect' ){
			$add = 'mycred_'.$args['provider'];
		}
		$mycred = UM()->options()->get( $add );

		if ( !$mycred ) return;

		// imply limits
		if( $this->imply_limit( $user_id, $add, 'awarded' ) ){
			return;
		}

		$action = $add;
		$this->action = $add;
		$type = 'reward';
		$amount = UM()->options()->get( $action . '_points');

		$default_handler = apply_filters( 'um_mycred_add_func', true );
		if ( $default_handler ) {
			$task = UM()->options()->get( $action . '_task');
			$log_template = UM()->options()->get( $action . '_log_template');
			$log_text = str_replace('%task%', __( $task,'um-mycred') , __($log_template,'um-mycred') );

			mycred_add( $action, $user_id, $amount, $log_text );
		}

		do_action('um_mycred_credit_balance_user', $user_id, $add, $action, $args, $type );

		$description = sprintf(
			__('Earned %s via Ultimate Member( %s )','um-mycred'),
			'%plural%',
			$action
		);

		$description = apply_filters( 'um_mycred_add_point_description', $description, $amount, $action );

		mycred_add( $action, $user_id, $add, $description );
		delete_option( "um_cache_userdata_{$user_id}" );

	}


	/***
	***	@add points (hold)
	***/
	function add_pending( $user_id, $add ) {
		$mycred = UM()->options()->get( $add );
		if ( ! $mycred )
			return;

		// imply limits
		if ( $this->imply_limit( $user_id, $add, 'awarded' ) )
			return;

		$add = UM()->options()->get( $add . '_points' );
		return $add;
	}


	/***
	***	@deduct points
	***/
	function deduct( $user_id, $deduct, $args = array() ) {

		if( $deduct == 'mycred_um_social_disconnect' ){
			$deduct = 'mycred_d_'.$args['provider'];
		}

		$mycred = UM()->options()->get($deduct);

		if ( !$mycred ) return;

		// imply limits
		if( $this->imply_limit( $user_id, $deduct,'deducted' ) )
			return;

		$action = $deduct;
		$this->action = $deduct;
		$type = 'deduct';

		$default_handler = apply_filters( 'um_mycred_deduct_func', true );
		if ( $default_handler ) {
			$amount = UM()->options()->get( $action . '_points');
			$task = UM()->options()->get( $action . '_task');
			$log_template = UM()->options()->get( $action . '_log_template');
			$log_text = str_replace('%task%', __( $task,'um-mycred') , __($log_template,'um-mycred') );

			mycred_subtract( $action, $user_id, $amount, $log_text );
		}

		do_action('um_mycred_credit_balance_user', $user_id, $deduct, $action, $args, $type );

		delete_option( "um_cache_userdata_{$user_id}" );

	}


	/**
	 * Update user's balance
	 * @param  integer $user_id
	 * @param  string  $action
	 * @param  string  $type
	 */
	function imply_limit( $user_id, $action, $type ){

		if ( UM()->options()->get( $action . '_limit' ) ) {

			$last_update 	= get_user_meta( $user_id, '_mycred_'.$type.'_last_time_update', true);
			$limit   	 	= UM()->options()->get( $action . '_limit' );
			$limit_by 	 	= UM()->options()->get( $action . '_limit_duration');
			$a_limit 	 	= get_user_meta( $user_id, '_mycred_'.$type.'_lmt', true);
			$user_limit_by_value = get_user_meta( $user_id, '_mycred_'.$type.'_'.$limit_by.'_lmt', true);

			$current_time 	= current_time('timestamp');

			if( ! $user_limit_by_value ){
				$user_limit_by_value = 0;
			}

			$user_limit_by_value++;

			switch ( $limit_by ) {
				case 'in_total': // if within total limit
					if ( isset( $a_limit[$action] ) && $a_limit[$action] >= $limit ) {
						return true;
					}
					break;
				case 'per_day': // if within the day and exceeds limit, return;
					if( strtotime('+1 day',$last_update) >= $current_time &&  $user_limit_by_value >= $limit  ){
						return true;
					}
					break;
				case 'per_week': // if within the week and exceeds limit, return;
					if( strtotime('+1 week',$last_update) >=  $current_time &&  $user_limit_by_value >= $limit   ){
						return true;
					}
					break;
				case 'per_month': // if within the month and exceeds limit, return;
					if(  strtotime('+1 month',$last_update) >=  $current_time &&  $user_limit_by_value >= $limit   ){
						return true;
					}
					break;
				default: // no limit

					break;
			}

			if ( !isset( $a_limit[ $action ] ) ) {
				$a_limit[ $action ] = 1;
			} else {
				$a_limit[ $action ] = $a_limit[ $action ] + 1;
			}



			update_user_meta( $user_id, '_mycred_'.$type.'_lmt', $a_limit);
			update_user_meta( $user_id, '_mycred_'.$type.'_'.$limit_by.'_lmt', $user_limit_by_value);
			update_user_meta( $user_id, '_mycred_'.$type.'_last_time_update', current_time('timestamp') );

			return false;

		}
	}


	/**
	 * Add custom notification
	 * @param  string $content
	 * @param  array  $vars
	 * @return string
	 */
	function um_mycred_custom_notification( $content, $vars = array() ){

		$mycred 				= mycred();
		$action 				= $this->action;
		$user_id 				= um_user('ID');
		$amount 				= UM()->options()->get( $action . '_points');
		$limit 					= UM()->options()->get( $action . '_limit');
		$task 					= UM()->options()->get( $action . '_task');
		$notification_template 	= UM()->options()->get( $action . '_notification_template');
		$log_template 			= UM()->options()->get( $action . '_log_template');

		$content = str_replace('%task%', __( $task,'um-mycred') , __($notification_template,'um-mycred') );
		$content = str_replace('%plural%', strtolower($amount.' '.$mycred->plural()) , $content);
		$log_text = str_replace('%task%', __( $task,'um-mycred') , __($log_template,'um-mycred') );

		if( $vars['mycred_type'] == 'reward' ){
			mycred_add( $action, $user_id, $amount, $log_text );
		}else if( $vars['mycred_type'] == 'deduct' ){
			mycred_subtract( $action, $user_id, $amount, $log_text );
		}

		return $content;
	}


	/**
	 * Get user progress
	 *
	 * @param $user_id
	 *
	 * @return int|string
	 */
	function get_rank_progress( $user_id ) {

		$mycred = mycred();

		$key = $mycred->get_point_type_key();

		$users_balance = $mycred->get_users_balance( $user_id, $key );
		$users_rank = mycred_get_users_rank( $user_id );
		if( is_object( $users_rank ) ){
			$max = $users_rank->maximum;
		}


		if ( !$users_balance || ! isset( $max ) || empty( $max ) ) return 0;
		$progress = number_format( ( ( floatval( $users_balance ) / floatval( $max ) ) * 100 ), 1 );

		if ( $progress < number_format( 100, 1 ) ) {

		} else {
			$progress = number_format( 100, 1 );
		}

		return $progress;

	}

}

//create class var
add_action( 'plugins_loaded', 'um_init_mycred', -10, 1 );
function um_init_mycred() {
	if ( function_exists( 'UM' ) ) {
		UM()->set_class( 'myCRED', true );
	}
}