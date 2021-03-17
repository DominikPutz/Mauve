<?php
namespace Hurrytimer;

/**
 * The public-facing functionality of the plugin.
 *
 * @link       http://nabillemsieh.com
 * @since      1.0.0
 *
 * @package    Hurrytimer
 * @subpackage Hurrytimer/public
 */
class Frontend {
    /**
     * The ID of this plugin.
     *
     * @since    1.0.0
     * @access   private
     * @var      string $plugin_name The ID of this plugin.
     */
    private $plugin_name;

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
     * @param string $plugin_name The name of the plugin.
     * @param string $version     The version of this plugin.
     *
     * @since    1.0.0
     *
     */
    public function __construct( $plugin_name, $version ) {
        $this->plugin_name = $plugin_name;
        $this->version     = $version;
    }

    function init() {

        // Enqueue CSS and JS.
        add_action( 'wp_enqueue_scripts', [ $this, 'enqueue_styles' ] );
        add_action( 'wp_enqueue_scripts', [ $this, 'enqueue_scripts' ] );

        // Display campaign in sticky bar.
        add_action( 'wp_footer', [ $this, 'render_sticky_bar' ] );

        // Display campaign on product page.
        add_action( 'wp', [ $this, 'render_on_product_page' ] );

        // Change stock status for recurring/onetime campaign via Ajax.
        add_action( 'wp_ajax_change_stock_status', [ $this, 'ajax_change_stock_status' ] );
        add_action( 'wp_ajax_nopriv_change_stock_status', [ $this, 'ajax_change_stock_status' ] );

        // Log evergreen campaign start time for each visitor.
        add_action( 'wp_ajax_set_timestamp', [ $this, 'ajax_save_evergreen_start_time' ] );
        add_action( 'wp_ajax_nopriv_set_timestamp', [ $this, 'ajax_save_evergreen_start_time' ] );

        // Return next recurrence.
        add_action( 'wp_ajax_next_recurrence', [ $this, 'ajax_next_recurrence' ] );
        add_action( 'wp_ajax_nopriv_next_recurrence', [ $this, 'ajax_next_recurrence' ] );

        // Check before rendering campaign.
        add_action( 'hurryt_pre_render', [ $this, 'pre_render_shortcode' ] );

        // Check actions for expired recurring and onetime campaigns
        // This only concerns shortcodes inserted in post editor.
        // Fallback to shortcode callback
        add_action( 'wp', [ $this, 'check_post_shortcode' ] );
        
    }


    /**
     * Check if there is shortcode in the current post.
     */
    function check_post_shortcode() {
        global $post;
        if ( is_singular() && is_a( $post, 'WP_Post' )
             && has_shortcode( $post->post_content, 'hurrytimer' ) && !(defined( 'DOING_AJAX' ) && DOING_AJAX)
        ) {
            $campaigns_ids = hurryt_parse_campaigns( $post->post_content );
            foreach ( $campaigns_ids as $id ) {
                do_action( 'hurryt_pre_render', hurryt_get_campaign( $id ) );
            }
        }
    }

    /**
     * @param $campaign Campaign
     */
    function pre_render_shortcode( $campaign ) {
        if ( $campaign->is_running() && $campaign->is_expired() ) {
            ( new ActionManager( $campaign ) )->run();
        }
    }


    public function ajax_next_recurrence() {
        check_ajax_referer( 'hurryt', 'nonce' );
        $campaign_id = absint( $_GET[ 'id' ] );
        if ( ! get_post( $campaign_id )
             || get_post_type( $campaign_id ) !== HURRYT_POST_TYPE
        ) {
            die( -1 );
        }

        $campaign = new Campaign( $campaign_id );
        $endDate  = $campaign->get_current_recurrence_end_date();
        wp_send_json_success( [
            'endTimestamp' => $endDate ? $endDate->getBrowserTimestamp() : null,
        ] );
    }

    /**
     * Uses ajax to save start time for the given visitor.
     * This used to bypass cookie cache.
     */
    public function ajax_save_evergreen_start_time() {
        check_ajax_referer( 'hurryt', 'nonce' );
        if ( ! isset( $_POST[ 'timestamp' ] ) || ! isset( $_POST[ 'cid' ] ) ) {
            wp_die();
        }
        $ip_detection       = new IPDetection();
        $cookie_detection   = new CookieDetection();
        $evergreen_campaign = new EvergreenCampaign( intval( $_POST[ 'cid' ] ), $cookie_detection,
            $ip_detection );
        $evergreen_campaign->setEndDate( filter_input( INPUT_POST, 'timestamp' ) );
        wp_die();
    }

    public function render_sticky_bar() {
        $campaigns = get_posts( [
            'post_type'        => HURRYT_POST_TYPE,
            'numberposts'      => -1,
            'post_status'      => 'publish',
            'meta_key'         => 'enable_sticky',
            'meta_value'       => C::YES,
            'suppress_filters' => false,
        ] );
        foreach ( $campaigns as $post ) {
            echo do_shortcode( '[hurrytimer id="' . $post->ID . '"]' );
        }

    }

    /**
     * Apply change stock status
     */
    public function ajax_change_stock_status() {
        check_ajax_referer( 'hurryt', 'nonce' );
        if ( ! isset( $_POST[ 'campaign_id' ], $_POST[ 'status' ] ) ) {
            die( -1 );
        }
        $id          = intval( $_POST[ 'campaign_id' ] );
        $status      = sanitize_key( $_POST[ 'status' ] );
        $wc_campaign = new WCCampaign();
        $campaign    = new Campaign( $id );
        $wc_campaign->change_stock_status( $campaign, $status );
        die();
    }

    /**
     * Maybe display campaign on current product page.
     */
    public function render_on_product_page() {
        global $post;
        if ( hurryt_is_woocommerce_activated() && is_product() ) {
            $wc_campaign = new WCCampaign();
            $wc_campaign->run( $post->ID );
        }

    }

    /**
     * Register the stylesheets for the public-facing side of the site.
     *
     * @since    1.0.0
     */
    public function enqueue_styles() {
        $buildVersion = false;

        $filePath = HURRYT_DIR . 'assets/css/hurrytimer.css';
        if ( file_exists( $filePath ) ) {
            $buildVersion = filemtime( $filePath );
        }

        if ( ! $buildVersion ) {
            $buildVersion = time();
        }

        wp_enqueue_style(
            $this->plugin_name,
            HURRYT_URL . 'assets/css/hurrytimer.css', [], $buildVersion
        );

    }

    /**
     * Register the JavaScript for the public-facing side of the site.
     *
     * @since    1.0.0
     */
    public function enqueue_scripts() {

        wp_enqueue_script(
            'hurryt-cookie',
            HURRYT_URL . 'assets/js/cookie.min.js',
            [],
            '2.2.0',
            true
        );
        wp_enqueue_script(
            'hurryt-countdown',
            HURRYT_URL . 'assets/js/jquery.countdown.min.js',
            [ 'jquery' ],
            '2.2.0',
            true
        );
        wp_enqueue_script(
            $this->plugin_name,
            HURRYT_URL . 'assets/js/hurrytimer.js',
            [ 'hurryt-countdown', 'hurryt-cookie' ],
            defined( 'WP_DEBUG' ) && WP_DEBUG ? time() : $this->version,
            true
        );
        wp_localize_script( $this->plugin_name, 'hurrytimer_ajax_object', [
            'tz'                      => \hurryt_tz(),
            'ajax_url'                => admin_url( 'admin-ajax.php' ),
            'ajax_nonce'              => wp_create_nonce( 'hurryt' ),
            'disable_actions'         => hurryt_is_admin_area()
                                         && hurryt_settings()[ 'disable_actions' ],
            'sticky_bar_hide_timeout' => apply_filters( 'hurryt_sticky_bar_hide_timeout',
                7 ),
            'actionsOptions'          => [
                'none'                => C::ACTION_NONE,
                'hide'                => C::ACTION_HIDE,
                'redirect'            => C::ACTION_REDIRECT,
                'stockStatus'         => C::ACTION_CHANGE_STOCK_STATUS,
                'hideAddToCartButton' => C::ACTION_HIDE_ADD_TO_CART_BUTTON,
                'displayMessage'      => C::ACTION_DISPLAY_MESSAGE,
            ],
            'restartOptions'          => [
                'none'        => C::RESTART_NONE,
                'immediately' => C::RESTART_IMMEDIATELY,
                'afterReload' => C::RESTART_AFTER_RELOAD,
            ],
            'redirect_no_back'        => apply_filters( 'hurryt_redirect_no_back', true ),
        ] );
    }
}
