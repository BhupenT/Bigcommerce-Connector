<?php

/**
 * Provide a admin area view for the plugin
 *
 * This file is used to markup the admin-facing aspects of the plugin.
 *
 * @link       https://www.matterdesgin.com.au
 * @since      1.0.0
 *
 * @package    Bigcommerce_Connector
 * @subpackage Bigcommerce_Connector/admin/partials
 */
?>

<!-- This file should primarily consist of HTML with a little bit of PHP. -->
<!-- This file should primarily consist of HTML with a little bit of PHP. -->

<div class="wrap bigcom-settings-page connector">

    <h2><?php echo esc_html(get_admin_page_title()); ?></h2>
    
    <form method="post" name="plugin_settings" action="options.php">
    	<?php 

	    	$connect = New Bigcommerce_Connect();

			//$response = $connect->get_pages();

			//var_dump($response);

    		$options = get_option($this->plugin_name);
    		
    		settings_fields($this->plugin_name);
        	do_settings_sections($this->plugin_name);

        	$client_id = (!empty($options['client_id'])) ? Bigcommerce_Connect::get_sensitive_data($options['client_id']) : '';

        	$client_token = (!empty($options['client_token']))?Bigcommerce_Connect::get_sensitive_data($options['client_token']) : '';

        	//var_dump($options);
    	?>
    
    <div>
		<h3><?php esc_attr_e( 'Authentication', 'wp_admin_style' ); ?></h3>

		<span><strong>Bigcommerce Store hash:</strong> </span><input class="medium-field" type="text" id="<?php echo $this->plugin_name; ?>-hash" name="<?php echo $this->plugin_name; ?>[storehash]" placeholder="xxxxxxxxx" value="<?php echo $options['storehash']; ?>" />
		<p class="small">The Bigcommerce store hash value can also be derived from your temp URL. For example, if your temp URL is http ://store-xxxxxxxxx.mybigcommerce.com/ then your store_hash value is the xxxxxxxxx</p>
			
	</div>
    	

    <div>
		<span><strong>Bigcommerce Client API:</strong> </span><input class="medium-field" type="text" id="<?php echo $this->plugin_name; ?>-clientid" name="<?php echo $this->plugin_name; ?>[client_id]" value="<?php echo $client_id; ?>"/>
			
	</div>

	<div>
		<span><strong>Bigcommerce Client Token:</strong> </span><input class="medium-field" type="text" id="<?php echo $this->plugin_name; ?>-client-token" name="<?php echo $this->plugin_name; ?>[client_token]" value="<?php echo $client_token; ?>"/>
			
	</div>


	<div>
		<h3><?php esc_attr_e( 'Auto Sync Features', 'wp_admin_style' ); ?></h3>
		<p>Please check the following options that you would like to auto Sync to your Bigcommerce Store </p>
		<fieldset>
	        <legend class="screen-reader-text">
	            <span>Auto Sync Wordpress Post</span>
	        </legend>
	        <label for="<?php echo $this->plugin_name; ?>-sync-post">
	            <input type="checkbox" id="<?php echo $this->plugin_name; ?>-sync-post" name="<?php echo $this->plugin_name; ?>[sync_post]" value="1" <?php checked($options['sync_post'], 1); ?> />
	            <span><?php esc_attr_e('Sync Wordpress Posts', $this->plugin_name); ?></span>
	        </label>
    	</fieldset>

    	<fieldset>
	        <legend class="screen-reader-text">
	            <span>Auto Sync Wordpress Pages</span>
	        </legend>
	        <label for="<?php echo $this->plugin_name; ?>-sync-pages">
	            <input type="checkbox" id="<?php echo $this->plugin_name; ?>-sync-pages" name="<?php echo $this->plugin_name; ?>[sync_pages]" value="1" <?php checked($options['sync_pages'], 1); ?> />
	            <span><?php esc_attr_e('Sync Wordpress Pages', $this->plugin_name); ?></span>
	        </label>
    	</fieldset>
	</div>

	<div>
		<h3><?php esc_attr_e( 'URL Structure', 'wp_admin_style' ); ?></h3>

		<p>The following URL structure is for the default post and pages url permalink structure base. Only Postname are supported as of now. To have a custom url for specific pages please edit in the post or page edit screen.</p>

		<span><strong>Post Default URL: </strong></span><input class="medium-field" type="text" id="<?php echo $this->plugin_name; ?>-post-url" name="<?php echo $this->plugin_name; ?>[post_url]" placeholder="/blog/%postname%" value="<?php echo $options['post_url']; ?>" />
			
	</div>

	<div>
		<span><strong>Pages Default URL: </strong></span><input class="medium-field" type="text" id="<?php echo $this->plugin_name; ?>-page-url" name="<?php echo $this->plugin_name; ?>[page_url]" placeholder="/%postname%" value="<?php echo $options['page_url']; ?>" />
	</div>


	<div>
		<h3><?php esc_attr_e( 'Bulk Sync all posts including Pages', 'wp_admin_style' ); ?></h3>
		
		 <?php submit_button('Sync Now', 'primary','sync-now', false); ?>
		 <span class="progress-label" style="display: none;">Synching... <span id="progress-data">please wait...</span></span>
	</div>

	<div id="sync-log" style="display: none;"></div>
	<div id="Fixerrors" style="display: none;"><?php submit_button('Fix Now', 'delete','fix-now', false); ?></div>

	<div class="progress-container" style="display: none;">
		<div id="progressbar"><div class="progress-status">Synching...</div></div>
	</div>
	

    <?php submit_button('Save all changes', 'primary','submit', TRUE); 
    wp_nonce_field( $this->plugin_name . '_save_settings', 'save_settings' );?>

    </form>

</div>
