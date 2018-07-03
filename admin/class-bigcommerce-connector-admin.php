<?php

/**
 * The admin-specific functionality of the plugin.
 *
 * @link       https://www.matterdesgin.com.au
 * @since      1.0.0
 *
 * @package    Bigcommerce_Connector
 * @subpackage Bigcommerce_Connector/admin
 */

/**
 * The admin-specific functionality of the plugin.
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the admin-specific stylesheet and JavaScript.
 *
 * @package    Bigcommerce_Connector
 * @subpackage Bigcommerce_Connector/admin
 * @author     Bhupendra Tamang <bhupendra@matterdesign.com.au>
 */
class Bigcommerce_Connector_Admin {

	/**
	 * The ID of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $plugin_name    The ID of this plugin.
	 */
	private $plugin_name;

	/**
	 * The version of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $version    The current version of this plugin.
	 */
	private $version;

	/**
	 * Initialize the class and set its properties.
	 *
	 * @since    1.0.0
	 * @param      string    $plugin_name       The name of this plugin.
	 * @param      string    $version    The version of this plugin.
	 */
	public function __construct( $plugin_name, $version ) {

		$this->plugin_name = $plugin_name;
		$this->version = $version;

	}

	/**
	 * Register the stylesheets for the admin area.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_styles() {

		/**
		 * This function is provided for demonstration purposes only.
		 *
		 * An instance of this class should be passed to the run() function
		 * defined in Bigcommerce_Connector_Loader as all of the hooks are defined
		 * in that particular class.
		 *
		 * The Bigcommerce_Connector_Loader will then create the relationship
		 * between the defined hooks and the functions defined in this
		 * class.
		 */

		wp_enqueue_style( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'css/bigcommerce-connector-admin.css', array(), $this->version, 'all' );

	}

	/**
	 * Register the JavaScript for the admin area.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_scripts() {

		/**
		 * This function is provided for demonstration purposes only.
		 *
		 * An instance of this class should be passed to the run() function
		 * defined in Bigcommerce_Connector_Loader as all of the hooks are defined
		 * in that particular class.
		 *
		 * The Bigcommerce_Connector_Loader will then create the relationship
		 * between the defined hooks and the functions defined in this
		 * class.
		 */

		wp_enqueue_script( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'js/bigcommerce-connector-admin.js', array( 'jquery' ), $this->version, false );

	}

	public function add_plugin_menu( ) {
		
		add_menu_page( 'BigCommerce Settings', 'BigCommerce Connector', 'manage_options', $this->plugin_name, array($this, 'plugin_setting_page'), 'dashicons-image-filter', 3);
		
	}


	public function add_setting_link($links) {
	
		$setting_link = array(
			'<a href="' . admin_url( 'admin.php?page=' . $this->plugin_name) . '">' . __('Settings', $this->plugin_name) . '</a>',	
		);

		return array_merge($setting_link, $links);
	
	}

	public function plugin_setting_page() {
	
		include_once('partials/bigcommerce-connector-admin-display.php');
	
	}


	public function options_update() {
	    register_setting($this->plugin_name, $this->plugin_name, array($this, 'validate'));
	}

	public function validate($input) {    
	    $valid = array();
	    $valid['storehash'] =  (isset($input['storehash']) && !empty($input['storehash'])) ? sanitize_text_field($input['storehash']) : '';

	    $valid['api'] =  (isset($input['api']) && !empty($input['api'])) ? sanitize_text_field($input['api']) : '';

	    $valid['client_id'] =  (isset($input['client_id']) && !empty($input['client_id'])) ? sanitize_text_field(Bigcommerce_Connect::save_sensitive_data($input['client_id'])) : '';

	    $valid['client_token'] =  (isset($input['client_token']) && !empty($input['client_token'])) ? sanitize_text_field(Bigcommerce_Connect::save_sensitive_data($input['client_token'])) : '';

	    $valid['sync_post'] = (isset($input['sync_post']) && !empty($input['sync_post'])) ? 1 : 0;

	    $valid['sync_pages'] = (isset($input['sync_pages']) && !empty($input['sync_pages'])) ? 1 : 0;


	    $valid['post_url'] = (isset($input['post_url']) && !empty($input['post_url'])) ? sanitize_text_field($input['post_url']) : '/blog/%postname%';

	    $valid['page_url'] = (isset($input['page_url']) && !empty($input['page_url'])) ? sanitize_text_field($input['page_url']) : '/%postname%';
	    return $valid;
 	}

}
