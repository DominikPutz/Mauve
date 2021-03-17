<?php
if ( ! defined( 'ABSPATH' ) ) exit;


add_filter("um_activity_oembed__filter","um_activity_oembed__filter", 10 , 2 );
function um_activity_oembed__filter( $args, $url ){
	 
      

	return $args;
}