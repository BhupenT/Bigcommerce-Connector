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
	public function enqueue_styles($hook) {

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

		if($hook != 'post.php' && $hook != 'toplevel_page_' . $this->plugin_name)
			return;

		wp_enqueue_style( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'css/bigcommerce-connector-admin.css', array(), $this->version, 'all' );

		if($hook == 'toplevel_page_' .  $this->plugin_name) {
			wp_enqueue_style( $this->plugin_name . 'jquery-ui', plugin_dir_url( __FILE__ ) . 'css/jquery-ui.min.css', array(), $this->version, 'all' );
		}

	}

	/**
	 * Register the JavaScript for the admin area.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_scripts($hook) {

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

		if($hook != 'toplevel_page_' . $this->plugin_name) { // only for the plugin settings page
			return;
		}

		//wp_enqueue_script('jquery-ui-progressbar'); // the progress bar

		wp_enqueue_script( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'js/bigcommerce-connector-admin.js', array( 'jquery', 'jquery-ui-progressbar' ), $this->version, true );

		$nonce_forids = wp_create_nonce($this->plugin_name . '-fetch-postids');
		$nonce_forsync = wp_create_nonce($this->plugin_name . '-sync-posts');
		$nonce_forcleanup_metas = wp_create_nonce($this->plugin_name . '-clean-metas');

		wp_localize_script($this->plugin_name, 'global_object', array(
			'post_url' 	=> admin_url('admin-ajax.php'),
			'security'	=> $nonce_forids,
			'syncpost'	=> $nonce_forsync,
			'cleanup'	=> $nonce_forcleanup_metas
		));

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

		if(!empty($_POST) && check_admin_referer( $this->plugin_name . '_save_settings' , 'save_settings' )) {

		    $valid = array();
		    $valid['storehash'] =  (isset($input['storehash']) && !empty($input['storehash'])) ? sanitize_text_field($input['storehash']) : '';

		    $valid['api'] =  (isset($input['api']) && !empty($input['api'])) ? sanitize_text_field($input['api']) : '';

		    $valid['client_id'] =  (isset($input['client_id']) && !empty($input['client_id'])) ? sanitize_text_field(Bigcommerce_Connect::save_sensitive_data($input['client_id'])) : '';

		    $valid['client_token'] =  (isset($input['client_token']) && !empty($input['client_token'])) ? sanitize_text_field(Bigcommerce_Connect::save_sensitive_data($input['client_token'])) : '';

		    $valid['sync_post'] = (isset($input['sync_post']) && !empty($input['sync_post'])) ? 1 : 0;

		    $valid['sync_pages'] = (isset($input['sync_pages']) && !empty($input['sync_pages'])) ? 1 : 0;


		    $valid['post_url'] = (isset($input['post_url']) && !empty($input['post_url'])) ? get_corrected_permalink($input['post_url']) : '/blog/%postname%/';

		    $valid['page_url'] = (isset($input['page_url']) && !empty($input['page_url'])) ? get_corrected_permalink($input['page_url']) : '/%postname%/';
		    return $valid;
		}
 	}


 	public function ajax_fetch_posts_ids() {

		if ( defined( 'DOING_AJAX' ) && DOING_AJAX ) {

			check_ajax_referer( $this->plugin_name . '-fetch-postids', 'security' );

			$sync = array(
				'post'	=> 'post',
				'page'	=> 'page'
			);

			$sync = apply_filters_ref_array('bigcommerce_bulksync_custom_post_type', array($sync));

			$post_types = array();
			foreach ($sync as $key => $value) {
				$post_types[] = $key;
			}

	 		// For syching all the post and pages at once
	 		$args = array(
				'posts_per_page' => -1,
				'post_type' => $post_types,
			);

			$posts = get_posts($args);
			$ids = array();
			foreach ($posts as $post) {
				$ids[] = $post->ID;
			}
			echo json_encode($ids);
	 	}

	 	wp_die();

 	}


 	public function ajax_bigcom_sync_posts() {

 		// For syching all the post and pages at once

 		if ( defined( 'DOING_AJAX' ) && DOING_AJAX ) {
 			check_ajax_referer( $this->plugin_name . '-sync-posts', 'syncpost' );

	 		$post_id = ( isset($_POST['postid']) && !empty($_POST['postid']) ) ? intval(sanitize_text_field($_POST['postid'])) : null;

	 		if($post_id === null) 
	 			wp_die('postid not found', 300);

	 		$title = get_the_title($post_id);
	 		$mesg = $title . ' - ' . $post_id;

 			/** May be custom post type or default that has been registered via filter
 			 ** bigcommerce_bulksync_custom_post_type - filter
 			 */

 			$post_type = get_post_type($post_id);

 			$bulk_sync = array(
 				'post'	=> 'post',
 				'page'	=> 'page'
 			);

 			$bulk_sync = apply_filters_ref_array('bigcommerce_bulksync_custom_post_type', array($bulk_sync));

 			$defaultsto = $bulk_sync[$post_type];

			$syncpost = New Wordpress_Sync();

	 		$response = $syncpost->sync_posts_pages($post_id, $defaultsto);

	 		if($response == false)
	 			wp_die($mesg, 400);

	 		if($response == 404)
	 			wp_die($mesg, $response);

	 		echo $mesg;
	 	}

 		wp_die();

 	}

 	public function ajax_cleanup_postmetas() {

 		// For syching all the post and pages at once

 		if ( defined( 'DOING_AJAX' ) && DOING_AJAX ) {
 			check_ajax_referer( $this->plugin_name . '-clean-metas', 'cleanup' );

	 		$post_ids = ( isset($_POST['postids']) && !empty($_POST['postids']) ) ? json_decode($_POST['postids'], true) : null;
	 		//echo json_encode($post_ids);

	 		if($post_ids === null) 
	 			wp_die();

	 		$post_ids = array_map(function($item) {
	 			return update_post_meta(intval(sanitize_text_field($item)), 'bigcom_id', '');
	 		}, $post_ids);

	 		echo true;
	 		
	 	}

 		wp_die();

 	}

}
