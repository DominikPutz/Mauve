<?php
/**
 * Template for the UM User Tags.
 * Used on the "Profile" page to display tags in the profile head and for the 'User Tags' field type
 *
 * Caller: method UM_User_Tags->get_tags()
 * Hook: 'um_profile_field_filter_hook__user_tags'
 *
 * This template can be overridden by copying it to yourtheme/ultimate-member/um-user-tags/tags.php
 */
if ( ! defined( 'ABSPATH' ) ) exit;

$i = 0;
$remaining = 0; ?>

<span class="um-user-tags">

	<?php foreach ( $tags as $tag ) {

		$term = UM()->User_Tags()->get_localized_term_by( $tag );
		if ( $term ) {
			$i++;

			$class = 'um-user-tag um-tag-' . $term->term_id;
			if ( $term->description ) {
				$class .= ' um-user-tag-desc';
			}
			if ( $limit > 0 && $i > $limit ) {
				$class .= ' um-user-hidden-tag';
				$remaining++;
			}

			$tagname = sprintf( __( '%s', 'um-user-tags' ), $term->name );
			$show_tag_link = apply_filters( 'um_user_tag__show_tag_link', true, 'get_tags' );

			if ( UM()->options()->get( 'members_page' ) && $show_tag_link ) {
				$link = get_term_link( $term, 'um_user_tag' );
				$link = add_query_arg( array( 'tag_field' => $metakey ), $link ); ?>

				<span class="<?php echo esc_attr( $class ); ?>" title="<?php echo esc_attr( $term->description ); ?>"><a href="<?php echo esc_url( $link ); ?>"><?php echo esc_html( $tagname ); ?></a></span>

			<?php } else { ?>

				<span class="<?php echo esc_attr( $class ); ?>" title="<?php echo esc_attr( $term->description ); ?>"><?php echo esc_html( $tagname ); ?></span>

			<?php }
		}
	}

	if ( $remaining > 0 ) { ?>

		<span class="um-user-tag um-user-tag-more"><a href="javascript:void(0);"><?php printf( __( '%s more', 'um-user-tags' ), $remaining ); ?></a></span>

	<?php } ?>

</span>
<div class="um-clear"></div>