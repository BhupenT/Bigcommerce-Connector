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
class Wordpress_Sync_Upon_Save {


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
	public function __construct($plugin_name, $version) {
		
		$this->plugin_name = $plugin_name;
		$this->version = $version;
		$this->options = get_option($this->plugin_name);

	}

	public function sync_post_upon_update($post_id) {

		if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE )
            return;

		// If this is just a revision, don't send the email.
		if ( wp_is_post_revision( $post_id ) )
			return;

		$post_type = get_post_type($post_id);

		$sync = array(
			'post' => 'post',
			'page' => 'page',
		);

		$sync = apply_filters_ref_array('bigcommerce_sync_custom_post_type', array($sync));

		if(!$sync[$post_type] || empty($sync[$post_type]))
			return;


		if($this->options['sync_post'] === 0 && 'post' == $post_type)
			return; // syn wp post are not checked in options


		if($this->options['sync_pages'] === 0 && 'page' == $post_type)
			return; // syn wp pages are not checked in options


		//check nonces and security

		if ( ! empty( $_POST ) && check_admin_referer('add_bigcomm_url', 'bigcommpermalink') ) {

			if(isset($_POST['bigcomm_post_link'])) {
				$permalink = get_corrected_permalink($_POST['bigcomm_post_link']);

				update_post_meta($post_id, 'bigcom_permalink', $permalink);
			}

			$response = new Wordpress_Sync();

			$response = $response->sync_posts_pages($post_id, $sync[$post_type]);


			if($response == 404) {
				add_filter( 'redirect_post_location', array( $this, 'add_notice_query_var_error' ), 99 );

			}elseif ($response === false) {
				
				add_filter( 'redirect_post_location', array( $this, 'add_notice_general_errors' ), 99 );

			}else{

				add_filter( 'redirect_post_location', array( $this, 'add_notice_query_var_success' ), 99 );
			}
		}

	}

	public function add_notice_general_errors($location) {
		remove_filter( 'redirect_post_location', array( $this, 'add_notice_general_errors' ), 99 );
		return add_query_arg(array('sync_error' => 'false'), $location);
	}


	public function add_notice_query_var_error($location) {

		remove_filter( 'redirect_post_location', array( $this, 'add_notice_query_var_error' ), 99 );
		return add_query_arg(array('sync_error' => '404'), $location);

	}

	public function add_notice_query_var_success($location) {

		remove_filter( 'redirect_post_location', array( $this, 'add_notice_query_var_success' ), 99 );
		return add_query_arg(array('sync_success' => ''), $location);

	}

	public function remove_current_query_arg($arg) { ?>
		<script type="text/javascript">

				var locationUrl = window.location.href;
				var replaceUrl = locationUrl.replace('<?php echo $arg; ?>', '');
				console.log(replaceUrl);
				window.history.pushState('data', 'Title', replaceUrl);

		</script>
	<?php }


	public function add_admin_notices() {

		if(isset($_GET['sync_error']) && !empty($_GET['sync_error'])) { 

			if($_GET['sync_error'] == '404' ) {

			
				if(!empty($_POST) && check_admin_referer( $this->plugin_name . '_del_meta' , 'delete_bigcom_id' )) {

					if(isset($_POST['clear_data']) && $_POST['clear_data'] == 'Clear') {
						update_post_meta( $_GET['post'], 'bigcom_id', '' );
						$this->remove_current_query_arg('&sync_error=404');
						return;
					}

				} ?>

				<div class="notice notice-error is-dismissible">
			        <p><?php _e( 'Post could not be synched! Please check if the post/page exist in the Bigcommerce Store.', 'bigcommerce-connector' ); ?></p>
			        <form action="" method="post" name="clear_meta">
			        	<p>Clear and create a new post on next update?</p>
			        	<?php submit_button('Clear', 'delete', 'clear_data', false); ?>
			        	<?php wp_nonce_field( $this->plugin_name . '_del_meta', 'delete_bigcom_id' ); ?>
			        	<p></p>
			        </form>

			    </div>

			<?php }else{

				//must be false and other errors
				$this->remove_current_query_arg('&sync_error=false');?>
				<div class="notice notice-error is-dismissible">
			        <p><?php _e( 'Post could not be synched! Some datas while posting are not allowed in Bigcommerce. Please Edit post and try again.', 'bigcommerce-connector' ); ?>
			        </p>
			    </div>


			<?php } ?>

		<?php } elseif (isset($_GET['sync_success'])) { ?>

			<div class="notice notice-success is-dismissible">
		        <p><?php _e( 'Success! Synched to Bigcommerce Store.' ); ?></p>
		    </div>
		    <?php $this->remove_current_query_arg('&sync_success'); ?>
		    
		<?php }else{

			return;
		}

	}

	public function register_post_meta_boxes($post_type) {

		$sync = array(
			'post' => 'post',
			'page' => 'page',
		);

		$sync = apply_filters_ref_array('bigcommerce_sync_custom_post_type', array($sync));

		//Assign post type array of later use
		$display_post_type = array();
		foreach ($sync as $key => $value) { 
			$display_post_type[] = $key;
		}

		// register to those that have sync activated in the settings
		if($this->options['sync_post'] === 0 && 'post' == $post_type) {
			$key = array_search($post_type, $display_post_type);
			unset($display_post_type[$key]);
			//return; // syn wp post are not checked in options
		}

		if($this->options['sync_pages'] === 0 && 'page' == $post_type) {
			$key = array_search($post_type, $display_post_type);
			unset($display_post_type['page']);
			//return; // syn wp pages are not checked in options
		}

		//var_dump($display_post_type); exit;

		add_meta_box(
			'Bigcom-permalink',
			esc_html__('Bigcommerce Custom Permalink', $this->plugin_name),
			array($this, 'metabox_display_callback'),
			$display_post_type,
			'side',
			'high'
		);
	}


	public function metabox_display_callback($post) { 
		$current_url = get_post_meta($post->ID, 'bigcom_permalink', true);
		$current_url = ($current_url || !empty($current_url)) ? $current_url : ''; ?>

		<p>If left Empty, the permalink will be used from settings default</p>
		<input type="text" name="bigcomm_post_link" id="Bigcomlink" value="<?php echo $current_url; ?>" placeholder="/my-post/">
		 <?php wp_nonce_field( 'add_bigcomm_url', 'bigcommpermalink' );
	}
}