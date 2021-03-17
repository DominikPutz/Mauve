<?php
/**
 * Template for the UM MailChimp
 *
 * Page: "Account", tab "PNotifications"
 * Caller: function um_mc_account_tab()
 *
 * This template can be overridden by copying it to yourtheme/ultimate-member/um-mailchimp/account_email_newsletters.php
 */
if ( !defined( 'ABSPATH' ) ) {
	exit;
}

if ( empty( $notification_lists ) ) {
	return;
}
?>
<div class="um-field um-field-mailchimp" data-key="mailchimp">
	<div class="um-field-label">
		<label><?php _e( 'Email Newsletters', 'um-mailchimp' ); ?></label>
		<div class="um-clear"></div>
	</div>

	<?php
	foreach ( $notification_lists as $nl ) :
		$wp_list = $nl['wp_list'];
		if ( $nl['enabled'] ) {
			$active = 'active';
			$iclass = "um-icon-android-checkbox-outline";
		} else {
			$active = '';
			$iclass = "um-icon-android-checkbox-outline-blank";
		}
		?>

		<fieldset class="um-account-fieldset">
			<legend><?php echo empty( $wp_list->_um_desc ) ? $wp_list->post_title : $wp_list->_um_desc; ?></legend>
			<div class="um-field um-field-checkbox">
				<div class="um-field-area">
					<label class="um-field-checkbox <?php echo esc_attr( $active ); ?>">
						<input type="hidden" name="<?php echo esc_attr( $nl['name'] ); ?>[wp_list_id]" value="<?php echo esc_attr( $wp_list->ID ); ?>" />
						<input type="checkbox" name="<?php echo esc_attr( $nl['name'] ); ?>[enabled]" value="1" <?php echo checked( $nl['enabled'] ); ?>>
						<span class="um-field-checkbox-state"><i class="<?php echo esc_attr( $iclass ); ?>"></i></span>
						<span class="um-field-checkbox-option"><?php _e( 'Subscribe to the audience', 'um-mailchimp' ); ?></span>
					</label>
					<div class="um-clear"></div>
				</div>
			</div>

			<section class="um-account-fieldset-dropdown">

				<?php
				foreach ( $nl['groups'] as $group_id => $group_title ) :
					$group_name = $nl['name'] . '[groups][' . $group_id . '][]';
					$interests = UM()->Mailchimp()->api()->mc_get_interests_array( $nl['list_id'], $group_id );
					if ( !empty( $interests ) ) :
						?>

						<div class="um-field um-field-multiselect">
							<div class="um-field-label">
								<label><?php echo $group_title; ?></label>
								<div class="um-clear"></div>
							</div>
							<div class="um-field-area  ">
								<select class="um-form-field not-required um-s1" multiple="true" name="<?php echo esc_attr( $group_name ); ?>" style="width: 100%; display: block;">

									<?php foreach ( $interests as $id => $name ) : ?>
										<option value="<?php echo esc_attr( $id ); ?>" <?php echo selected( !empty( $nl['groups-selected'][$id] ) ); ?>><?php echo esc_html( $name ); ?></option>
										<?php
									endforeach;
									unset( $name );
									?>

								</select>
							</div>
						</div>

						<?php
					endif;
				endforeach;
				?>

				<?php if ( !empty( $nl['tags'] ) ) : ?>

					<div class="um-field um-field-multiselect">
						<div class="um-field-label">
							<label><?php _e( 'Tags', 'um-mailchimp' ); ?></label>
							<div class="um-clear"></div>
						</div>
						<div class="um-field-area">
							<input type="hidden" name="<?php echo esc_attr( $nl['name'] ); ?>[tags-update]" value="1" />
							<select class="um-form-field not-required um-s1" multiple="true" name="<?php echo esc_attr( $nl['name'] ); ?>[tags][]" style="width: 100%; display: block;">

								<?php foreach ( $nl['tags'] as $id => $name ) : ?>
									<option value="<?php echo esc_attr( $id ); ?>" <?php echo selected( !empty( $nl['tags-selected'][$id] ) ); ?>><?php echo esc_html( $name ); ?></option>
									<?php
								endforeach;
								unset( $name );
								?>

							</select>
						</div>
					</div>

				<?php endif; ?>

				<?php if ( UM()->options()->get( 'mailchimp_allow_add_tags' ) ) : ?>

					<div class="um-field um-is-conditional um-field-textarea um-field-type_textarea">
						<div class="um-field-label">
							<label><?php _e( 'Add New Tags', 'um-mailchimp' ); ?></label>
							<div class="um-clear"></div>
						</div>
						<div class="um-field-area">
							<textarea class="um-form-field not-required" name="<?php echo esc_attr( $nl['name'] ); ?>[tags-new]" placeholder="<?php esc_attr_e( 'Comma separated list of tags', 'um-mailchimp' ); ?>" style="height: 4em;"></textarea>
						</div>
					</div>

				<?php endif; ?>

			</section>
		</fieldset>

	<?php endforeach; ?>

</div>

<style type="text/css">
	fieldset.um-account-fieldset {
		padding-top: 0px;
	}
	.um-field-mailchimp .select2-container--default .select2-search--inline .select2-search__field {
		border: none !important;
	}
</style>

<script type="text/javascript">
	jQuery(function () {
		jQuery('.um-account-fieldset > .um-field-checkbox input[type="checkbox"]').on('change', function (e) {
			var $section = jQuery(e.target).closest('.um-account-fieldset').find('section.um-account-fieldset-dropdown');
			if (e.target.checked) {
				$section.show(200);
			} else {
				$section.hide(200);
			}
		}).trigger('change');
	});
</script>