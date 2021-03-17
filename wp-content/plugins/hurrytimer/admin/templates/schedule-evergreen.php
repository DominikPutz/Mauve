<?php namespace Hurrytimer;
global $post_id;
?>
    <table class="form-table  hidden mode-settings" data-for="hurrytModeEvergreen">
  
        <tr class="form-field" >
            <td>
                <label><?php _e('Ends After', "hurrytimer") ?></label>
            </td>
            <td>
                <div class="hurrytimer-field-duration">

                    <label>
                        <?php _e("Days", "hurrytimer") ?>

                        <input type="number"
                               class="hurrytimer-duration"
                               name="duration[]"
                               min="0"
                               data-index="0"
                               value="<?php echo $campaign->duration[0] ?>">
                    </label>
                    <label>
                        <?php _e("Hours", "hurrytimer") ?>
                        <input type="number"
                               class="hurrytimer-duration"
                               name="duration[]"
                               min="0"
                               data-index="1"
                               value="<?php echo $campaign->duration[1] ?>"
                        >

                    </label>
                    <label>
                        <?php _e("Minutes", "hurrytimer") ?>
                        <input type="number"
                               class="hurrytimer-duration"
                               name="duration[]"
                               min="0"
                               data-index="2"
                               value="<?php echo $campaign->duration[2] ?>"
                        >

                    </label>
                    <label>
                        <?php _e("seconds", "hurrytimer") ?>
                        <input type="number"
                               class="hurrytimer-duration"
                               name="duration[]"
                               data-index="3"
                               value="<?php echo $campaign->duration[3] ?>"
                        >

                    </label>
                </div>
            </td>
        </tr>
        <tr class="form-field" >
            <td><label for="active"><?php _e("Restart when expired", "hurrytimer") ?></label></td>
            <td>
                <select name="restart" id="js-hurrytimer-restart-coundown"
                        class="hurryt-w-full">
                    <option value="<?php echo C::RESTART_NONE ?>" <?php echo selected($campaign->restart,
                        C::RESTART_NONE) ?>>
                        <?php _e("None", "hurrytimer") ?>
                    </option>
                    <option value="<?php echo C::RESTART_IMMEDIATELY ?>" <?php echo selected($campaign->restart,
                        C::RESTART_IMMEDIATELY) ?>>
                        <?php _e("Restart immediately", "hurrytimer") ?>
                    </option>
                    <option value="<?php echo C::RESTART_AFTER_RELOAD ?>" <?php echo selected($campaign->restart,
                        C::RESTART_AFTER_RELOAD) ?>>
                        <?php _e("Restart at the next visit", "hurrytimer") ?>
                    </option>
                </select>
            </td>
        </tr>
        <?php if($post_id !== null): ?>
            <tr class="form-field">
                <td><label for="active"><?php _e("Reset Countdown", "hurrytimer") ?>
                    </label></td>
                <td>
                    <div>
                        <button type="button"  data-id="<?php echo $post_id ?>" data-cookie="<?php echo CookieDetection::cookieName($post_id) ?>" data-url="<?php echo $resetCampaignCurrentAdminUrl ?>" class="button button-default" id="hurrytResetCurrent">Only for me</button>
                        &nbsp;
                        <button type="button"  data-url="<?php echo $resetCampaignAllVisitorsUrl ?>" class="button button-default" id="hurrytResetAll">For all visitors...</button>
                    </div>
                </td>
            </tr>
        <?php endif; ?>
        </table>