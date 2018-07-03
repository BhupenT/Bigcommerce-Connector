<?php

/**
 * BigCommerce API connection functionality of the plugin.
 *
 * @link       https://www.matterdesgin.com.au
 * @since      1.0.0
 *
 * @package    Bigcommerce_Connector
 * @subpackage Bigcommerce_Connector/admin
 */

/**
 * BigCommerce API connection functionality of the plugin.
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the admin-specific stylesheet and JavaScript.
 *
 * @package    Bigcommerce_Connector
 * @subpackage Bigcommerce_Connector/admin
 * @author     Bhupendra Tamang <bhupendra@matterdesign.com.au>
 */
class Bigcommerce_Connect {


	/**
	 * @var encryption key
	**/
	protected static $encryp_key ='bRuD5WYw5wd0rdHR9yLlM6wt2vteuiniQBqE70nAuhU';


	private $options;

	/**
	 * The ID of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $plugin_name    The ID of this plugin.
	 */

	private $plugin_name;

	/**
	 * The version of this api
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $version    The current version of this api.
	 */
	private $api_version;


	/**
	 * Initialize the class and set its properties.
	 *
	 * @since    1.0.0
	 * @param      string    $plugin_name       The name of this plugin.
	 * @param      string    $version    The version of this plugin.
	 */
	public function __construct() {

		$plugin_name = new Bigcommerce_Connector();
		$plugin_name = $plugin_name->get_plugin_name();
		$this->plugin_name = $plugin_name;
		$this->api_version = 'v2';
		$this->options = get_option($this->plugin_name);

	}

	public static function save_sensitive_data($value) {
		$data = self::encrypt_imp_data($value, self::$encryp_key);
		return $data;
	}

	public static function get_sensitive_data($value) {
		$data = self::decrypt_imp_data($value, self::$encryp_key);
		return $data;
	}



	public function get_posts($id = '', $param = '') {

		$param = $this->setup_api_call('posts', 'GET', $id, $param);
		list($url, $param) = $param;
		return $this->process_api_call($url, $param);

	}

	public function get_pages($id = '', $param = '') {

		$param = $this->setup_api_call('pages', 'GET', $id, $param);
		list($url, $param) = $param;
		return $this->process_api_call($url, $param);
		
	}


	public function update_posts() {
		


	}


	public function update_pages() {


		
	}


	public function process_api_call($url, $param) {

		$response = wp_remote_request( $url, $param );

		if(200 != wp_remote_retrieve_response_code( $response )) {
			return false;
		}

		// If all ok
		return json_decode( wp_remote_retrieve_body( $response ) );

	}


	protected function encrypt_imp_data($data, $key) {
	    // Remove the base64 encoding from our key
	    $encryption_key = base64_decode($key);

	    $encrypted = @openssl_encrypt($data, 'aes-256-cbc', $encryption_key);
	    return base64_encode($encrypted);
	}

	protected function decrypt_imp_data($data, $key) {
	    // Remove the base64 encoding from our key
	    $encryption_key = base64_decode($key);

	    $encrypted_data = base64_decode($data);
	    return @openssl_decrypt($encrypted_data, 'aes-256-cbc', $encryption_key);
	}


	protected function add_headers($type, $id) {

		if('posts' == $type) {
			$type = '/blog/posts';
		} elseif ('pages' == $type) {
			$type = '/pages';
		}

		$url = 'https://api.bigcommerce.com/stores/' . $this->options['storehash'] . '/' . $this->api_version . $type;

		$url = (null != $id) ? $url . '/' . $id : $url;

		$headers = array(
			'X-Auth-Client'	=> $this->get_sensitive_data($this->options['client_id']),
			'X-Auth-Token'	=> $this->get_sensitive_data($this->options['client_token']),
			'Accept'		=> 'application/json'
		);

		return array($url, $headers);

	}


	protected function setup_api_call($type, $method, $param = '', $id = '') {

		$id = (!$id || !is_int($id)) ? null : $id;

		$array = $this->add_headers($type, $id);

		list($url, $headers) = $array;

		$args = array(
			'method'	=> $method,
			'timeout'	=> 15,
			'headers'	=> $headers,
			'body'		=> $param
		);

		return array($url, $args);

	}

}
