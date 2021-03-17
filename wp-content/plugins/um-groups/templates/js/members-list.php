<?php if ( ! defined( 'ABSPATH' ) ) exit; ?>


<script type="text/template" id="tmpl-um_groups_members">

	<# _.each( data.users, function( user, key ) { #>

		<div class="um-groups-user-wrap" data-group-uid="{{{user.id}}}" data-group-id="{{{data.group_id}}}">

			<div class="user-details">
				<div class="um-group-image-wrap">
					<a href="{{{user.profile_url}}}">
						{{{user.avatar}}}
					</a>
				</div>

				<div class="um-group-buttons">

					<?php if ( $list == 'members' || $list == 'requests' ) { ?>

						<# if ( Object.keys( user.menus ).length > 0 ) { #>
							<a href="javascript:void(0);" class="um-group-button um-group-button-more">
								<i class="um-faicon-ellipsis-h"></i>
							</a>
							<div class="um-groups-wrap-buttons">
								<ul class="um-group-buttons-more">
									<# _.each( user.menus, function( value, key ) { #>
										<li>
											<a href="javascript:void(0);" data-action-key="{{{key}}}">{{{value}}}</a>
										</li>
									<# }); #>
								</ul>
							</div>
						<# } #>

					<?php } elseif ( $list == 'blocked' ) { ?>

						<a href="javascript:void(0);" class="um-group-button" data-action-key="unblock">
							<?php _e( 'Unblock', 'um-groups' ) ?>
						</a>

					<?php } elseif ( $list == 'invites' ) { ?>

						<# if ( user.is_invited ) {#>
							<a href="javascript:void(0);" class="um-group-button" data-action-key="resend_invite">
								<span class="um-faicon-check"></span> <?php _e( 'Invited', 'um-groups' ) ?>
							</a>
						<# } else { #>
							<a href="javascript:void(0);" class="um-group-button" data-action-key="invite">
								<?php _e( 'Invite', 'um-groups' ) ?>
							</a>
						<# } #>

					<?php } ?>

				</div>

				<div class="um-group-texts">
					<div>
						<a href="{{{user.profile_url}}}">{{{user.display_name}}}</a>
					</div>
					<div>
						<ul>
							<?php if ( $list !== 'invites' ) { ?>
								<li>{{{user.date}}}</li>
							<?php } ?>
							{{{user.additional_content}}}
						</ul>
					</div>
				</div>
			</div>
		</div>

	<# }); #>

</script>