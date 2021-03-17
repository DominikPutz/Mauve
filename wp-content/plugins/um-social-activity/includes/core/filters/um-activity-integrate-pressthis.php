<?php
if ( ! defined( 'ABSPATH' ) ) exit;


 /**
  * Publis PressThis activity
  * @param  string $redirect    
  * @param  integer $post_id  
  * @param  string $post_status 
  * @hook_filter 'press_this_save_redirect'
  */
  add_filter("press_this_save_redirect","um_pressthis_publish",3,10);
 function um_pressthis_publish( $redirect, $post_id, $post_status ){

 		if( $post_status != 'publish' )
 			return;
		if ( ! UM()->options()->get('activity-new-post') )
			return;

		$post = get_post( $post_id );
		$user_id = $post->post_author;

		um_fetch_user( $user_id );
		$author_name = um_user('display_name');
		$author_profile = um_user_profile_url();

		if( ! empty(  $post->post_content ) ){
			$html = new DOMDocument();
			@$html->loadHTML(mb_convert_encoding( $post->post_content, 'HTML-ENTITIES', 'UTF-8'));
			$tags = null;

			$title = $html->getElementsByTagName('title');
			$tags['title'] = $title->item(0)->nodeValue;
			$stop = false;
			foreach( $html->getElementsByTagName('img') as $img ) {
					if ( $stop == true ) continue;
					$src = trim( str_replace('\\','/', $img->getAttribute('src') ) );
					$data = UM()->Activity_API()->api()->is_image( $src );
					if (  is_array( $data ) ) {
						$tags['image'] = $src;
						$tags['image_width'] = $data[0];
						$tags['image_height'] = $data[1];
						$stop = true;
					}
			}
			
		}

		if( isset( $tags['image'] ) && ! empty(  $tags['image']  ) ){
			$post_image = '<span class="post-image"><img src="'. $tags['image']. '" alt="" title="" class="um-activity-featured-img" /></span>';
		} else {
			$post_image = '';
		}



		if ( $post->post_content ) {
			$post_excerpt = '<span class="post-excerpt">' . wp_trim_words( strip_shortcodes( $post->post_content ), $num_words = 25, $more = null ) . '</span>';
		} else {
			$post_excerpt = '';
		}

        UM()->Activity_API()->api()->save(
			array(
				'template' => 'new-post',
				'wall_id' => $user_id,
				'related_id' => $post_id,
				'author' => $user_id,
				'author_name' => $author_name,
				'author_profile' => $author_profile,
				'post_title' => '<span class="post-title">' . $post->post_title . '</span>',
				'post_url' => get_permalink( $post_id ),
				'post_excerpt' => $post_excerpt,
				'post_image' => $post_image,
			)
		);

		return $redirect;
 }

