<?php
/*
 * Campaign settings metabox.
 */
?>

<ul class="hurrytimer-tabbar">
    <li class="active">
        <a href="#hurrytimer-tabcontent-general"><?php _e('Schedule', 'hurrytimer') ?></a>
    </li>
    <li>
        <a href="#hurrytimer-tabcontent-woocommerce"><?php _e('WooCommerce', 'hurrytimer') ?></a>
    </li>
    <li>
        <a href="#hurrytimer-actions"><?php _e('Actions', 'hurrytimer') ?></a></li>
    <li><a href="#hurrytimer-tabcontent-styling"><?php _e('Appearance', 'hurrytimer') ?></a></li>
</ul>

<div class="hurrytimer-tabs">
    <?php include "schedule-tab.php"; ?>
    <?php include "woocommerce-tab.php"; ?>
    <?php include "actions-tab.php"; ?>
    <?php include "appearance-tab.php"; ?>
</div>
