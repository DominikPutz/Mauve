<?php if ( ! defined( 'ABSPATH' ) ) exit;

$class = $is_online ? 'online' : 'offline';
$title = $is_online ? __( 'online', 'um-online' ) : __( 'offline', 'um-online' ); ?>

<span class="um-online-status <?php echo esc_attr( $class ) ?> um-tip-n" title="<?php echo esc_attr( $title ) ?>">
	<i class="um-faicon-circle"></i>
</span>
