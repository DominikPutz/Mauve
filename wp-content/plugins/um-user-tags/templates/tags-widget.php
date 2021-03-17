<?php
/**
 * Template for the UM User Tags.
 * Used on the "Members" page, after the search form
 *
 * Caller: method User_Tags_Shortcode->ultimatemember_tags()
 * Shortcode: 'ultimatemember_tags'
 *
 * This template can be overridden by copying it to yourtheme/ultimate-member/um-user-tags/tags-wdgt.php
 */
if ( ! defined( 'ABSPATH' ) ) exit; ?>


<div class="um-user-tags-wdgt">

	<?php foreach ( $terms as $term ) {

		$show_tag_link = apply_filters( 'um_user_tag__show_tag_link', true, 'widget' );
		if ( UM()->options()->get( 'members_page' ) && $show_tag_link ) {

			$link = get_term_link( $term, 'um_user_tag' );
			$link = add_query_arg( array( 'tag_field' => $metakey ), $link ); ?>

			<div class="um-user-tags-wdgt-item">
				<a href="<?php echo $link ?>" class="tag"><?php echo $term->name ?></a>
				<span class="count"><?php echo $term->count ?></span>
			</div>

		<?php } else { ?>

			<div class="um-user-tags-wdgt-item">
				<span class="tag"><?php echo $term->name ?></span>
				<span class="count"><?php echo $term->count ?></span>
			</div>

		<?php }
	} ?>

</div>