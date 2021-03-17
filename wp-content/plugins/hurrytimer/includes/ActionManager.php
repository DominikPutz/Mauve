<?php

namespace Hurrytimer;


/**
 *
 * This class handle actions executions
 *
 * Class ActionManager
 *
 * @package Hurrytimer
 */
class ActionManager
{
    /**
     * @var Campaign
     */
    protected $campaign;

    public function __construct( $campaign )
    {
        $this->campaign = $campaign;
        add_filter( 'wp_insert_post_data', function ( $data ) {
            global $hurryt_saving_post;
            $hurryt_saving_post = true;

            return $data;
        } );
        add_action( 'save_post', function () {
            global $hurryt_saving_post;
            $hurryt_saving_post = false;
        } );

    }

    function is_enabled()
    {
        return !( hurryt_is_admin_area() && hurryt_settings()[ 'disable_actions' ] );
    }


    public function run()
    {

        if ( !$this->is_enabled() ) {
            return;
        }

        do_action( "hurryt_{$this->campaign->get_id()}_campaign_ended", $this->campaign );

        foreach ( $this->campaign->actions as $action ) {
            switch ( $action[ 'id' ] ) {
                case C::ACTION_HIDE:
                    $this->hide_campaign();
                    break;
                case C::ACTION_REDIRECT;
                    $this->redirect_to( $action[ 'redirectUrl' ] );
                    break;
                case C::ACTION_CHANGE_STOCK_STATUS:
                    $this->change_stock_status( $action[ 'wcStockStatus' ] );
                    break;
                case C::ACTION_DISPLAY_MESSAGE:
                    $this->display_message( $action[ 'message' ] );
                    break;
            }
        }
    }

    function hide_campaign()
    {
        add_filter( 'hurryt_campaign_template', function ( $template, $campaign_id ) {
            if ( $campaign_id === $this->campaign->get_id() ) {
                return '';
            }
            return $template;
        }, 10, 2 );
    }

    function redirect_to( $url )
    {
        global $hurryt_saving_post;
        if ( $hurryt_saving_post ) {
            return;
        }
        wp_redirect( $url );

        return;
    }

    function change_stock_status( $stock_status )
    {
        $wc_campaign = new WCCampaign();
        $wc_campaign->change_stock_status( $this->campaign, $stock_status );

        return;
    }

    function display_message( $message )
    {
        add_filter( 'hurryt_campaign_template',
            function ( $template, $campaign_id ) use ( $message ) {
                if ( $campaign_id === $this->campaign->get_id() ) {
                    return "<div class='hurrytimer-campaign-message'>{$message}</div>";
                }
                return $template;
            }, 10, 2 );

        return;
    }
}
