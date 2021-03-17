<?php

namespace Hurrytimer;

class CampaignShortcode
{

    /**
     * Init shortcode
     */
    function init()
    {
        add_shortcode( 'hurrytimer', [ $this, 'content' ] );
        add_filter( 'pre_do_shortcode_tag', [ $this, 'maybe_display' ], 10, 3 );
    }

    /**
     *
     * @param $return
     * @param $tag
     * @param $attr
     *
     * @return mixed|string|void
     */
    function maybe_display( $return, $tag, $attr )
    {

        if ( $tag !== 'hurrytimer' ) {
            return $return;
        }
        $id = wp_parse_args( $attr, [ 'id' => -1 ] )[ 'id' ];

        // Nothing to display if the campaign doesn't exist.
        if ( !get_post( $id ) ) {
            return __( 'HurryTimer: Invalid campaign ID.', 'hurrytimer' );
        }
        $campaign = hurryt_get_campaign( $id );

        if ( !$campaign->is_running() ) {

            // Show something else.
            return apply_filters( 'hurryt_no_campaign', '', $campaign->get_id() );
        }

        // Let developer decide whether to show the campaign or not.
        if ( !apply_filters( 'hurryt_show_campaign', true, $campaign->get_id() ) ) {
            return '';
        }

        return $return;
    }

    /**
     * Return shortcode content.
     *
     * @param $attrs
     *
     * @return string
     */
    public function content( $attrs )
    {

        $campaign = hurryt_get_campaign( absint( $attrs[ 'id' ] ) );
        
        if ( $campaign->is_expired() ) {
           (new ActionManager( $campaign ))->run();
        }

        $template = apply_filters( 'hurryt_campaign_template', $campaign->build_template(), $campaign->get_id() );

        return !empty( $template ) ? $campaign->wrap_template( $template ) : '';
    }


}
