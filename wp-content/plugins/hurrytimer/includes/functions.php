<?php

use Hurrytimer\Campaign;

if ( ! function_exists( 'hurryt_wc_stock_statuses' ) ) {
    /**
     * Returns WooCommerce stock status as array.
     *
     * @return array
     */
    function hurryt_wc_stock_statuses() {
        return [
            [
                'name' => __( 'In stock', 'hurrytimer' ),
                'id'   => \Hurrytimer\C::WC_IN_STOCK,
            ],
            [
                'name' => __( 'Out of stock', 'hurrytimer' ),
                'id'   => \Hurrytimer\C::WC_OUT_OF_STOCK,
            ],
            [
                'name' => __( 'On backorder', 'hurrytimer' ),
                'id'   => \Hurrytimer\C::WC_ON_BACKORDER,
            ],
        ];
    }
}

if ( ! function_exists( 'hurryt_wc_conditions' ) ) {

    /**
     * Returns supported conditions.
     *
     * @return array
     */
    function hurryt_wc_conditions() {
        return ( new \Hurrytimer\ConditionalLogic() )->addRule( [
            'key'       => 'stock_status',
            'name'      => __( 'Stock status', 'hurrytimer' ),
            'operators' => [ '==', '!=' ],
            'values'    => hurryt_wc_stock_statuses(),
            'type'      => 'string',
        ] )->addRule( [
            'key'       => 'stock_quantity',
            'name'      => __( 'Stock quantity', 'hurrytimer' ),
            'operators' => [ '==', '!=', '<', '>' ],
            'values'    => [],
            'type'      => 'number',
        ] )->addRule( [
            'key'       => 'shipping_class',
            'name'      => __( 'Shipping class', 'hurrytimer' ),
            'operators' => [ '==', '!=' ],
            'values'    => array_map( function ( $class ) {
                return [
                    'name' => $class->name,
                    'id'   => $class->term_id,
                ];
            }, \WC_Shipping::instance()->get_shipping_classes() ),
        ] )->get();
    }
}

if ( ! function_exists( 'hurryt_tz' ) ) {
    /**
     * Return WP timezone string/offset.
     *
     * @return mixed|string|void
     */
    function hurryt_tz() {
        $tz_string = get_option( 'timezone_string' );
        if ( ! empty( $tz_string ) ) {
            return $tz_string;
        }
        $offset  = get_option( 'gmt_offset' );
        $hours   = (int)$offset;
        $minutes = abs( ( $offset - (int)$offset ) * 60 );
        $offset  = sprintf( '%+03d:%02d', $hours, $minutes );

        return $offset;
    }
}

if ( ! function_exists( 'hurryt_current_page_id' ) ) {

    /**
     * Get current page ID.
     *
     * @return int|mixed
     */
    function hurryt_current_page_id() {
        $object_id = get_queried_object_id();
        if ( ! hurryt_is_woocommerce_activated() ) {
            return $object_id;
        }
        $wc_ids = [
            'shop'         => get_option( 'woocommerce_shop_page_id' ),
            'cart'         => get_option( 'woocommerce_cart_page_id' ),
            'checkout'     => get_option( 'woocommerce_checkout_page_id' ),
            'checkout_pay' => get_option( 'woocommerce_pay_page_id' ),
            'thanks'       => get_option( 'woocommerce_thanks_page_id' ),
            'myaccount'    => get_option( 'woocommerce_myaccount_page_id' ),
            'edit_address' => get_option( 'woocommerce_edit_address_page_id' ),
            'view_order'   => get_option( 'woocommerce_view_order_page_id' ),
            'terms'        => get_option( 'woocommerce_terms_page_id' ),
        ];
        if ( is_shop() ) {
            $object_id = $wc_ids[ 'shop' ];
        } elseif ( is_account_page() ) {
            $object_id = $wc_ids[ 'myaccount' ];
        } elseif ( is_checkout_pay_page() ) {
            $object_id = $wc_ids[ 'checkout_pay' ];
        } elseif ( is_checkout() ) {
            $object_id = $wc_ids[ 'checkout' ];
        } elseif ( is_cart() ) {
            $object_id = $wc_ids[ 'cart' ];
        } elseif ( is_view_order_page() ) {
            $object_id = $wc_ids[ 'view_order' ];
        } elseif ( is_view_order_page() ) {
            $object_id = $wc_ids[ 'view_order' ];
        } elseif ( is_view_order_page() ) {
            $object_id = $wc_ids[ 'view_order' ];
        } elseif ( is_view_order_page() ) {
            $object_id = $wc_ids[ 'view_order' ];
        }

        return $object_id;
    }
}

if ( ! function_exists( 'hurryt_is_woocommerce_activated' ) ) {
    /**
     * Returns true if WC is active.
     *
     * @return bool
     */
    function hurryt_is_woocommerce_activated() {
        return class_exists( 'woocommerce' );
    }
}

if ( ! function_exists( 'hurryt_settings' ) ) {
    /**
     * Returns plugin settings array.
     *
     * @return array
     */
    function hurryt_settings() {
        $defaults = [ 'disable_actions' => 0 ];

        return wp_parse_args( get_option( 'hurryt_settings', [] ), $defaults );
    }
}

if ( ! function_exists( 'hurryt_get_campaign' ) ) {
    /**
     * Create a campaign instance with all props.
     * @param $id
     *
     * @return Campaign
     */
    function hurryt_get_campaign( $id ) {
        $campaign = new Campaign( $id );
        $campaign->loadSettings();

        return $campaign;
    }
}

if ( ! function_exists( 'hurryt_is_admin_area' ) ) {
    function hurryt_is_admin_area() {
        global $wp;
        wp_parse_str( $wp->query_string, $params );

        return is_admin()
               || is_preview()
               || is_customize_preview()
               || ! empty( $params[ 'preview' ] )
               || ! empty( $params[ 'fl_builder' ] );
    }
}

if ( ! function_exists( 'hurryt_parse_campaigns' ) ) {

    /**
     * Parse campaigns shortcodes in the given string content.
     */
    function hurryt_parse_campaigns( $content ) {
        $pattern = get_shortcode_regex();
        $tag     = 'hurrytimer';
        $ids     = [];
        if ( preg_match_all( '/' . $pattern . '/s', $content, $matches )
             && array_key_exists( 2, $matches )
             && in_array( $tag, $matches[ 2 ] )
        ) {
            foreach ( (array)$matches[ 2 ] as $key => $value ) {
                if ( $tag === $value ) {
                    $ids[] = shortcode_parse_atts( $matches[ 3 ][ $key ] );
                }
            }
            $ids = array_map( function ( $id ) {
                return isset( $id[ 'id' ] ) ? absint( $id[ 'id' ] ) : null;
            }, $ids );

            return array_filter( $ids );
        }

        return [];
    }
}

if ( ! function_exists( 'hurryt_count_active_campaigns' ) ) {
    /**
     * Returns published campaigns.
     */
    function hurryt_count_active_campaigns() {
        $count = wp_count_posts( HURRYT_POST_TYPE );

        return absint( $count->publish );
    }
}




