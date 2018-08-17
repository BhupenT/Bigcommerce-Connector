<?php

/**
 * Wordpress Sync functionality of the plugin.
 *
 * @link       https://www.matterdesgin.com.au
 * @since      1.0.0
 *
 * @package    Bigcommerce_Connector
 * @subpackage Wordpress_Sync/admin
 */

/**
 * Wordpress Sync functionality of the plugin.
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the admin-specific stylesheet and JavaScript.
 *
 * @package    Bigcommerce_Connector
 * @subpackage Wordpress_Sync/admin
 * @author     Bhupendra Tamang <bhupendra@matterdesign.com.au>
 */

class Wordpress_Sync {

	private static $options = array();

	/**
	 * The ID of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $plugin_name    The ID of this plugin.
	 */


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
	public function __construct($plugin_name = false, $version = false) {

		if(!$plugin_name || !$version) {
			$class = new Bigcommerce_Connector();
			$plugin_name = $class->get_plugin_name();
			$version = $class->get_version();
		}

		$this->plugin_name = $plugin_name;
		$this->version = $version;
		self::$options = get_option($this->plugin_name);
	}


	public static function prepare_permalink($post_id) {

		$permalink = get_post_meta($post_id, 'bigcom_permalink', true);
		if(!$permalink || empty($permalink)) {
			$type = get_post_type($post_id);

			if($type != 'post' && $type != 'page') {
				// Must be custom post type registered via addfilter
				$sync = array(
					'post' => 'post',
					'page'	=> 'page'
				);

				$sync = apply_filters_ref_array('bigcommerce_sync_custom_post_type', array($sync));

				$type = $sync[$type];
			}

			$structure = trim(self::$options[$type . '_url']);
			$args = explode('%', $structure);
			//return $args;
			$permalink = array();
			foreach ($args as $arg) {
				if($arg == 'postname') {
					$postname = preg_replace('/[^\da-z ]/i', '', get_the_title($post_id));
					$permalink[] = $postname;

				}else{

					$permalink[] = $arg;
				}

			}

			$permalink = implode('', $permalink);
			$permalink = get_corrected_permalink($permalink, true);
		}

		return $permalink;
	}


	public static function prepare_posts($object, $default_req = true) {
		//Preparing for the param
		$synched_id = get_post_meta($object->ID, 'bigcom_id', true);
		
		$param = self::setup_postmetas($object->ID, $object->post_author);

		//Setting Param
		$param['title'] = $object->post_title;
		$param['body']	= $object->post_content;
		$post_status = $object->post_status;
		$param['is_published'] = ($post_status == 'publish') ? true : false;
		$param['url'] = self::prepare_permalink($object->ID);

		if($default_req === false)
			return $param;

		//var_dump($param); exit;

		if($synched_id && !empty($synched_id)) {
			//it has been synched before and the page may exist
			return self::process_sync('post', $synched_id, $param, $object->ID);
		}

		return self::process_sync('post', $id = '', $param, $object->ID);
	}

	public static function setup_postmetas($post_id, $author_id) {

		$first_name = get_the_author_meta('first_name', $author_id);
		$last_name = get_the_author_meta('last_name', $author_id);

		$author = (!$first_name && !$last_name) ? get_the_author_meta('user_login', $author_id) : $first_name . ' ' . $last_name;

		$datetime = new DateTime(get_the_date($d = '', $post_id));
		$post_date = $datetime->format(DateTime::RFC2822);
		$param = array(
			'published_date' => $post_date,
			'author' => $author
		);

		//var_dump($param); exit;

		return $param;
	}


	public static function prepare_pages($object, $default_req = true) {
		$param = array();
		//Preparing for the param
		$synched_id = get_post_meta($object->ID, 'bigcom_id', true);

		//Setting Param
		$param['name'] = $object->post_title;
		$param['body']	= $object->post_content;
		$post_status = $object->post_status;
		$param['is_visible'] = ($post_status == 'publish') ? true : false;

		$param['url'] = self::prepare_permalink($object->ID);

		if($default_req === false)
			return $param;

		$param['type'] = 'page';

		//var_dump($param); exit;

		if($synched_id && !empty($synched_id)) {
			//it has been synched before and the page may exist
			return self::process_sync('page', $synched_id, $param, $object->ID);
		}

		return self::process_sync('page', $id = '', $param, $object->ID);
	}


	public function sync_posts_pages($id = null, $defalutsto = 'post') {

		if($id !== null || !empty($id)) {

			$object = get_post($id);

			if($defalutsto == 'page') {

				return $this->prepare_pages($object);

			}else{

				return $this->prepare_posts($object);
			}

		}else{

			$args = array(
				'posts_per_page' => -1,
				'post_type' => $type
			);

			$posts = get_posts($args);

			if(!$posts) {
				return false;
			}

			foreach ($posts as $post) {

				if($defalutsto == 'page') {

					return $this->prepare_pages($post);

				}else{

					return $this->prepare_posts($post);
				}
			}

		}

	}



	public static function process_sync($post_type = 'post', $id = '', $param, $post_id) {

		switch ($post_type) {
			case 'page':

				if(!$id || empty($id)) {

					$response = self::request_create_page($param);

				}else{

					$response = self::request_update_page($id, $param);
				}

				break;
			
			case 'post':

				if(!$id || empty($id)) {

					$response = self::request_create_blog($param);

				}else{
					
					$response = self::request_update_blog($id, $param);
				}

				break;
		}


		if($response != false && $response != 404) {

			update_post_meta( $post_id, 'bigcom_id', $response->id );

		} elseif (404 == $response) {
			# resources not found

			return $response;
		}


		return $response;

	}


	private function request_create_page($param) {

		$connect = new Bigcommerce_Connect();

		$response = $connect->create_page($param);

		return $response;

	}

	private function request_create_blog($param) {

		$connect = new Bigcommerce_Connect();

		$response = $connect->create_post($param);

		return $response;

	}

	private function request_update_page($id, $param) {

		$connect = new Bigcommerce_Connect();

		$response = $connect->update_page($id, $param);

		return $response;

	}

	private function request_update_blog($id, $param) {

		$connect = new Bigcommerce_Connect();

		$response = $connect->update_post($id, $param);

		return $response;

	}


}
