<?php
// removeIf(pro)

/**
 * The plugin bootstrap file
 *
 * @link              http://hurrytimer.com
 * @since             1.0.0
 * @package           Hurrytimer
 *
 * @wordpress-plugin
 * Plugin Name:       HurryTimer
 * Plugin URI:        https://hurrytimer.com
 * Description:       A Scarcity and Urgency Countdown Timer for WordPress & WooCommerce with recurring and evergreen mode.
 * Version:           2.2.27
 * Author:            Nabil Lemsieh 
 * Author URI:        https://hurrytimer.com
 * License:           GPLv3
 * License URI:       http://www.gnu.org/licenses/gpl.html
 * Text Domain:       hurrytimer
 * Domain Path:       /languages
 */
// endRemoveIf(pro)

namespace Hurrytimer;

// If this file is called directly, abort.

if ( !defined( 'WPINC' ) ) {
    die;
}

define( 'HURRYT_VERSION', '2.2.27' );
define( 'HURRYT_DB_VERSION', '1.0.0' );
define( 'HURRYT_SLUG', 'hurrytimer' );
define( 'HURRYT_DIR', plugin_dir_path( __FILE__ ) );
define( 'HURRYT_URL', plugin_dir_url( __FILE__ ) );
define( 'HURRYT_BASENAME', plugin_basename( __FILE__ ) );
define( 'HURRYT_POST_TYPE', 'hurrytimer_countdown' );
define( 'HURRYT_OPTION_VERSION_KEY', 'hurrytimer_version' );


require plugin_dir_path( __FILE__ ) . 'includes/Bootstrap.php';

function activate()
{
    require_once HURRYT_DIR . 'includes/Activator.php';
    Activator::activate();
}

function deactivate()
{
    require_once HURRYT_DIR . 'includes/Deactivator.php';
    Deactivator::deactivate();
}

register_activation_hook( __FILE__, 'Hurrytimer\activate' );
register_deactivation_hook( __FILE__, 'Hurrytimer\deactivate' );

add_action('plugins_loaded', function(){
    ( new Bootstrap() )->run();
    do_action( 'hurrytimer_init' );
});

