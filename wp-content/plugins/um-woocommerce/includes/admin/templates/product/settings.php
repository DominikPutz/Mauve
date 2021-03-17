<div class="um-admin-metabox">
	<?php $roles = array( '' => __( 'None','um-woocommerce' ) ) + UM()->roles()->get_roles();

    $um_woo_product_role = (string)get_post_meta( get_the_ID(), '_um_woo_product_role', true );
    $um_woo_product_role = ( $um_woo_product_role ) ? $um_woo_product_role : '';

    $meta_fields = array(
        array(
            'id'		    => '_um_woo_product_role',
            'type'		    => 'select',
            'label'    		=> __( 'When this product is bought move user to this role', 'um-woocommerce' ),
            'value'		    => $um_woo_product_role,
            'options'       => $roles,
        )
    );

    if ( function_exists( 'wcs_get_subscription' ) ) {

        $um_woo_product_activated_role = (string)get_post_meta( get_the_ID(), '_um_woo_product_activated_role', true );
        $um_woo_product_activated_role = ( $um_woo_product_activated_role ) ? $um_woo_product_activated_role : '';

        $meta_fields[] = array(
            'id'		    => '_um_woo_product_activated_role',
            'type'		    => 'select',
            'label'    		=> __( 'When subscription is ACTIVATED move user to this role', 'um-woocommerce' ),
            'value'		    => $um_woo_product_activated_role,
            'options'       => $roles,
        );

        $um_woo_product_downgrade_pending_role = (string)get_post_meta( get_the_ID(), '_um_woo_product_downgrade_pending_role', true );
        $um_woo_product_downgrade_pending_role = ( $um_woo_product_downgrade_pending_role ) ? $um_woo_product_downgrade_pending_role : '';

        $meta_fields[] = array(
            'id'		    => '_um_woo_product_downgrade_pending_role',
            'type'		    => 'select',
            'label'    		=> __( 'When subscription is PENDING move user to this role', 'um-woocommerce' ),
            'value'		    => $um_woo_product_downgrade_pending_role,
            'options'       => $roles,
        );

        $um_woo_product_downgrade_onhold_role = (string)get_post_meta( get_the_ID(), '_um_woo_product_downgrade_onhold_role', true );
        $um_woo_product_downgrade_onhold_role = ( $um_woo_product_downgrade_onhold_role ) ? $um_woo_product_downgrade_onhold_role : '';

        $meta_fields[] = array(
            'id'		    => '_um_woo_product_downgrade_onhold_role',
            'type'		    => 'select',
            'label'    		=> __( 'When subscription is ON-HOLD move user to this role', 'um-woocommerce' ),
            'value'		    => $um_woo_product_downgrade_onhold_role,
            'options'       => $roles,
        );

        $um_woo_product_downgrade_expired_role = (string)get_post_meta( get_the_ID(), '_um_woo_product_downgrade_expired_role', true );
        $um_woo_product_downgrade_expired_role = ( $um_woo_product_downgrade_expired_role ) ? $um_woo_product_downgrade_expired_role : '';

        $meta_fields[] = array(
            'id'		    => '_um_woo_product_downgrade_expired_role',
            'type'		    => 'select',
            'label'    		=> __( 'When subscription is EXPIRED move user to this role', 'um-woocommerce' ),
            'value'		    => $um_woo_product_downgrade_expired_role,
            'options'       => $roles,
        );

        $um_woo_product_downgrade_cancelled_role = (string)get_post_meta( get_the_ID(), '_um_woo_product_downgrade_cancelled_role', true );
        $um_woo_product_downgrade_cancelled_role = ( $um_woo_product_downgrade_cancelled_role ) ? $um_woo_product_downgrade_cancelled_role : '';

        $meta_fields[] = array(
            'id'		    => '_um_woo_product_downgrade_cancelled_role',
            'type'		    => 'select',
            'label'    		=> __( 'When subscription is CANCELLED move user to this role', 'um-woocommerce' ),
            'value'		    => $um_woo_product_downgrade_cancelled_role,
            'options'       => $roles,
        );

				//pending-cancel
				$um_woo_product_downgrade_pendingcancel_role = (string) get_post_meta( get_the_ID(), '_um_woo_product_downgrade_pendingcancel_role', true );

				$meta_fields[] = array(
					'id'			 => '_um_woo_product_downgrade_pendingcancel_role',
					'type'		 => 'select',
					'label'		 => __( 'When subscription is PENDING-CANCEL move user to this role', 'um-woocommerce' ),
					'value'		 => $um_woo_product_downgrade_pendingcancel_role,
					'options'	 => $roles,
				);
		}

    UM()->admin_forms( array(
        'class'		=> 'um-wc-product-settings um-half-column',
        'prefix_id'	=> '',
        'fields' => $meta_fields
    ) )->render_form(); ?>

    <div class="um-admin-clear"></div>
</div>