<?php namespace Hurrytimer;
/**
 * The template for display the recurring campaigns settings
 *
 * @package hurrytimer/admin/templates
 */
?>
<div class="hidden mode-settings" data-for="hurrytModeRecurring"
     <?php //removeIf(pro) ?>
     data-hurryt-pro="wrap"
     <?php //endRemoveIf(pro) ?>

>
    <?php //removeIf(pro) ?>
    <div class="hurryt-upgrade-alert hurryt-upgrade-alert-inline" data-hurryt-pro="notice">
        <div class="hurryt-upgrade-alert-header">
            <span class="dashicons dashicons-lock"></span>
            <h3>Recurring Campaigns is a PRO feature</h3></div>
        <div class="hurryt-upgrade-alert-body">Unlock to create unlimited and customizable recurring campaigns.
        </div>
        <div class="hurryt-upgrade-alert-footer">
            <a class="hurryt-button button" href="https://hurrytimer.com/pricing?utm_source=plugin&utm_medium=recurring_mode&utm_campaign=upgrade">Upgrade now</a>
            <a href="https://hurrytimer.com?utm_source=plugin&utm_medium=recurring_mode&utm_campaign=learn_more" class="button">Learn more</a>
        </div>
    </div>
    <?php //endRemoveIf(pro) ?>
    <table class="form-table"
    <?php //removeIf(pro) ?>
           data-hurryt-pro="feature"
        <?php //endRemoveIf(pro) ?>
    >
        <tr class="form-field">
            <td><label><?php _e('Recur Every', "hurrytimer") ?></label></td>
            <td>
                <div class="hurryt-flex">
                    <div class="hurryt-w-16">
                        <input type="number"
                               min="1"
                               name="recurring_interval"
                               value="<?php echo $campaign->recurringInterval ?>"
                               id="hurrytRecurringInterval"/>
                    </div>
                    <div class="hurryt-flex-grow">
                        <select name="recurring_frequency" id="hurrytRecurringFrequency"
                                class="hurryt-w-full">
                            <option value="<?php echo C::RECURRING_MINUTELY ?>"
                                <?php echo selected($campaign->recurringFrequency,C::RECURRING_MINUTELY) ?>>
                                Minute(s)
                            </option>
                            <option value="<?php echo C::RECURRING_HOURLY ?>"
                                <?php echo selected($campaign->recurringFrequency,C::RECURRING_HOURLY) ?>>
                                Hour(s)
                            </option>
                            <option value="<?php echo C::RECURRING_DAILY ?>"
                                <?php echo selected($campaign->recurringFrequency,C::RECURRING_DAILY) ?>>
                                Day(s)
                            </option>
                            <option value="<?php echo C::RECURRING_WEEKLY ?>"
                                <?php echo selected($campaign->recurringFrequency,C::RECURRING_WEEKLY) ?>>
                                Week(s)
                            </option>
                        </select>
                    </div>
                </div>
            </td>
        </tr>

        <tr class="form-field">
            <td><label><?php _e('Duration', "hurrytimer") ?></label></td>
            <td>
                <div class="hurryt-flex">
                    <label class="hurryt-uppercase hurryt-text-gray-700 hurryt-text-xs   hurryt-pr-2">
                        <?php _e("Days", "hurrytimer") ?>
                        <input type="number"
                               class="hurrytimer-duration"
                               name="recurring_duration[]"
                               id="hurrytRecurringDays"
                               class="hurryt-w-full"
                               min="0"
                               value="<?php echo $campaign->recurringDuration[0] ?>"
                        >
                    </label>
                    <label class="hurryt-uppercase hurryt-text-gray-700 hurryt-text-xs hurryt-pr-2">
                        <?php _e("Hours", "hurrytimer") ?>
                        <input type="number"
                               name="recurring_duration[]"
                               id="hurrytRecurringHours"
                               class="hurryt-w-full"
                               min="0"
                               value="<?php echo $campaign->recurringDuration[1] ?>"
                        >
                    </label>
                    <label class="hurryt-uppercase hurryt-text-gray-700 hurryt-text-xs  hurryt-pr-2">
                        <?php _e("minutes", "hurrytimer") ?>
                        <input type="number"
                               id="hurrytRecurringMinutes"
                               name="recurring_duration[]"
                               class="hurryt-w-full"
                               min="0"
                               value="<?php echo $campaign->recurringDuration[2] ?>"
                        >
                    </label>
                    <label class="hurryt-uppercase hurryt-text-gray-700 hurryt-text-xs">
                        <?php _e("seconds", "hurrytimer") ?>
                        <input type="number"
                               id="hurrytRecurringSeconds"
                               name="recurring_duration[]"
                               class="hurryt-w-full"
                               min="0"
                               value="<?php echo $campaign->recurringDuration[3] ?>"
                        >
                    </label>
                </div>
                <p class="description hidden">
                    <?php _e('Max duration:', 'hurrytimer') ?> <span id="hurrytRecurringDurationMax"></span>
                </p>
            </td>
        </tr>
        <tr class="form-field" id="hurrytRecurringDays">
            <td><label><?php _e('Recur On', "hurrytimer") ?></label></td>
            <td>
                <div class="hurrytimer-field hurryt-flex hurryt-flex-wrap">
                    <?php $locale = new \WP_Locale(); ?>
                    <?php for ($i = 0; $i < 7; $i++): ?>
                        <label for="" class="hurryt-block hurryt-mb-3 hurryt-w-1/3"><input
                                    type="checkbox" <?php echo in_array($i, $campaign->recurringDays)
                                ? 'checked' : '' ?> name="recurring_days[]"
                                    value="<?php echo $i ?>"><?php echo $locale->get_weekday($i) ?>
                        </label>
                    <?php endfor; ?>
                </div>
            </td>
        </tr>
        <!-- Start time { -->
        <tr class="form-field">
            <td>
                <label><?php _e('Start Date/Time', "hurrytimer") ?></label>
            </td>
            <td>
                <label for="hurrytimer-end-datetime" class="date hurryt-w-full">
                    <input type="text" name="recurring_start_time" autocomplete="off"
                           class="hurrytimer-datepicker hurryt-w-full"
                           placeholder="Select Date/Time"
                           value="<?php echo $campaign->recurringStartTime ?>"
                    >
                </label>
            </td>
        </tr>

        <tr class="form-field">
            <td><label for="active"><?php _e("End", "hurrytimer") ?></label></td>
            <td>
                <div class="hurryt-flex hurryt-flex-col">
                    <label for="" class="hurryt-mr-2 hurryt-mb-2"><input type="radio"
                                                                         name="recurring_end"
                                                                         value="<?php echo C::RECURRING_END_NEVER ?>" <?php echo checked($campaign->recurringEnd,
                            C::RECURRING_END_NEVER) ?> >Never</label>
                    <label for="" class="hurryt-mb-2"><input type="radio" name="recurring_end"
                                                             value="<?php echo C::RECURRING_END_OCCURRENCES ?>" <?php echo checked($campaign->recurringEnd,
                            C::RECURRING_END_OCCURRENCES) ?>>After <input type="text"
                                                                          name="recurring_count"
                                                                          autocomplete="off"
                                                                          id="hurrytimer-recurring_end_date"
                                                                          style="width: 3em"
                                                                          value="<?php echo $campaign->recurringCount ?>"
                        > recurrences</label>
                    <label for="" class="hurryt-mb-2 date"><input type="radio" name="recurring_end"
                                                                  value="<?php echo C::RECURRING_END_TIME ?>" <?php echo checked($campaign->recurringEnd,
                            C::RECURRING_END_TIME) ?>>On <input type="text"
                                                                name="recurring_until"
                                                                autocomplete="off"
                                                                class="hurrytimer-datepicker"
                                                                placeholder="Select Date/Time"
                                                                style="width: 12em"
                                                                value="<?php echo $campaign->recurringUntil ?>"
                        ></label>
                </div>
            </td>
        </tr>
    </table>
</div>
