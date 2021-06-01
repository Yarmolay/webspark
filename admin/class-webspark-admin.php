<?php

/**
 * The admin-specific functionality of the plugin.
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the admin-specific stylesheet and JavaScript.
 *
 * @since      1.1.0
 * @package    Webspark
 * @subpackage Webspark/admin
 */
class Webspark_Admin {

	const DEFAULT_OPTIONS = [
		'product_quantity'     => 100,
		'api_url'              => 'https://my.api.mockaroo.com/products.json?key=89b23a40',
        'api_cron_interval'    => 60
	];

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
	 * @param string $plugin_name The name of this plugin.
	 * @param string $version The version of this plugin.
	 *
	 * @since    1.1.0
	 */
	public function __construct( $plugin_name, $version ) {
		$this->plugin_name = $plugin_name;
		$this->version     = $version;
	}

	/**
	 * Add plugin admin pages
	 *
	 * @since    1.1.0
	 */
	public function add_pages() {
		add_options_page( 'Content webspark settings', 'Wpsync Webspark', 'manage_options', 'webspark-plugin', [
			$this,
			'add_options_page'
		] );
	}

	/**
	 * Add plugin options page
	 *
	 * @since    1.1.0
	 */
	public function add_options_page() {
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_die( __( 'You do not have sufficient permissions to access this page.' ) );
		}

		require_once 'partials/webspark-admin-options.php';
	}

	/**
	 * Initialize webspark plugins settings
	 *
	 * @since    1.1.0
	 */
	public function settings_init() {
		add_settings_section( 'webspark-settings-section', 'Webspark settings', '', 'webspark-settings' );

		add_settings_field(
			'product_quantity',
			'Product import quantity',
			[ $this, 'product_quantity' ],
			'webspark-settings',
			'webspark-settings-section'
		);

		add_settings_field(
			'api_url',
			'API Url',
			[ $this, 'api_url' ],
			'webspark-settings',
			'webspark-settings-section'
		);

        add_settings_field(
            'api_cron_interval',
            'API Cron Interval, in minutes',
            [ $this, 'api_cron_interval' ],
            'webspark-settings',
            'webspark-settings-section'
        );
	}

	/**
	 * For all simple text fields
	 *
	 * @param $name Function name that is called
	 * @param $arguments Arguments for function
	 *
	 * @since    1.1.0
	 */
	public function __call( $name, $arguments ) {
		$value  = esc_attr( self::get_option( $name ) );
		$option = $name;
		require 'partials/webspark-text.php';
	}

	/**
	 * Get option wrapper to handle default values
	 *
	 * @param $name
	 *
	 * @since    1.1.0
	 *
	 * @return mixed
	 */
	public static function get_option( $name ) {
		return get_option( $name, self::DEFAULT_OPTIONS[ $name ] ?? '' );
	}
}
