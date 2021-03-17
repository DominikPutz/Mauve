<?php namespace Hurrytimer; ?>

 <table class="form-table  hidden mode-settings" data-for="hurrytModeRegular">
<tr class="form-field" >
            <td><label><?php _e("End Date/Time", "hurrytimer") ?></label></td>
            <td>
                <label for="hurrytimer-end-datetime" class="date">
                    <input type="text" name="end_datetime" autocomplete="off"
                           id="hurrytimer-end-datetime"
                           class="hurrytimer-datepicker"
                           value="<?php echo $campaign->endDatetime ?>"
                    >
                </label>
            </td>
        </tr>
        
 </table>
