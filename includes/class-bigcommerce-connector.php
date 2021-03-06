<?php

/**
 * The file that defines the core plugin class
 *
 * A class definition that includes attributes and functions used across both the
 * public-facing side of the site and the admin area.
 *
 * @link       https://www.matterdesgin.com.au
 * @since      1.0.0
 *
 * @package    Bigcommerce_Connector
 * @subpackage Bigcommerce_Connector/includes
 */

/**
 * The core plugin class.
 *
 * This is used to define internationalization, admin-specific hooks, and
 * public-facing site hooks.
 *
 * Also maintains the unique identifier of this plugin as well as the current
 * version of the plugin.
 *
 * @since      1.0.0
 * @package    Bigcommerce_Connector
 * @subpackage Bigcommerce_Connector/includes
 * @author     Bhupendra Tamang <bhupendra@matterdesign.com.au>
 */
class Bigcommerce_Connector {

	/**
	 * The loader that's responsible for maintaining and registering all hooks that power
	 * the plugin.
	 *
	 * @since    1.0.0
	 * @access   protected
	 * @var      Bigcommerce_Connector_Loader    $loader    Maintains and registers all hooks for the plugin.
	 */
	protected $loader;

	/**
	 * The unique identifier of this plugin.
	 *
	 * @since    1.0.0
	 * @access   protected
	 * @var      string    $plugin_name    The string used to uniquely identify this plugin.
	 */
	protected $plugin_name;

	/**
	 * The current version of the plugin.
	 *
	 * @since    1.0.0
	 * @access   protected
	 * @var      string    $version    The current version of the plugin.
	 */
	protected $version;


	/**
	 * The current version of api connection.
	 *
	 * @since    1.0.0
	 * @access   protected
	 * @var      string    $version    The current version of the api connection
	 */
	protected $api_version;


	/**
	 * Define the core functionality of the plugin.
	 *
	 * Set the plugin name and the plugin version that can be used throughout the plugin.
	 * Load the dependencies, define the locale, and set the hooks for the admin area and
	 * the public-facing side of the site.
	 *
	 * @since    1.0.0
	 */
	public function __construct() {
		if ( defined( 'PLUGIN_NAME_VERSION' ) ) {
			$this->version = PLUGIN_NAME_VERSION;
		} else {
			$this->version = '1.0.0';
		}
		$this->plugin_name = 'bigcommerce-connector';

		$this->load_dependencies();
		$this->set_locale();
		$this->define_admin_hooks();
		$this->define_wpsync_hooks();

	}

	/**
	 * Load the required dependencies for this plugin.
	 *
	 * Include the following files that make up the plugin:
	 *
	 * - Bigcommerce_Connector_Loader. Orchestrates the hooks of the plugin.
	 * - Bigcommerce_Connector_i18n. Defines internationalization functionality.
	 * - Bigcommerce_Connector_Admin. Defines all hooks for the admin area.
	 * - Bigcommerce_Connector_Public. Defines all hooks for the public side of the site.
	 *
	 * Create an instance of the loader which will be used to register the hooks
	 * with WordPress.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function load_dependencies() {

		/**
		 * The class responsible for orchestrating the actions and filters of the
		 * core plugin.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-bigcommerce-connector-loader.php';

		/**
		 * The class responsible for defining internationalization functionality
		 * of the plugin.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-bigcommerce-connector-i18n.php';

		/**
		 * Collection of helper functions
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'admin/helper-static-functions.php';

		/**
		 * The class responsible for handling all Api related connections
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'admin/class-bigcommerce-connection.php';


		/**
		 * The class responsible for handling all wordpress sync related funtionalities
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'admin/class-wordpress-sync.php';


		/**
		 * The class responsible for handling all wordpress sync upon post save/publish/update
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'admin/class-wordpress-sync-upon-save.php';


		/**
		 * The class responsible for defining all actions that occur in the admin area.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'admin/class-bigcommerce-connector-admin.php';

		$this->loader = new Bigcommerce_Connector_Loader();

	}

	/**
	 * Define the locale for this plugin for internationalization.
	 *
	 * Uses the Bigcommerce_Connector_i18n class in order to set the domain and to register the hook
	 * with WordPress.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function set_locale() {

		$plugin_i18n = new Bigcommerce_Connector_i18n();

		$this->loader->add_action( 'plugins_loaded', $plugin_i18n, 'load_plugin_textdomain' );

	}

	/**
	 * Register all of the hooks related to the admin area functionality
	 * of the plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function define_admin_hooks() {

		$plugin_admin = new Bigcommerce_Connector_Admin( $this->get_plugin_name(), $this->get_version() );

		$this->loader->add_action( 'admin_enqueue_scripts', $plugin_admin, 'enqueue_styles' );
		$this->loader->add_action( 'admin_enqueue_scripts', $plugin_admin, 'enqueue_scripts' );
		$this->loader->add_action('admin_menu', $plugin_admin, 'add_plugin_menu');

		$plugin_base = plugin_basename(plugin_dir_path(__DIR__) . $this->plugin_name . '.php');

		$this->loader->add_filter('plugin_action_links_' . $plugin_base, $plugin_admin, 'add_setting_link');

		$this->loader->add_action('admin_init', $plugin_admin, 'options_update');

		$this->loader->add_action('wp_ajax_fetch_posts', $plugin_admin, 'ajax_fetch_posts_ids');
		$this->loader->add_action('wp_ajax_sync_posts', $plugin_admin, 'ajax_bigcom_sync_posts');
		$this->loader->add_action('wp_ajax_cleanup_postmetas', $plugin_admin, 'ajax_cleanup_postmetas');

	}

	/**
	 * Register all of the hooks related to the wordpress sync functionality
	 * of the plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function define_wpsync_hooks() {

		$plugin_admin = new Wordpress_Sync_Upon_Save( $this->get_plugin_name(), $this->get_version());

		$this->loader->add_action('save_post', $plugin_admin, 'sync_post_upon_update');

		$this->loader->add_action( 'admin_notices', $plugin_admin, 'add_admin_notices');
		
		$this->loader->add_action( 'add_meta_boxes', $plugin_admin, 'register_post_meta_boxes');

	}


	/**
	 * Run the loader to execute all of the hooks with WordPress.
	 *
	 * @since    1.0.0
	 */
	public function run() {
		$this->loader->run();
	}

	/**
	 * The name of the plugin used to uniquely identify it within the context of
	 * WordPress and to define internationalization functionality.
	 *
	 * @since     1.0.0
	 * @return    string    The name of the plugin.
	 */
	public function get_plugin_name() {
		return $this->plugin_name;
	}

	/**
	 * The reference to the class that orchestrates the hooks with the plugin.
	 *
	 * @since     1.0.0
	 * @return    Bigcommerce_Connector_Loader    Orchestrates the hooks of the plugin.
	 */
	public function get_loader() {
		return $this->loader;
	}

	/**
	 * Retrieve the version number of the plugin.
	 *
	 * @since     1.0.0
	 * @return    string    The version number of the plugin.
	 */
	public function get_version() {
		return $this->version;
	}

}
