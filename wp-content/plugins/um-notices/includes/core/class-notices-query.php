<?php
namespace um_ext\um_notices\core;


if ( ! defined( 'ABSPATH' ) ) exit;


/**
 * Class Notices_Query
 * @package um_ext\um_notices\core
 */
class Notices_Query {

	/**
	 * Notices_Query constructor.
	 */
	function __construct() {
		add_action( 'wp_footer', array( &$this, 'head_enqueue' ), -1 );
		add_action( 'wp_footer', array( &$this, 'show_notice' ), 9999 );
	}


	/**
	 *
	 */
	function head_enqueue() {
		$this->get_notices();

		if ( ! isset( $this->notice_id ) || $this->notice_id <= 0 ) {
			return;
		}

		wp_enqueue_script( 'um_notices' );
		wp_enqueue_style( 'um_notices' );
	}


	/**
	 * Display notices in footer
	 *
	 * @param null $force_id
	 */
	function show_notice( $force_id = null ) {
		$this->get_notices( $force_id );

		if ( ! isset( $this->notice_id ) || $this->notice_id <= 0 ) {
			return;
		}

		$notice_id = $this->notice_id;

		$post = get_post( $notice_id );
		$meta = get_post_custom( $notice_id );

		$style = '';
		if ( $post->_um_border ) {
			$style .= ' border: ' . $post->_um_border . ';border-bottom: none !important;';
		}
		if ( $post->_um_border_radius ) {
			$style .= ' border-radius: ' . $post->_um_border_radius . ' ' . $post->_um_border_radius . ' 0px 0px;';
		}
		if ( $post->_um_boxshadow ) {
			$style .= ' box-shadow: ' . $post->_um_boxshadow . ';';
		}
		if ( $post->_um_bgcolor ) {
			$style .= ' background: ' . $post->_um_bgcolor . ';';
		}
		if ( $post->_um_textcolor ) {
			$style .= ' color: ' . $post->_um_textcolor . ';';
		}
		if ( $post->_um_fontsize ) {
			$style .= ' font-size: ' . $post->_um_fontsize . ';';
		}

		$close_color = '';
		if ( $post->_um_closeiconcolor ) {
			$close_color = ' color: ' . $post->_um_closeiconcolor . ';';
		} elseif ( $post->_um_textcolor ) {
			$close_color = ' color: ' . $post->_um_textcolor . ';';
		}

		$icon_color = '';
		if ( $post->_um_iconcolor ) {
			$icon_color = ' color: ' . $post->_um_iconcolor . ';';
		} elseif ( $post->_um_textcolor ) {
			$icon_color = ' color: ' . $post->_um_textcolor . ';';
		}

		wp_reset_query();

		wp_enqueue_script( 'um_notices' );
		wp_enqueue_style( 'um_notices' );

		$t_args = compact( 'close_color', 'force_id', 'icon_color', 'meta', 'notice_id', 'post', 'style' );
		UM()->get_template( 'notice.php', um_notices_plugin, $t_args, true );
	}


	/**
	 * Get user notices
	 *
	 * @param int|null $force_id
	 */
	function get_notices( $force_id = null ) {
		global $current_user;

		$args = array(
			'post_status'       => array( 'publish' ),
			'post_type'         => 'um_notice',
			'posts_per_page'    => -1,
			'fields'            => 'ids',
		);
		
		if ( $force_id ) {
			$args['post__in'] = array( $force_id );
		}

		$notices = new \WP_Query( $args );
		$notices_count = $notices->found_posts;
		if ( $notices_count <= 0 ) {
			return;
		}

		$user_notices = $notices->posts;
		foreach ( $user_notices as $k => $notice_id ) {

			$meta = get_post_custom( $notice_id );

			if ( ! $force_id ) {

				if ( isset( UM()->Notices()->shortcodes[ $notice_id ] ) ) {
					unset( $user_notices[ $k ] );
				}

				if ( isset( $meta['_um_show_in_footer'][0] ) && $meta['_um_show_in_footer'][0] == 0 ) {
					unset( $user_notices[ $k ] );
				}

				if ( isset( $meta['_um_show_in_urls'][0] ) && $meta['_um_show_in_urls'][0] == 1 ) {
					
					$urls = array_map("rtrim", explode("\n", $meta['_um_allowed_urls'][0] ));
					
					$current_url = UM()->permalinks()->get_current_url( true );
					$current_url = untrailingslashit( $current_url );
					$current_url_slash = trailingslashit( $current_url );
					
					if ( um_is_core_page('user') && strstr( $current_url, untrailingslashit( um_get_core_page('user') ) ) ) {
						
					} elseif ( in_array( $current_url, $urls ) || in_array( $current_url_slash, $urls ) ) {
						
					} else {
						unset( $user_notices[ $k ] );
					}

				} else {

					if ( isset( $meta['_um_show_in_home'][0] ) && $meta['_um_show_in_home'][0] == 0 && ( is_home() || is_front_page() ) ) {
						unset( $user_notices[ $k ] );
					}
					
					if ( isset( $meta['_um_show_in_pages'][0] ) && $meta['_um_show_in_pages'][0] == 0 && get_post_type() == 'page' ) {
						unset( $user_notices[ $k ] );
					}
					
					if ( isset( $meta['_um_show_in_posts'][0] ) && $meta['_um_show_in_posts'][0] == 0 && get_post_type() == 'post' ) {
						unset( $user_notices[ $k ] );
					}
					
					if ( isset( $meta['_um_show_in_types'][0] ) && $meta['_um_show_in_types'][0] == 0 && ! in_array( get_post_type(), array( 'post', 'page' ) ) ) {
						unset( $user_notices[ $k ] );
					}
					
				}
				
			}

			if ( ! empty( $meta['_um_only_users'][0] ) ) {

				if ( ! is_user_logged_in() ) {
					unset( $user_notices[ $k ] );
				} else {

					$users = array_map( 'trim', explode( ',', $meta['_um_only_users'][0] ) );
					if ( ! array_intersect( $users, array( $current_user->ID, $current_user->user_login ) ) ) {
						unset( $user_notices[ $k ] );
					}
				}
			}

			if ( $this->user_saw_this_notice( $notice_id ) ) {
				unset( $user_notices[ $k ] );
			}
			
			if ( $meta['_um_show_loggedout'][0] == 1 && $meta['_um_show_loggedin'][0] == 0 && is_user_logged_in() ) {
				unset( $user_notices[ $k ] );
			}
			
			if ( $meta['_um_show_loggedout'][0] == 0 && $meta['_um_show_loggedin'][0] == 1 && ! is_user_logged_in() ) {
				unset( $user_notices[ $k ] );
			}
			
			if ( $meta['_um_show_loggedout'][0] == 0 && $meta['_um_show_loggedin'][0] == 0 ) {
				// do not show_notice
				unset( $user_notices[ $k ] );
			}
			
			if ( is_user_logged_in() ) {
				if ( isset( $meta['_um_roles'][0] ) ) {
					$roles = maybe_unserialize( $meta['_um_roles'][0] );
					$current_user_roles = UM()->roles()->get_all_user_roles( get_current_user_id() );
					if ( $roles && ( empty( $current_user_roles ) || count( array_intersect( $current_user_roles, $roles ) ) <= 0 ) ) {
						unset( $user_notices[ $k ] );
					}
				}
			
				if ( ! empty( $meta['_um_custom_field'][0] ) ) {
					
					if ( $meta['_um_custom_field'][0] == 'other' ) {
						$key = $meta['_um_custom_key'][0];
					} else {
						$key = $meta['_um_custom_field'][0];
					}
					
					if ( get_user_meta( get_current_user_id(), $key, true ) ) {
						unset( $user_notices[ $k ] );
					}
					
					if ( $key == 'profile_photo' ) {
						if ( get_user_meta( get_current_user_id(), 'synced_profile_photo', true ) ) {
							unset( $user_notices[ $k ] );
						}
					}

				}
				
				// EDD Integration
				if ( class_exists( 'Easy_Digital_Downloads' ) ) {
					
					if ( isset( $meta['_um_edd_users'][0] ) && $meta['_um_edd_users'][0] == 2 ) { // made purchases
						
						$user = edd_get_purchase_stats_by_user( get_current_user_id() );
						if ( $meta['_um_edd_users_amount'][0] > 0 && $user['total_spent'] < $meta['_um_edd_users_amount'][0] ) {
							unset( $user_notices[ $k ] );
						}
						
						if ( ! edd_has_purchases( get_current_user_id() ) ) {
							unset( $user_notices[ $k ] );
						}
						
					} else if ( isset( $meta['_um_edd_users'][0] ) && $meta['_um_edd_users'][0] == 1 ) { // did not make purchases
						if ( edd_has_purchases( get_current_user_id() ) ) {
							unset( $user_notices[ $k ] );
						}
					}
					
				}
				
			}

		}

		if ( ! empty( $user_notices ) && $user_notices ) {
			reset( $user_notices );
			$first_key = key( $user_notices );
			$this->notice_id = $user_notices[ $first_key ];
		} else {
			$this->notice_id = 0;
		}
	}


	/**
	 * Boolean if user saw this notice
	 *
	 * @param $notice_id
	 *
	 * @return bool
	 */
	function user_saw_this_notice( $notice_id ) {
		if ( is_user_logged_in() ) {
			$users = get_post_meta( $notice_id, '_users', true );
			if ( $users && is_array( $users ) && in_array( get_current_user_id(), $users ) ) {
				return true;
			}
		} elseif ( isset( $_COOKIE[ 'um_notice_seen_' . $notice_id ] ) ) {
			return true;
		}
		return false;
	}
}