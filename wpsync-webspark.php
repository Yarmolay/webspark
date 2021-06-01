<?php

/**
 * @package           wpsync-webspark
 *
 * @wordpress-plugin
 * Plugin Name:       Webspark Test Plugin
 * Description:       Create bot to retrieve products.
 * Author:            Yarmolenko Andrii
 * Version:           1.1.0
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 */

/**
* Current plugin version.
*/
define( 'WPWTP_VERSION', '1.1.0' );
define( 'WPWTP_PLUGIN_DIR', ABSPATH . 'wp-content/plugins/wpsync-webspark/' );

require_once WPWTP_PLUGIN_DIR . 'includes/class-webspark.php';
require_once WPWTP_PLUGIN_DIR . 'includes/class-webspark-activator.php';

$activator = new Webspark_Activator();
register_activation_hook( __FILE__, [ $activator, 'activate' ] );
register_deactivation_hook( __FILE__, [ $activator, 'deactivate' ] );

/**
 * Begins execution of the plugin.
 *
 * Since everything within the plugin is registered via hooks,
 * then kicking off the plugin from this point in the file does
 * not affect the page life cycle.
 *
 * @since    1.1.0
 */
function run_webspark() {

	if ( !session_id() ) {
		session_start();
	}

	$plugin = new Webspark();
	$plugin->run();
}

run_webspark();