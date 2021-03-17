<div class="um-admin-metabox">
	<?php $role = $object['data'];

	UM()->admin_forms( array(
		'class'		=> 'um-role-woocommerce um-half-column',
		'prefix_id'	=> 'role',
		'fields' => array(
			array(
				'id'		    => '_um_woo_purchases_tab',
				'type'		    => 'checkbox',
				'label'		    => __( 'Display purchases tab in profile?', 'um-woocommerce' ),
				'value'		    => isset( $role['_um_woo_purchases_tab'] ) ? $role['_um_woo_purchases_tab'] : 1,
			),
			array(
				'id'		    => '_um_woo_reviews_tab',
				'type'		    => 'checkbox',
				'label'		    => __( 'Display reviews tab in profile?', 'um-woocommerce' ),
				'value'		    => isset( $role['_um_woo_reviews_tab'] ) ? $role['_um_woo_reviews_tab'] : 1,
			),
			array(
				'id'		    => '_um_woo_account_billing',
				'type'		  => 'checkbox',
				'label'		  => __( 'Display billing address under account?', 'um-woocommerce' ),
				'tooltip'   => __( 'Depends on option <em>Hide billing tab from members in account page</em> on [Ultimate Member > Settings > Extensions > Woocommerce] page.', 'um-messaging' ),
				'value'		  => isset( $role['_um_woo_account_billing'] ) ? $role['_um_woo_account_billing'] : 1,
			),
			array(
				'id'		    => '_um_woo_account_shipping',
				'type'		  => 'checkbox',
				'label'		  => __( 'Display shipping address under account?', 'um-woocommerce' ),
				'tooltip'   => __( 'Depends on option <em>Hide shipping tab from members in account page</em> on [Ultimate Member > Settings > Extensions > Woocommerce] page.', 'um-messaging' ),
				'value'		  => isset( $role['_um_woo_account_shipping'] ) ? $role['_um_woo_account_shipping'] : 1,
			),
			array(
				'id'		    => '_um_woo_account_orders',
				'type'		    => 'checkbox',
				'label'		    => __( 'Display orders under account?', 'um-woocommerce' ),
				'value'		    => isset( $role['_um_woo_account_orders'] ) ? $role['_um_woo_account_orders'] : 1,
			),
			array(
				'id'		    => '_um_woo_account_subscription',
				'type'		    => 'checkbox',
				'label'		    => __( 'Display subscriptions under account?', 'um-woocommerce' ),
				'value'		    => isset( $role['_um_woo_account_subscription'] ) ? $role['_um_woo_account_subscription'] : 1,
			),
			array(
				'id'		    => '_um_woo_account_downloads',
				'type'		  => 'checkbox',
				'label'		  => __( 'Display downloads under account?', 'um-woocommerce' ),
				'value'		  => isset( $role['_um_woo_account_downloads'] ) ? $role['_um_woo_account_downloads'] : 1,
			),
			array(
				'id'		    => '_um_woo_account_payment_methods',
				'type'		  => 'checkbox',
				'label'		  => __( 'Display payment methods under account?', 'um-woocommerce' ),
				'value'		  => isset( $role['_um_woo_account_payment_methods'] ) ? $role['_um_woo_account_payment_methods'] : 0,
			),

		)
	) )->render_form(); ?>
	
	<div class="um-admin-clear"></div>
</div>