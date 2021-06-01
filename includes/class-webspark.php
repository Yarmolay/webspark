<?php

/**
 * The file that defines the core plugin class
 *
 * This is used to define internationalization, admin-specific hooks, and
 * cron-facing site hook Webspark_Loaders.
 *
 * Also maintains the unique identifier of this plugin as well as the current
 * version of the plugin.
 *
 * @since      1.1.0
 * @package    Webspark
 * @subpackage Webspark/includes
 */
class Webspark {
	/**
	 * The loader that's responsible for maintaining and registering all hooks that power
	 * the plugin.
	 *
	 * @since    1.1.0
	 * @access   protected
	 * @var      Webspark_Loader $loader Maintains and registers all hooks for the plugin.
	 */
	protected $loader;

	/**
	 * The unique identifier of this plugin.
	 *
	 * @since    1.1.0
	 * @access   protected
	 * @var      string $plugin_name The string used to uniquely identify this plugin.
	 */
	protected $plugin_name;

	/**
	 * The current version of the plugin.
	 *
	 * @since    1.1.0
	 * @access   protected
	 * @var      string $version The current version of the plugin.
	 */
	protected $version;

	/**
	 * Define the core functionality of the plugin.
	 *
	 * Set the plugin name and the plugin version that can be used throughout the plugin.
	 * Load the dependencies, define the locale, and set the hooks for the admin area and
	 * the cron-facing side of the site.
	 *
	 * @since    1.1.0
	 */
	public function __construct() {
		if ( defined( 'WPWTP_VERSION' ) ) {
			$this->version = WPWTP_VERSION;
		} else {
			$this->version = '1.1.0';
		}
		$this->plugin_name = 'wpsync-webspark';

		$this->load_dependencies();
		$this->define_admin_hooks();
		$this->define_cron_hooks();

	}

	/**
	 * Load the required dependencies for this plugin.
	 *
	 * Include the following files that make up the plugin:
	 *
	 * - Webspark_Loader. Orchestrates the hooks of the plugin.
	 * - Webspark_Admin. Defines all hooks for the admin area.
	 * - Webspark_Public. Defines all hooks for the cron side of the site.
	 *
	 * Create an instance of the loader which will be used to register the hooks
	 * with WordPress.
	 *
	 * @since    1.1.0
	 * @access   private
	 */
	private function load_dependencies() {
		/**
		 * The class responsible for orchestrating the actions and filters of the
		 * core plugin.
		 */
		require_once WPWTP_PLUGIN_DIR . 'includes/class-webspark-loader.php';

		/**
		 * The class responsible for defining all actions that occur in the admin area.
		 */
		require_once WPWTP_PLUGIN_DIR . 'admin/class-webspark-admin.php';

		/**
		 * The class responsible for defining all actions that occur in the cron-facing
		 * side of the site.
		 */
		require_once WPWTP_PLUGIN_DIR . 'cron/class-webspark-cron.php';

		$this->loader = new Webspark_Loader();
	}

	/**
	 * Register all of the hooks related to the admin area functionality
	 * of the plugin.
	 *
	 * @since    1.1.0
	 * @access   private
	 */
	private function define_admin_hooks() {
		$plugin_admin = new Webspark_Admin( $this->get_plugin_name(), $this->get_version() );

		$this->loader->add_action( 'admin_menu', $plugin_admin, 'add_pages' );
		$this->loader->add_action( 'admin_init', $plugin_admin, 'settings_init' );

		foreach ( Webspark_Admin::DEFAULT_OPTIONS as $setting => $value ) {
			$this->loader->add_option( $setting, $value );
			$this->loader->add_setting( 'webspark-settings', $setting );
		}

	}

	/**
	 * Register all of the hooks related to the cron-facing functionality
	 * of the plugin.
	 *
	 * @since    1.1.0
	 * @access   private
	 */
	private function define_cron_hooks() {
		$plugin_cron = new Webspark_Cron( $this->get_plugin_name(), $this->get_version() );

        $this->loader->add_filter( 'cron_schedules', $plugin_cron, 'add_interval', 1 );

        $url = get_option( 'api_url' );
        $data = file_get_contents($url);
        $characters = json_decode($data, true);
        $slice_array = array_slice($characters, 0, get_option( 'product_quantity' ));
//        var_dump($slice_array);

        if ( 0 === count( $slice_array ) ) {
            return;
        }

        foreach ($slice_array as $item) {
            $product_id = wc_get_product_id_by_sku( $item['sku'] );

            if ($product_id) {
                $this->update_product($product_id, $item);
            } else {
                $this->create_product($item);
            }
        }
	}

    public function get_all_products() {
        $full_product_list = [];
        $args = array(
            'post_type'      => 'product',
            'posts_per_page' => -1,
        );

        $loop = new WP_Query( $args );

        while ($loop->have_posts()) : $loop->the_post();
            $theid = get_the_ID();

                $sku = get_post_meta($theid, '_sku', true);
                $regular_price = get_post_meta($theid, '_regular_price', true);
                $description = get_the_content($theid);
                $thetitle = get_the_title($theid);
                $modificated = get_date_modified($theid);

            if (!empty($sku))
                $full_product_list[] = ['ID' => (int)$theid,
                                        'description' => $description,
                                        'title' => $thetitle,
                                        'sku' => $sku,
                                        'price' => $regular_price,
                                        'modificated' => $modificated
                ];
        endwhile;

        return $full_product_list;
    }

    /**
     * Remove non-updated products.
     *
     * @since    1.1.0
     */

    public function remove_products() {
	    $time = current_time( 'timestamp' );
        $products = $this->get_all_products();

        if($products) {

            foreach ($products as $product) {
                $product = new WC_Product($product->ID);

                if($product['modificated'] > ($time - get_option('api_cron_interval')*60)) {

                    return;
                } else {
                    $product->delete();
                }
            }
        }
    }

    /**
     * Create product by SKU.
     *
     * @param $item
     *
     * @since    1.1.0
     */
	public function create_product($item) {
        $product = new WC_Product_Simple();

        $product->set_name($item['name']);
        $product->set_description( $item['description'] );
        $product->set_sku( $item['sku'] );
        $product->set_stock_status( $item['in_stock'] );
        $product->set_regular_price( $item['price'] );
        $product->set_status('publish');
   //     $product_id = $product->save();

   //     return;
    }

    /**
     * Update product by ID.
     *
     * @param $id
     * @param $item
     *
     * @since    1.1.0
     */
    public function update_product($id, $item) {
        $product = wc_get_product($id);

        $product->set_name($item['name']);
        $product->set_description( $item['description'] );
        $product->set_stock_status( $item['in_stock'] );
        $product->set_regular_price( $item['price'] );
   //     $product_id = $product->save();

   //     return;
    }

	/**
	 * Run the loader to execute all of the hooks with WordPress.
	 *
	 * @since    1.1.0
	 */
	public function run() {
		$this->loader->run();
	}

	/**
	 * The name of the plugin used to uniquely identify it within the context of
	 * WordPress and to define internationalization functionality.
	 *
	 * @return    string    The name of the plugin.
	 * @since     1.1.0
	 */
	public function get_plugin_name() {
		return $this->plugin_name;
	}

	/**
	 * The reference to the class that orchestrates the hooks with the plugin.
	 *
	 * @return    Webspark_Loader    Orchestrates the hooks of the plugin.
	 * @since     1.1.0
	 */
	public function get_loader() {
		return $this->loader;
	}

	/**
	 * Retrieve the version number of the plugin.
	 *
	 * @return    string    The version number of the plugin.
	 * @since     1.1.0
	 */
	public function get_version() {
		return $this->version;
	}
}

