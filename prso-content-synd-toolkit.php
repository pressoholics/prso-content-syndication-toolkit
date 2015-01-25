<?php
/*
 * Plugin Name: Content Syndication Toolkit
 * Plugin URI: 
 * Description: Content Syndication Toolkit allows you to syndicate content to multiple client sites automatically. Posts, Categories, Tags, and Images.
 * Author: Benjamin Moody
 * Version: 1.0.5
 * Author URI: http://www.benjaminmoody.com
 * License: GPL2+
 * Text Domain: prso_synd_toolkit_plugin
 * Domain Path: /languages/
 */

//Define plugin constants
define( 'PRSOSYNDTOOLKIT__MINIMUM_WP_VERSION', '3.0' );
define( 'PRSOSYNDTOOLKIT__VERSION', '1.0.5' );
define( 'PRSOSYNDTOOLKIT__DOMAIN', 'prso_synd_toolkit_plugin' );

//Plugin admin options will be available in global var with this name, also is database slug for options
define( 'PRSOSYNDTOOLKIT__OPTIONS_NAME', 'prso_synd_toolkit_options' );
define( 'PRSOSYNDTOOLKIT__USER_ROLE', 'pcst_subscriber' );

define( 'PRSOSYNDTOOLKITREADER__WEBHOOK_PARAM', 'pcst_push_webhook' );

define( 'PRSOSYNDTOOLKIT__PLUGIN_DIR', plugin_dir_path( __FILE__ ) );
define( 'PRSOSYNDTOOLKIT__PLUGIN_URL', plugin_dir_url( __FILE__ ) );

define( 'PRSOSYNDTOOLKIT__XMLRPC_LIB', ABSPATH . WPINC . '/class-IXR.php' );

//Include plugin classes
require_once( PRSOSYNDTOOLKIT__PLUGIN_DIR . 'class.prso-content-synd-toolkit.php'               );

//Set Activation/Deactivation hooks
register_activation_hook( __FILE__, array( 'PrsoSyndToolkit', 'plugin_activation' ) );
register_deactivation_hook( __FILE__, array( 'PrsoSyndToolkit', 'plugin_deactivation' ) );

prso_synd_toolkit_master_init();
function prso_synd_toolkit_master_init() {
	
	//Init vars
	global $prso_synd_toolkit_master_options;
	
	//Set plugin config
	$config_options = array(
		'post_options' => array(
			'post_type' 		=> 'prso_synd_toolkit',
			'push_on_publish'	=> TRUE,
		)
	);
	
	//Cache plugin options array
	$prso_synd_toolkit_master_options = get_option( PRSOSYNDTOOLKIT__OPTIONS_NAME );
	
	//Cache post type for syndcation
	if( isset($prso_synd_toolkit_master_options['post-type-select']) && !empty($prso_synd_toolkit_master_options['post-type-select']) ) {
		$config_options['post_options']['post_type'] = $prso_synd_toolkit_master_options['post-type-select'];
	}
	
	//Instatiate plugin class and pass config options array
	new PrsoSyndToolkit( $config_options );
		
}

/* Display a notice that can be dismissed */
add_action('admin_notices', 'pcsn_pro_admin_notice');
function pcsn_pro_admin_notice() {
	global $current_user ;
    $user_id = $current_user->ID;
    
    /* Check that the user hasn't already clicked to ignore the message */
	if ( ! get_user_meta($user_id, 'pcsn_pro_ignore_notice') ) {
        echo '<div class="updated"><p>'; 
        printf(__('<strong>New</strong>: Create your own Content Syndication Network</strong>. Setup subscriptions and have clients pay for them, sell subscriptions directly from your website.  <a href="%1$s" target="_blank">Learn More</a> | <a href="%2$s">Hide Notice</a>'), 'http://benjaminmoody.com/downloads/content-syndication-toolkit-pro/?bm_plugin_notice', home_url('/wp-admin/index.php').'?pcsn_pro_ignore_notice=0');
        echo "</p></div>";
	}
}

add_action('admin_init', 'pcsn_pro_nag_ignore');
function pcsn_pro_nag_ignore() {
	global $current_user;
	
    $user_id = $current_user->ID;
    /* If user clicks to ignore the notice, add that to their user meta */
    if ( isset($_GET['pcsn_pro_ignore_notice']) && '0' == $_GET['pcsn_pro_ignore_notice'] ) {
         add_user_meta($user_id, 'pcsn_pro_ignore_notice', 'true', true);
	}
}