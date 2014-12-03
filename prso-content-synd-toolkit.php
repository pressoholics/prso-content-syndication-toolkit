<?php
/*
 * Plugin Name: Content Syndication Toolkit
 * Plugin URI: 
 * Description: Content Syndication Toolkit allows you to syndicate content to multiple client sites automatically. Posts, Categories, Tags, and Images.
 * Author: Benjamin Moody
 * Version: 1.0
 * Author URI: http://www.benjaminmoody.com
 * License: GPL2+
 * Text Domain: prso_synd_toolkit_plugin
 * Domain Path: /languages/
 */

//Define plugin constants
define( 'PRSOSYNDTOOLKIT__MINIMUM_WP_VERSION', '3.0' );
define( 'PRSOSYNDTOOLKIT__VERSION', '1.0' );
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

//Set plugin config
$config_options = array(
	'post_options' => array(
		'post_type' => 'prso_synd_toolkit'
	)
);

//Instatiate plugin class and pass config options array
new PrsoSyndToolkit( $config_options );