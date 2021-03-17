<?php
namespace um_ext\um_woocommerce\core;

if ( ! defined( 'ABSPATH' ) ) exit;

class WooCommerce_Access {


	/**
	 * WooCommerce_Access constructor.
	 */
	function __construct() {
		add_filter( 'pre_get_posts', array( &$this, 'woo_pre_get_posts' ), 99, 1 );
		add_filter( 'the_posts', array( &$this, 'woo_filter_protected_posts' ), 98, 2 );

		add_action( 'admin_init', array( &$this, 'remove_hooks' ) );

		add_filter( 'um_user_permissions_filter', array( &$this, 'user_permissions_filter' ), 10, 4 );
	}


	/**
	 * @param $meta
	 * @param $user_id
	 *
	 * @return mixed
	 */
	function user_permissions_filter( $meta, $user_id ) {

		if ( ! isset( $meta['woo_purchases_tab'] ) ) {
			$meta['woo_purchases_tab'] = 1;
		}

		if ( ! isset( $meta['woo_reviews_tab'] ) ) {
			$meta['woo_reviews_tab'] = 1;
		}

		if ( ! isset( $meta['woo_account_billing'] ) ) {
			$meta['woo_account_billing'] = 1;
		}

		if ( ! isset( $meta['woo_account_shipping'] ) ) {
			$meta['woo_account_shipping'] = 1;
		}

		if ( ! isset( $meta['woo_account_orders'] ) ) {
			$meta['woo_account_orders'] = 1;
		}

		if ( ! isset( $meta['woo_account_subscription'] ) ) {
			$meta['woo_account_subscription'] = 1;
		}

		if ( ! isset( $meta['woo_account_downloads'] ) ) {
			$meta['woo_account_downloads'] = 1;
		}

		if ( ! isset( $meta['woo_account_payment_methods'] ) ) {
			$meta['woo_account_payment_methods'] = 0;
		}

		return $meta;
	}


	/**
	 * Show restrict content metabox on Shop page
	 */
	function remove_hooks() {
		remove_filter( 'um_restrict_content_hide_metabox', array( UM()->metabox(), 'hide_metabox_restrict_content_shop' ) );
	}


	/**
	 * @param \WP_Query $query
	 *
	 * @return \WP_Query
	 */
	function woo_pre_get_posts( $query ) {

		//is_shop add notices because uses $query->is_page in wp-query
		//so added @ for hide notices, but works properly
		if ( ! ( @is_shop() && $query->is_main_query() ) ) {
			return $query;
		}

		$shop_post = get_post( wc_get_page_id( 'shop' ) );

		$restriction = UM()->access()->get_post_privacy_settings( $shop_post );

		if ( ! $restriction ) {
			return $query;
		} else {
			//post is private
			if ( '1' == $restriction['_um_accessible'] ) {
				//if post for not logged in users and user is not logged in

				if ( ! is_user_logged_in() ) {
					return $query;
				} else {

					if ( current_user_can( 'administrator' ) ) {
						return $query;
					}

					//if single post query
					if ( isset( $restriction['_um_noaccess_action'] ) && '1' == $restriction['_um_noaccess_action'] ) {
						$curr = UM()->permalinks()->get_current_url();

						if ( ! isset( $restriction['_um_access_redirect'] ) || '0' == $restriction['_um_access_redirect'] ) {

							exit( wp_redirect( esc_url( add_query_arg( 'redirect_to', urlencode_deep( $curr ), um_get_core_page( 'login' ) ) ) ) );

						} elseif ( '1' == $restriction['_um_access_redirect'] ) {

							if ( ! empty( $restriction['_um_access_redirect_url'] ) ) {
								$redirect = $restriction['_um_access_redirect_url'];
							} else {
								$redirect = esc_url( add_query_arg( 'redirect_to', urlencode_deep( $curr ), um_get_core_page( 'login' ) ) );
							}

							exit( wp_redirect( $redirect ) );
						}

					}
				}
			} elseif ( '2' == $restriction['_um_accessible'] ) {
				//if post for logged in users and user is not logged in
				if ( is_user_logged_in() ) {

					if ( current_user_can( 'administrator' ) ) {
						return $query;
					}

					$custom_restrict = apply_filters( 'um_custom_restriction', true, $restriction );

					if ( empty( $restriction['_um_access_roles'] ) ) {
						if ( $custom_restrict ) {
							return $query;
						}
					} else {
						$user_can = UM()->access()->user_can( get_current_user_id(), $restriction['_um_access_roles'] );

						if ( isset( $user_can ) && $user_can && $custom_restrict ) {
							return $query;
						}
					}

					//if single post query
					if ( isset( $restriction['_um_noaccess_action'] ) && '1' == $restriction['_um_noaccess_action'] ) {

						$curr = UM()->permalinks()->get_current_url();

						if ( ! isset( $restriction['_um_access_redirect'] ) || '0' == $restriction['_um_access_redirect'] ) {

							exit( wp_redirect( esc_url( add_query_arg( 'redirect_to', urlencode_deep( $curr ), um_get_core_page( 'login' ) ) ) ) );

						} elseif ( '1' == $restriction['_um_access_redirect'] ) {

							if ( ! empty( $restriction['_um_access_redirect_url'] ) ) {
								$redirect = $restriction['_um_access_redirect_url'];
							} else {
								$redirect = esc_url( add_query_arg( 'redirect_to', urlencode_deep( $curr ), um_get_core_page( 'login' ) ) );
							}

							exit( wp_redirect( $redirect ) );
						}

					}

				} else {
					//if single post query
					if ( isset( $restriction['_um_noaccess_action'] ) && '1' == $restriction['_um_noaccess_action'] ) {

						$curr = UM()->permalinks()->get_current_url();

						if ( ! isset( $restriction['_um_access_redirect'] ) || '0' == $restriction['_um_access_redirect'] ) {

							exit( wp_redirect( esc_url( add_query_arg( 'redirect_to', urlencode_deep( $curr ), um_get_core_page( 'login' ) ) ) ) );

						} elseif ( '1' == $restriction['_um_access_redirect'] ) {

							if ( ! empty( $restriction['_um_access_redirect_url'] ) ) {
								$redirect = $restriction['_um_access_redirect_url'];
							} else {
								$redirect = esc_url( add_query_arg( 'redirect_to', urlencode_deep( $curr ), um_get_core_page( 'login' ) ) );
							}

							exit( wp_redirect( $redirect ) );
						}
					}
				}
			}
		}

		return $query;
	}


	/**
	 * Protect Post Types in query
	 * Restrict content new logic
	 *
	 * @param $posts
	 * @param \WP_Query $query
	 * @return array
	 */
	function woo_filter_protected_posts( $posts, $query ) {
		//is_shop add notices because uses $query->is_page in wp-query
		//so added @ for hide notices, but works properly
		if ( ! ( @is_shop() && $query->is_main_query() ) ) {
			return $posts;
		}

		$shop_post = get_post( wc_get_page_id( 'shop' ) );

		$restriction = UM()->access()->get_post_privacy_settings( $shop_post );

		if ( ! $restriction )
			return $posts;

		//post is private
		if ( '1' == $restriction['_um_accessible'] ) {
			//if post for not logged in users and user is not logged in
			if ( ! is_user_logged_in() ) {
				return $posts;
			} else {

				if ( current_user_can( 'administrator' ) ) {
					return $posts;
				}

				//if single post query
				if ( ! isset( $restriction['_um_noaccess_action'] ) || '0' == $restriction['_um_noaccess_action'] ) {
					return $this->clear_query();
				}
			}
		} elseif ( '2' == $restriction['_um_accessible'] ) {
			//if post for logged in users and user is not logged in
			if ( ! is_user_logged_in() ) {

				if ( ! isset( $restriction['_um_noaccess_action'] ) || '0' == $restriction['_um_noaccess_action'] ) {
					return $this->clear_query();
				}

			} else {

				if ( current_user_can( 'administrator' ) ) {
					return $posts;
				}

				$custom_restrict = apply_filters( 'um_custom_restriction', true, $restriction );

				if ( empty( $restriction['_um_access_roles'] ) ) {
					if ( $custom_restrict ) {
						return $posts;
					}
				} else {
					$user_can = UM()->access()->user_can( get_current_user_id(), $restriction['_um_access_roles'] );

					if ( isset( $user_can ) && $user_can && $custom_restrict ) {
						return $posts;
					}
				}

				//if single post query
				if ( ! isset( $restriction['_um_noaccess_action'] ) || '0' == $restriction['_um_noaccess_action'] ) {
					return $this->clear_query();
				}
			}
		}

		return $posts;
	}


	/**
	 * Clear Shop page content when there are not products
	 *
	 * @return array
	 */
	function clear_query() {
		remove_action( 'woocommerce_no_products_found', 'wc_no_products_found' );
		add_action( 'woocommerce_no_products_found', array( &$this, 'um_wc_access_message' ) );
		return array();
	}


	/**
	 * Show restriction message on shop page
	 */
	function um_wc_access_message() {
		$post_id = wc_get_page_id( 'shop' );

		$restricted_global_message = UM()->options()->get( 'restricted_access_message' );

		$restriction = UM()->access()->get_post_privacy_settings( get_post( $post_id ) );

		$message = '';
		//post is private
		if ( ! isset( $restriction['_um_restrict_by_custom_message'] ) || '0' == $restriction['_um_restrict_by_custom_message'] ) {
			$message = $restricted_global_message;
		} elseif ( '1' == $restriction['_um_restrict_by_custom_message'] ) {
			$message = ! empty( $restriction['_um_restrict_custom_message'] ) ? $restriction['_um_restrict_custom_message'] : '';
		}

		echo $message;
	}
}