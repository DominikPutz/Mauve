<?php

namespace Hurrytimer;

use Carbon\Carbon;
use Hurrytimer\Utils\Helpers;

class Admin {
    /**
     * The ID of this plugin.
     *
     * @since    1.0.0
     * @access   private
     * @var      string $pluginName The ID of this plugin.
     */
    private $pluginName;

    /**
     * The version of this plugin.
     *
     * @since    1.0.0
     * @access   private
     * @var      string $version The current version of this plugin.
     */
    private $version;

    /**
     * Initialize the class and set its properties.
     *
     * @param string $pluginName The name of this plugin.
     * @param string $version    The version of this plugin.
     *
     * @since    1.0.0
     *
     */
    public function __construct( $pluginName, $version ) {
        $this->pluginName = $pluginName;
        $this->version    = $version;
        add_action( 'wp_insert_post', [ $this, 'regenerate_css' ], 10, 3 );
        add_action( 'wp_ajax_wcSearchProducts', [ $this, 'ajaxWcSearchProducts' ] );
        add_action( 'wp_ajax_add_wc_condition_group', [ $this, 'ajaxAddWcConditionGroup' ] );
        add_action( 'wp_ajax_add_wc_condition', [ $this, 'ajaxAddWcCondition' ] );
        add_action( 'wp_ajax_load_wc_condition', [ $this, 'ajaxLoadWcCondition' ] );
        add_action( 'admin_init', [ $this, 'activateCampaign' ] );
        add_action( 'admin_init', [ $this, 'deactivateCampaign' ] );
        add_action( 'admin_init', [ $this, 'resetEvergreenCampaign' ] );
        add_action( 'admin_init', [ $this, 'resetAllEvergreenCampaigns' ] );
        add_action( 'admin_init', [ $this, 'pluginSettings' ] );
        add_filter( 'post_row_actions', [ $this, 'campaignsListRowActions' ] );
        add_action( 'admin_menu', [ $this, 'menu' ] );
        add_filter( 'bulk_actions-edit-' . HURRYT_POST_TYPE,
            [ $this, 'campaignsListBulkActions', ] );

        add_filter( 'manage_' . HURRYT_POST_TYPE . '_posts_columns',
            [ $this, 'campaignsListColumns', ] );


        add_action( 'manage_' . HURRYT_POST_TYPE . '_posts_custom_column',
            [ $this, 'countdownListColumnsContent' ], 10, 2 );

        add_filter( 'bulk_post_updated_messages',
            [ $this, 'customBulkPostUpdatedMessages' ], 10, 2 );

        add_action( 'admin_menu', [ $this, 'helpTabs' ] );
        add_action( 'admin_notices', [ $this, 'resetEvergreenCampaignNotice' ] );
        add_action( 'admin_notices', [ $this, 'resetAllEvergreenCampaignNotice' ] );

        add_action( 'admin_notices', [ $this, 'countdown_activation_notice' ] );
        add_filter( 'admin_footer_text', [ $this, 'admin_footer_text' ] );
        add_filter( 'wp_insert_post_data', [ $this, 'mark_post_being_saved' ] );

        add_filter( 'plugin_action_links_' . HURRYT_BASENAME, [ $this, 'set_plugin_action_links' ] );

    }

    function mark_post_being_saved( $data ) {

        global $hurryt_saving_post;

        $hurryt_saving_post = 1;

        return $data;
    }

    /**
     * Generate published campaigns CSS file.
     *
     * @param $post_id
     * @param $post
     * @param $update
     */
    function regenerate_css( $post_id, $post, $update ) {
        if ( wp_is_post_revision( $post_id ) ) {
            return;
        }

        if ( $post->post_type !== HURRYT_POST_TYPE ) {
            return;
        }

        if ( $update ) {
            // TODO: Check if custom CSS has been changed.
            Campaign::generate_css();
        }

    }

    public function admin_footer_text() {
        $screen = get_current_screen();
        if ( $screen->post_type === HURRYT_POST_TYPE ) {
            include HURRYT_DIR . 'admin/templates/footer-rate-plugin.php';
        }
    }

    public function set_plugin_action_links( $links ) {
        $settings_url    = admin_url( 'admin.php?page=hurrytimer_settings' );

        $settings_anchor = '<a href="' . $settings_url . '">' . __( 'Settings' ) . '</a>';

        array_unshift( $links, $settings_anchor );
    //removeIf(pro)
        $manage_url    = admin_url( 'edit.php?post_type=' . HURRYT_POST_TYPE);
        $manage_anchor = '<a href="' . $manage_url . '">' . __( 'Manage campaigns', 'hurrytimer' ) . '</a>';
        array_unshift( $links, $manage_anchor );

    $links[]
            = '<a href="https://hurrytimer.com/?utm_source=plugin&utm_medium=installed_plugins&utm_campaign=go_pro" target="_blank" class="hurryt-text-green-500 hurryt-font-bold">Go Pro</a>';
    
    //endRemoveIf(pro)

        return $links;
    }


    public function countdown_activation_notice() {
        $post_id = filter_input( INPUT_GET, 'post', FILTER_VALIDATE_INT );
        $status  = filter_input( INPUT_GET, 'hurrytimer_action' );

        if ( ! $post_id || ! $status || ! ( $post = get_post( $post_id ) ) ) {
            return;
        }
        if (
            $status === "activate-compaign"
            &&
            ! Utils\Helpers::isPostActive( $post_id )
        ) { ?>
            <div class="notice notice-success is-dismissible">
                <p><?php _e(
                        'Countdown timer deactivated.',
                        "hurrytimer"
                    ); ?></p>
            </div>
        <?php } else { ?>
            <div class="notice notice-success is-dismissible">
                <p><?php _e( 'Countdown timer activated.', "hurrytimer" ); ?></p>
            </div>
        <?php }
    }

    public function helpTabs() {
        add_action( 'load-edit.php', [ $this, 'editHelpTabs' ] );
        add_action( 'load-post-new.php', [ $this, 'editHelpTabs' ] );
        add_action( 'load-post.php', [ $this, 'editHelpTabs' ] );
    }

    public function editHelpTabs() {
        $screen = get_current_screen();
        if ( strpos( $screen->id, 'hurrytimer_countdown' ) === false ) {
            return;
        }
        $screen->remove_help_tabs();
    }

    public function customBulkPostUpdatedMessages( $bulk_messages, $bulk_counts ) {
        $bulk_messages[ HURRYT_POST_TYPE ] = [
            'updated'   => _n(
                '%s countdown timer updated.',
                '%s countdown timers updated.',
                $bulk_counts[ 'updated' ]
            ),
            'locked'    => _n(
                '%s countdown timer not updated, somebody is editing it.',
                '%s countdown timers not updated, somebody is editing them.',
                $bulk_counts[ 'locked' ]
            ),
            'deleted'   => _n(
                '%s countdown timer permanently deleted.',
                '%s countdown timers permanently deleted.',
                $bulk_counts[ 'deleted' ]
            ),
            'trashed'   => _n(
                '%s countdown timer deleted.',
                '%s countdown timers deleted.',
                $bulk_counts[ 'trashed' ]
            ),
            'untrashed' => _n(
                '%s countdown timer restored from the Trash.',
                '%s countdown timers restored from the Trash.',
                $bulk_counts[ 'untrashed' ]
            ),
        ];

        return $bulk_messages;
    }

    public function countdownListColumnsContent( $column, $post_id ) {
        $campaign = new Campaign( $post_id );
        switch ( $column ) {
            case 'status':
                echo $campaign->is_published()
                    ? __( 'Active', "hurrytimer" )
                    : __( 'Inactive', "hurrytimer" );
                break;
            case 'mode':
                if ( $campaign->is_evergreen() ) {
                    echo __( 'Evergreen', "hurrytimer" );
                }
                if ( $campaign->is_one_time() ) {
                    echo __( 'One-Time', "hurrytimer" );
                }
                if ( $campaign->is_recurring() ) {
                    echo __( 'Recurring', "hurrytimer" );
                }
                break;
            case 'CampaignShortcode':
                echo '<pre class="htmr-list-shortcode">[hurrytimer id="' .
                     $post_id .
                     '"]</pre>';
                break;
        }
    }

    public function campaignsListBulkActions( $actions ) {
        unset( $actions[ 'edit' ] );

        return $actions;
    }

    public function campaignsListColumns( $columns ) {
        $allowed_columns = [];
        foreach ( $columns as $column_name => $value ) {
            if ( in_array( $column_name, [ 'cb', 'title', 'date' ] ) ) {
                $allowed_columns[ $column_name ] = $value;
            }
        }

        $columns = $allowed_columns;
        $date    = $columns[ 'date' ];
        unset( $columns[ 'date' ] );
        $columns           = array_merge( $columns, [
            'status'            => __( 'Status', "hurrytimer" ),
            'mode'              => __( 'Mode', "hurrytimer" ),
            'CampaignShortcode' => __( 'CampaignShortcode', "hurrytimer" ),
        ] );
        $columns[ 'date' ] = $date;

        return $columns;
    }

    /**
     * Register the stylesheets for the admin area.
     *
     * @since    1.0.0
     */
    public function enqueueStyles() {
        $version = defined( 'WP_DEBUG' ) && WP_DEBUG ? time() : $this->version;
        wp_enqueue_style( 'wp-color-picker' );
        wp_enqueue_style( 'iris' );
        wp_dequeue_style( 'select2' );

        wp_enqueue_style(
            "codemirror",
            HURRYT_URL . 'assets/css/codemirror.css',
            [],
            "5.42.2",
            'all'
        );
        wp_enqueue_style(
            'hurryt-select2',
            HURRYT_URL . 'assets/css/select2.css',
            array(),
            '4.0.5',
            'all'
        );

        wp_enqueue_style(
            'hurryt-base-css',
            HURRYT_URL . 'assets/css/base.css',
            array(),
            $version,
            'all'
        );

        wp_enqueue_style(
            $this->pluginName,
            HURRYT_URL . 'assets/css/admin.css',
            [ 'hurryt-base-css', 'hurryt-select2' ],
            $version,
            'all'
        );
    }

    public function campaignsListRowActions( $actions ) {
        global $post;
        if ( $post->post_type === HURRYT_POST_TYPE ) {
            unset( $actions[ 'inline hide-if-no-js' ] );
        }

        return $actions;
    }

    /**
     * Register the JavaScript for the admin area.
     *
     * @since    1.0.0
     */
    public function enqueueScripts() {
        wp_enqueue_script( 'jquery-ui-core' );
        wp_enqueue_script( 'jquery-ui-tooltip' );
        wp_enqueue_script( 'wp-color-picker' );
        wp_enqueue_script( 'iris' );
        wp_enqueue_script( 'jquery-ui-slider' );
        wp_dequeue_script( 'select2' );

        wp_enqueue_script(
            'hurryt-cookie',
            HURRYT_URL . 'assets/js/cookie.min.js',
            [],
            '2.2.0',
            true
        );

        wp_enqueue_script(
            'jq-date-picker-addon',
            HURRYT_URL . 'assets/js/timepicker-addon.js',
            [ 'jquery-ui-datepicker' ],
            '1.6.3',
            true
        );

        wp_enqueue_script(
            'hurryt-select2',
            HURRYT_URL . 'assets/js/select2.min.js',
            [ 'jquery' ],
            '4.0.5',
            true
        );

        $deps = [ 'hurryt-cookie', 'jq-date-picker-addon', 'hurryt-select2' ];

        wp_enqueue_script(
            $this->pluginName,
            HURRYT_URL . 'assets/js/admin.js',
            $deps,
            $this->version,
            true
        );

        wp_localize_script( $this->pluginName, 'hurrytimer_ajax_object', array(
            'ajax_nonce'        => wp_create_nonce( 'hurryt-admin' ),
            'ajax_url'          => admin_url( 'admin-ajax.php' ),
            'headline_pos'      => [
                'above_timer' => C::HEADLINE_POSITION_ABOVE_TIMER,
                'below_timer' => C::HEADLINE_POSITION_BELOW_TIMER,
            ],
            'mode'              => [
                'evergreen' => C::MODE_EVERGREEN,
                'regular'   => C::MODE_REGULAR,
                'recurring' => C::MODE_RECURRING,
            ],
            'browser_tz_offset' => Carbon::now( hurryt_tz() )->getOffset() / 60,
        ) );

    }

    public function menu() {
   
        add_menu_page(
            'HurryTimer',
            'HurryTimer',
            apply_filters('hurryt_admin_capability', 'edit_posts', 'campaigns_list'),
            'hurrytimer',
            null,
            'data:image/svg+xml;base64,PHN2ZyB4bWxucz0iaHR0cDovL3d3dy53My5vcmcvMjAwMC9zdmciIHhtbG5zOnhsaW5rPSJodHRwOi8vd3d3LnczLm9yZy8xOTk5L3hsaW5rIiB2aWV3Qm94PSIwIDAgNjkuNTYgNzAuNjIiPiAgPGRlZnM+ICAgIDxsaW5lYXJHcmFkaWVudCBpZD0iYzNiZGI3MTYtYmI3Ny00MzUxLTllOGYtYTUwY2Y3ODEzNDNlIiB5MT0iMzUuMzEiIHgyPSI2NS4wNyIgeTI9IjM1LjMxIiBncmFkaWVudFVuaXRzPSJ1c2VyU3BhY2VPblVzZSI+ICAgICAgPHN0b3Agb2Zmc2V0PSIwIiBzdG9wLWNvbG9yPSIjZjg1MzdmIi8+ICAgICAgPHN0b3Agb2Zmc2V0PSIxIiBzdG9wLWNvbG9yPSIjNjNjIi8+ICAgIDwvbGluZWFyR3JhZGllbnQ+ICAgIDxsaW5lYXJHcmFkaWVudCBpZD0iYzk5ODViMWMtYjg3Zi00M2Y1LWFiMzItNWE4ZWZiMzQ1ZDA1IiB4MT0iMTUuODgiIHkxPSIyOS4zIiB4Mj0iNjkuNTYiIHkyPSIyOS4zIiB4bGluazpocmVmPSIjYzNiZGI3MTYtYmI3Ny00MzUxLTllOGYtYTUwY2Y3ODEzNDNlIi8+ICA8L2RlZnM+ICA8dGl0bGU+QXNzZXQgMzwvdGl0bGU+ICA8ZyBpZD0iMGIwNjBiYzEtNGVlNS00YjUwLTkxMjctYTFjZWRmNGYxZDljIiBkYXRhLW5hbWU9IkxheWVyIDIiPiAgICA8ZyBpZD0iNmRiZGQyZWItOWY5My00YWMwLWE3YTQtYjE0ODY1MDQyYzNiIiBkYXRhLW5hbWU9IkxheWVyIDEiPiAgICAgIDxnPiAgICAgICAgPHBhdGggZD0iTTYzLjU5LDI4LjMzYTIuODUsMi44NSwwLDAsMC0zLjg0LTEuNzRsLS4wNSwwYTIuODgsMi44OCwwLDAsMC0xLjYsMy41M2MuMjYuODQuNDgsMS42OS42NSwyLjU1YTI3LDI3LDAsMCwxLDAsMTAuNzksMjYuNTksMjYuNTksMCwwLDEtNCw5LjU2LDI2Ljg0LDI2Ljg0LDAsMCwxLTExLjc3LDkuNywyNi42LDI2LjYsMCwwLDEtNSwxLjU2LDI3LjE3LDI3LjE3LDAsMCwxLTEwLjc5LDAsMjYuNTksMjYuNTksMCwwLDEtOS41Ni00LDI2Ljg0LDI2Ljg0LDAsMCwxLTkuNy0xMS43NywyNi42LDI2LjYsMCwwLDEtMS41Ni01LDI3LDI3LDAsMCwxLDAtMTAuNzksMjYuNTksMjYuNTksMCwwLDEsNC05LjU2LDI2Ljg0LDI2Ljg0LDAsMCwxLDExLjc3LTkuNywyNi42LDI2LjYsMCwwLDEsNS0xLjU2LDI3LjE3LDI3LjE3LDAsMCwxLDEwLjc5LDAsMjYuNiwyNi42LDAsMCwxLDUsMS41NnExLjE5LjUsMi4zMywxLjEyQTIuODMsMi44MywwLDAsMCw0OSwxMy42OGwuMDYtLjA5YTIuODUsMi44NSwwLDAsMC0xLTQuMVE0Ni42OCw4LjczLDQ1LjIsOC4xYTMyLjM5LDMyLjM5LDAsMCwwLTQuNjktMS41N1YyLjQxQTIuNDEsMi40MSwwLDAsMCwzOC4xLDBIMjguNDJBMi40MSwyLjQxLDAsMCwwLDI2LDIuNDFWNi4yaDBhMzIuMzcsMzIuMzcsMCwwLDAtMTEuNjQsNC45Yy0uMzUuMjQtLjY5LjQ5LTEsLjc0TDExLjQ4LDEwYTIuNDEsMi40MSwwLDAsMC0zLjQxLDBMNC44MiwxMy4yNWEyLjQxLDIuNDEsMCwwLDAsMCwzLjQxTDYuNiwxOC40NGMtLjM2LjQ3LS43MSwxLTEsMS40NWEzMi41MywzMi41MywwLDEsMCw1OCw4LjQ0WiIgc3R5bGU9ImZpbGw6IHVybCgjYzNiZGI3MTYtYmI3Ny00MzUxLTllOGYtYTUwY2Y3ODEzNDNlKSIvPiAgICAgICAgPHBhdGggZD0iTTU3LjY3LDEyLjk1bDEsMEwzOS45NCwzNC4yOSwzNSwzMC40YTIuNDEsMi40MSwwLDAsMC0zLjI0LjI0TDE2LjU0LDQ2LjcyYTIuNDEsMi40MSwwLDAsMCwuMDksMy40MWgwQTIuNDEsMi40MSwwLDAsMCwyMCw1MGwxMy43LTE0LjQ5LDUsMy45NGEyLjQxLDIuNDEsMCwwLDAsMy4zLS4zMUw2OS41Niw3LjgxbC0xMiwuMzJhMi40MSwyLjQxLDAsMCwwLC4xMyw0LjgyWiIgc3R5bGU9ImZpbGw6IHVybCgjYzk5ODViMWMtYjg3Zi00M2Y1LWFiMzItNWE4ZWZiMzQ1ZDA1KSIvPiAgICAgIDwvZz4gICAgPC9nPiAgPC9nPjwvc3ZnPg=='
        );
        add_submenu_page(
            'hurrytimer',
            __( 'Add Campaign', 'hurrytimer' ),
            __( 'Add Campaign', 'hurrytimer' ),
            apply_filters('hurryt_admin_capability', 'edit_posts', 'add_campaign'),
            'post-new.php?post_type=hurrytimer_countdown'
        );

        add_submenu_page(
            'hurrytimer',
            __( 'Settings', 'hurrytimer' ),
            __( 'Settings', 'hurrytimer' ),
            apply_filters('hurryt_admin_capability', 'manage_options', 'settings'),
            'hurrytimer_settings',
            [ $this, 'settings' ]
        );
    }

    private function get_menu_position( $position, $menu_slug, $menu_title) {
        global $wp_version;
        
        if( version_compare($wp_version, '4.4', '<') ){
            return  $position + substr( base_convert( md5( $menu_slug . $menu_title ), 16, 10 ) , -5 ) * 0.00001;
        }
        return $position;
    }

    public function settings() {
        include_once HURRYT_DIR . 'admin/templates/settings.php';
    }

    public function activateCampaign() {
        if (
            isset( $_GET[ 'hurrytimer_action' ] )
            && $_GET[ 'hurrytimer_action' ] === "activate-compaign"
            && isset( $_GET[ '_wpnonce' ] )
            && wp_verify_nonce( $_GET[ '_wpnonce' ], 'activate-compaign' )
        ) {
            $postId = intval( $_GET[ 'post' ] );
            wp_update_post( [ 'ID' => $postId, 'post_status' => 'publish' ] );
        }
    }

    function resetEvergreenCampaignNotice() {
        if (
            isset( $_GET[ 'hurryt-action' ] )
            && isset( $_GET[ 'hurryt-scope' ] )
            && isset( $_GET[ 'post' ] )
            && $_GET[ 'hurryt-action' ] === "reset-evergreen-compaign"
            && isset( $_GET[ '_wpnonce' ] )
            && wp_verify_nonce( $_GET[ '_wpnonce' ], 'reset-evergreen-compaign' )
        ) {
            $scopeText
                = $_GET[ 'hurryt-scope' ] === 'admin'
                ? ' you '
                : ' all visitors '; ?>
            <div class="notice notice-success is-dismissible">
                <p><?php _e(
                        "Campaign has been reset for $scopeText successfully.",
                        'hurrytimer'
                    ); ?></p>
            </div>
            <?php
        }
    }

    function resetAllEvergreenCampaignNotice() {
        if (
            isset( $_GET[ 'hurryt-page' ] )
            && isset( $_GET[ 'hurryt-scope' ] )
            && isset( $_GET[ 'hurryt-action' ] )
            && $_GET[ 'hurryt-page' ] === "hurrytimer_settings"
            && $_GET[ 'hurryt-action' ] === "reset-all-evergreen-compaigns"
            && isset( $_GET[ '_wpnonce' ] )
            && wp_verify_nonce( $_GET[ '_wpnonce' ], 'reset-all-evergreen-compaigns' )
        ) {
            $scopeText
                = $_GET[ 'hurryt-scope' ] === 'admin'
                ? ' you '
                : ' all visitors '; ?>
            <div class="notice notice-success is-dismissible">
                <p><?php _e(
                        "All evergreen campaigns have been reset for $scopeText successfully.",
                        'hurrytimer'
                    ); ?></p>
            </div>
            <?php
        }
    }

    public function resetEvergreenCampaign() {
        if (
            isset( $_GET[ 'hurryt-action' ] )
            && isset( $_GET[ 'hurryt-scope' ] )
            && isset( $_GET[ 'post' ] )
            && $_GET[ 'hurryt-action' ] === "reset-evergreen-compaign"
            && isset( $_GET[ '_wpnonce' ] )
            && wp_verify_nonce( $_GET[ '_wpnonce' ], 'reset-evergreen-compaign' )
        ) {
            $postId          = intval( $_GET[ 'post' ] );
            $IPDetection     = new IPDetection();
            $cookieDetection = new CookieDetection();
            $campaign        = new EvergreenCampaign(
                $postId,
                $cookieDetection,
                $IPDetection
            );
            $campaign->reset( sanitize_text_field( $_GET[ 'hurryt-scope' ] ) );
        }
    }

    public function resetAllEvergreenCampaigns() {
        if (
            isset( $_GET[ 'hurryt-page' ] )
            && isset( $_GET[ 'hurryt-scope' ] )
            && isset( $_GET[ 'hurryt-action' ] )
            && $_GET[ 'hurryt-page' ] === "hurrytimer_settings"
            && $_GET[ 'hurryt-action' ] === "reset-all-evergreen-compaigns"
            && isset( $_GET[ '_wpnonce' ] )
            && wp_verify_nonce( $_GET[ '_wpnonce' ], 'reset-all-evergreen-compaigns' )
        ) {
            $evergreenCampaign = new EvergreenCampaign( null, new CookieDetection(),
                new IPDetection() );
            $evergreenCampaign->resetAll( sanitize_text_field( $_GET[ 'hurryt-scope' ] ) );
        }
    }

    public function deactivateCampaign() {
        if (
            isset( $_GET[ 'hurrytimer_action' ] )
            && $_GET[ 'hurrytimer_action' ] === "deactivate-compaign"
            && isset( $_GET[ '_wpnonce' ] )
            && wp_verify_nonce( $_GET[ '_wpnonce' ], 'deactivate-compaign' )
        ) {
            $postId = intval( $_GET[ 'post' ] );
            wp_update_post( [ 'ID' => $postId, 'post_status' => 'draft' ] );
        }
    }

    public function settingsBox() {
        require plugin_dir_path( dirname( __FILE__ ) ) .
                'admin/CampaignSettings.php';
        new CampaignSettings();
    }

    //removeIf(pro)
    public function upgradeBanner() {
        add_meta_box(
            'hurrytimer-upgrade-banner',
            __( 'Get the Most of HurryTimer', 'hurrytimer' ),
            function () {
                include HURRYT_DIR . 'admin/templates/upgrade-banner.php';
            },
            HURRYT_POST_TYPE,
            'side',
            'low'
        );
    }
    //endRemoveIf(pro)

    /**
     * Search products
     *
     * @return void
     */
    public function ajaxWcSearchProducts() {
        $search    = sanitize_text_field( $_GET[ 'search' ] );
        $selection = sanitize_text_field( $_GET[ 'productsSelection' ] );
        $exclude   = array_map( 'intval', $_GET[ 'exclude' ] );

        if (
        in_array( $selection, [
            C::WC_PS_TYPE_INCLUDE_CATEGORIES,
            C::WC_PS_TYPE_EXCLUDE_CATEGORIES,
        ] )
        ) {
            $args = [
                "hide_empty" => false,
                "taxonomy"   => "product_cat",
                "name__like" => $search,
                'exclude'    => $exclude,
            ];

            $items   = get_terms( $args );
            $results = array_map( function ( $item ) {
                return [
                    "id"   => $item->term_id,
                    "text" => $item->name,
                ];
            }, $items );
        } else {
            $args    = [
                's'              => $search,
                'post_type'      => 'product',
                'post__not_in'   => $exclude,
                'posts_per_page' => -1,
            ];
            $items   = get_posts( $args );
            $results = array_map( function ( $item ) {
                return [
                    "id"   => $item->ID,
                    "text" => $item->post_title,
                ];
            }, $items );
        }
        exit( json_encode( [ "results" => $results, "pagination" => true ] ) );
    }

    public function pluginSettings() {
        register_setting( 'hurrytimer_settings', 'hurryt_settings' );
        add_settings_section(
            'hurryt_settings_general',
            __( 'General', 'hurrytimer' ),
            null,
            'hurrytimer_settings'
        );
        add_settings_field(
            'hurryt_disable_actions_edit_mode',
            'Disable actions in the admin area',
            function ( $args ) {
                $options = get_option( 'hurryt_settings' ); ?>
                <label for="">
                    <input type="checkbox"
                           name="hurryt_settings[disable_actions]" <?php checked(
                        isset( $options[ 'disable_actions' ] )
                        && $options[ 'disable_actions' ],
                        1
                    ); ?> id="hurryt-disable-actions" value="1"/>
                </label>
                <p class="description">
                    Don't run actions when editing or previewing a page in the admin area.
                </p>
                <?php
            },
            'hurrytimer_settings',
            'hurryt_settings_general'
        );
    }

    function ajaxAddWcConditionGroup() {
        check_ajax_referer( 'hurryt-admin', 'nonce' );

        ob_start();
        include HURRYT_DIR . 'admin/templates/wc-condition-group.php';
        echo ob_get_clean();
        wp_die();
    }

    function ajaxAddWcCondition() {
        check_ajax_referer( 'hurryt-admin', 'nonce' );

        ob_start();
        $groupId = sanitize_key( $_GET[ 'group_id' ] );
        include HURRYT_DIR . 'admin/templates/wc-condition.php';
        echo ob_get_clean();
        wp_die();
    }

    function ajaxLoadWcCondition() {
        check_ajax_referer( 'hurryt-admin', 'nonce' );
        ob_start();
        $groupId  = sanitize_key( $_GET[ 'group_id' ] );
        $selected = hurryt_wc_conditions()[ sanitize_key( $_GET[ 'condition_key' ] ) ];
        include HURRYT_DIR . 'admin/templates/wc-condition.php';
        echo ob_get_clean();
        wp_die();
    }
}
