<?php

/**
 * The file that defines the activate/deactivate plugin actions
 *
 * @since      1.1.0
 * @package    Webspark
 * @subpackage Webspark/includes
 */
class Webspark_Activator {

	/**
	 * Activate plugin
	 *
	 * @since      1.1.0
	 */
	public function activate() {
		$time = current_time( 'timestamp' );

        if ( class_exists( 'WooCommerce' ) ) {

            if ( ! wp_next_scheduled( 'wp_sixty_minutes' ) ) {
                wp_schedule_event( $time - $time % get_option('api_cron_interval') * 60, 'sixty_minutes', 'wp_sixty_minutes' );
            }

        } else {
            wp_die( __( 'You need install WooCommerce plugin in this themes.' ) );
        }
	}

	public function deactivate() {
		while ( false !== wp_unschedule_event( wp_next_scheduled( 'wp_sixty_minutes' ), 'wp_sixty_minutes' ) ) {
		}
	}

}

