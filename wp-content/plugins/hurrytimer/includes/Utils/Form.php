<?php

namespace Hurrytimer\Utils;

class Form
{

    static function toggle($name, $value, $id)
    {
        ?>
        <input type="checkbox"
               name="<?php echo $name ?>"
               id="<?php echo $id ?>"
               class="js-hurrytimer-input-toggle"
               value="yes"
            <?php checked($value, 'yes') ?>
        />
        <?php
    }

    /**
     * @param $name
     * @param $value
     *
     * @return string
     */
    static function colorInput($name, $value)
    {
        ?>
        <span class="hurrytimer-color-preview"></span>
        <input
                type="text"
                name="<?php echo $name ?>"
                placeholder="Select color"
                autocomplete="off"
                class="hurrytimer-color-input"
                value="<?php echo $value ?>" />
       <span class="dashicons dashicons-no-alt hurrytimer-color-clear"></span>
        <?php
    }
}