=== Plugin Name ===
Contributors: (this should be a list of wordpress.org userid's)
Donate link: https://www.matterdesgin.com.au
Tags: Bigcommerce, Sync, Bulk Sync, Wordpress Bigcommerce Integration
Requires at least: 3.0.1
Tested up to: 3.4
Stable tag: 4.3
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

Integrates your Wordpress posts and pages to Bigcommerce Site.

== Description ==

Bigcommerce Connector is a wordpress plugin that Integrates and syncs your Wordpress posts and pages to Bigcommerce Site.
Please try this plugin out and give me your feedbacks and any issues that you may encounter. This Plugin that we can use in the future on those sites that may want to move to Bigcommerce from Wordpress or they simply want to edit in Wordpress and publish in the Bigcommerce side.

Some of the features of the plugin are as follows:

1: Auto Sync  —  Syncs Wordpress post and pages upon publish/updates Automatically

2: Permalink Control per post —  Ability to change the permalink/URL of the post/pages in the bigcommerce side - (This works per post/pages)

3: Global Permalink — Option to change the permalink/URL of the post/pages as a default settings that is set in the plugin setting page (if the permalink is set in the specific page using the above feature will override the global option)

4: Bulk sync  — Sync all the post and pages from the plugin settings if they choose to. (rather than doing from each pages)

5: Fix Sync Issue — When encountered an error related to page/post that may have deleted in the Bigcomerce. Plugin will automatically detect and gives option to fix those issues.

6: Sync Status log — Shows a detailed log upon bulk sync about the sync status.

7: Custom Post type Sync — Plugin does has feature to sync custom post that may have developed by devs and using a simple/easy plugins hooks its very easy

8: Custom Post type Sync — Hooks for developers to allow their custom post type to include in bulk sync


== Installation ==

Upload the plugin and active and enjoy

This section describes how to install the plugin and get it working.

e.g.

1. Upload `bigcommerce-connector.php` to the `/wp-content/plugins/` directory
1. Activate the plugin through the 'Plugins' menu in WordPress


== Frequently Asked Questions ==

= Any hooks or Filter so it can support a custom Post Type?

Yes, Its very easy, there is two filters that allows you to add your custom post type to auto and bulk sync to Bigcommerce

	
	add_filter('bigcommerce_sync_custom_post_type', 'my_callback');
	function my_callback($sync) {
		$sync['testimonials'] = 'page';
		return $sync;
	}


	add_filter('bigcommerce_bulksync_custom_post_type', 'my_callback1');
	function my_callback1($sync) {
		$sync['testimonials'] = 'page';
		return $sync;
	}


== Screenshots ==

1. This screen shot description corresponds to screenshot-1.(png|jpg|jpeg|gif). Note that the screenshot is taken from
the /assets directory or the directory that contains the stable readme.txt (tags or trunk). Screenshots in the /assets
directory take precedence. For example, `/assets/screenshot-1.png` would win over `/tags/4.3/screenshot-1.png`
(or jpg, jpeg, gif).
2. This is the second screen shot

== Changelog ==

= 1.0.0 =
* Initial release

== Arbitrary section ==

You may provide arbitrary sections, in the same format as the ones above.  This may be of use for extremely complicated
plugins where more information needs to be conveyed that doesn't fit into the categories of "description" or
"installation."  Arbitrary sections will be shown below the built-in sections outlined above.