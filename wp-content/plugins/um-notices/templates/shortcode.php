<?php
/**
 * Template for the UM Notices shortcode
 * Called from the Notices_Shortcode->ultimatemember_notice() method
 *
 * This template can be overridden by copying it to yourtheme/ultimate-member/um-notices/shortcode.php
 */
if ( ! defined( 'ABSPATH' ) ) exit; ?>

<!-- um-notices/templates/shortcode.php -->
<div class="um-notices-shortcode">
	<?php UM()->Notices()->query()->show_notice( $args[ 'id' ] ); ?>
</div>