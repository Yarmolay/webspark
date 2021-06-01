<?php

/**
 * The cron-facing functionality of the plugin.
 *
 * @since      1.1.0
 * @package    Webspark
 * @subpackage Webspark/cron
 */

class Webspark_Cron {
	/**
	 * The ID of this plugin.
	 *
	 * @since    1.1.0
	 * @access   private
	 * @var      string $plugin_name The ID of this plugin.
	 */
	private $plugin_name;

	/**
	 * The version of this plugin.
	 *
	 * @since    1.1.0
	 * @access   private
	 * @var      string $version The current version of this plugin.
	 */
	private $version;

	/**
	 * Initialize the class and set its properties.
	 *
	 * @param string $plugin_name The name of the plugin.
	 * @param string $version The version of this plugin.
	 *
	 * @since    1.1.0
	 */
	public function __construct( $plugin_name, $version ) {
		$this->plugin_name = $plugin_name;
		$this->version     = $version;
	}

	public function add_interval( $schedules ) {
		$schedules['sixty_minutes'] = [
            'interval' => get_option('api_cron_interval') * 60,
            'display'  => esc_html__( '1 minute' )
        ];

		return $schedules;
	}

}
