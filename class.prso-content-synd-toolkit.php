<?php
class PrsoSyndToolkit {
	
	protected static $class_config 				= array();
	protected $current_screen					= NULL;
	protected $plugin_ajax_nonce				= 'prso_synd_toolkit-ajax-nonce';
	protected $plugin_path						= PRSOSYNDTOOLKIT__PLUGIN_DIR;
	protected $plugin_url						= PRSOSYNDTOOLKIT__PLUGIN_URL;
	protected $plugin_textdomain				= PRSOSYNDTOOLKIT__DOMAIN;
	
	function __construct( $config = array() ) {
		
		//Cache plugin congif options
		self::$class_config = $config;
		
		//Set textdomain
		add_action( 'after_setup_theme', array($this, 'plugin_textdomain') );
		
		//Init plugin
		add_action( 'init', array($this, 'init_plugin') );
		//add_action( 'admin_init', array($this, 'admin_init_plugin') );
		//add_action( 'current_screen', array($this, 'current_screen_init_plugin') );
		
	}
	
	/**
	 * Attached to activate_{ plugin_basename( __FILES__ ) } by register_activation_hook()
	 * @static
	 */
	public static function plugin_activation( $network_wide ) {
		
		//Add pcst subscriber user role with no permissions
		add_role( PRSOSYNDTOOLKIT__USER_ROLE,
			_x( 'Syndication Sub', 'text', PRSOSYNDTOOLKIT__DOMAIN ),
			array()
		);
		
	}

	/**
	 * Attached to deactivate_{ plugin_basename( __FILES__ ) } by register_deactivation_hook()
	 * @static
	 */
	public static function plugin_deactivation( ) {
		
		//Remove pcst subscriber user role
		remove_role( PRSOSYNDTOOLKIT__USER_ROLE );
		
	}
	
	/**
	 * Setup plugin textdomain folder
	 * @public
	 */
	public function plugin_textdomain() {
		
		load_plugin_textdomain( $this->plugin_textdomain, FALSE, $this->plugin_path . '/languages/' );
		
	}
	
	/**
	* init_plugin
	* 
	* Used By Action: 'init'
	* 
	*
	* @access 	public
	* @author	Ben Moody
	*/
	public function init_plugin() {
		
		//Init vars
		$options 			= self::$class_config;
		
		if( is_admin() ) {
		
			//PLUGIN OPTIONS FRAMEWORK -- comment out if you dont need options
			//$this->load_redux_options_framework();
			
		}
		
		//Setup and init the Content Syndication Toolkit Master plugin
		$plugin_setup_inc 	= $this->plugin_path . 'inc/class/class.prso-synd-setup.php';
		if( file_exists($plugin_setup_inc) ) {
			require_once( $plugin_setup_inc );
			new PrsoSyndSetup( $options );
		}
		
		//Init the Master API for exporting posts to client api
		$get_post_api_inc 	= $this->plugin_path . 'inc/class/class.prso-synd-get-posts-api.php';
		if( file_exists($get_post_api_inc) ) {
			require_once( $get_post_api_inc );
			new PrsoSyndGetPostsApi( $options );
		}
		
	}
	
	/**
	* admin_init_plugin
	* 
	* Used By Action: 'admin_init'
	* 
	*
	* @access 	public
	* @author	Ben Moody
	*/
	public function admin_init_plugin() {
		
		//Init vars
		$options 		= self::$class_config;
		
		if( is_admin() ) {
			
			//Enqueue admin scripts
			add_action( 'admin_enqueue_scripts', array($this, 'enqueue_admin_scripts') );
			
		}
		
	}
	
	/**
	* current_screen_init_plugin
	* 
	* Used By Action: 'current_screen'
	* 
	* Detects current view and decides if plugin should be activated
	*
	* @access 	public
	* @author	Ben Moody
	*/
	public function current_screen_init_plugin() {
		
		//Init vars
		$options 		= self::$class_config;
		
		if( is_admin() ) {
		
			//Confirm we are on an active admin view
			if( $this->is_active_view() ) {
		
				//Carry out view specific actions here
				
			}
			
		}
		
	}
	
	/**
	* load_redux_options_framework
	* 
	* Loads Redux options framework as well as the unique config file for this plugin
	*
	* NOTE!!!!
	*			You WILL need to make sure some unique constants as well as the class
	*			name in the plugin config file 'inc/ReduxConfig/ReduxConfig.php'
	*
	* @access 	public
	* @author	Ben Moody
	*/
	protected function load_redux_options_framework() {
		
		//Init vars
		$framework_inc 		= $this->plugin_path . 'inc/ReduxFramework/ReduxCore/framework.php';
		$framework_config	= $this->plugin_path . 'inc/ReduxConfig/ReduxConfig.php';
		
		//Try and load redux framework
		if ( !class_exists('ReduxFramework') && file_exists($framework_inc) ) {
			require_once( $framework_inc );
		}
		
		//Try and load redux config for this plugin
		if ( file_exists($framework_config) ) {
			require_once( $framework_config );
		}
		
	}
	
	/**
	* is_active_view
	* 
	* Detects if current admin view has been set as 'active_post_type' in
	* plugin config options array.
	* 
	* @var		array	self::$class_config
	* @var		array	$active_views
	* @var		obj		$screen
	* @var		string	$current_screen
	* @return	bool	
	* @access 	protected
	* @author	Ben Moody
	*/
	protected function is_active_view() {
		
		//Init vars
		$options 		= self::$class_config;
		$active_views	= array();
		$screen			= get_current_screen();
		$current_screen	= NULL;
		
		//Cache all views plugin will be active on
		$active_views = $this->get_active_views( $options );
		
		//Cache the current view
		if( isset($screen) ) {
		
			//Is this an attachment screen (base:upload or post_type:attachment)
			if( ($screen->id === 'attachment') || ($screen->id === 'upload') ) {
				$current_screen = 'attachment';
			} else {
				
				//Cache post type for all others
				$current_screen = $screen->post_type;
				
			}
			
			//Cache current screen in class protected var
			$this->current_screen = $current_screen;
		}
		
		//Finaly lets check if current view is an active view for plugin
		if( in_array($current_screen, $active_views) ) {
			return TRUE;
		} else {
			return FALSE;
		}
		
	}
	
	/**
	* get_active_views
	* 
	* Interates over plugin config options array merging all
	* 'active_post_type' values into single array
	* 
	* @param	array	$options
	* @var		array	$active_views
	* @return	array	$active_views
	* @access 	private
	* @author	Ben Moody
	*/
	protected function get_active_views( $options = array() ) {
		
		//Init vars
		$active_views = array();
		
		//Loop options and cache each active post view
		foreach( $options as $option ) {
			if( isset($option['active_post_types']) ) {
				$active_views = array_merge($active_views, $option['active_post_types']);
			}
		}
		
		return $active_views;
	}
	
	/**
	 * Helper to set all actions for plugin
	 */
	protected function set_admin_actions() {
		
		
		
	}
	
	/**
	 * Helper to enqueue all scripts/styles for admin views
	 */
	public function enqueue_admin_scripts() {
		
		//Init vars
		$js_inc_path 	= $this->plugin_url . 'inc/js/';
		$css_inc_path 	= $this->plugin_url . 'inc/css/';
		
		
		
		//Localize vars
		$this->localize_script();
		
	}
	
	/**
	* localize_script
	* 
	* Helper to localize all vars required for plugin JS.
	* 
	* @var		string	$object
	* @var		array	$js_vars
	* @access 	private
	* @author	Ben Moody
	*/
	protected function localize_script() {
		
		//Init vars
		$object 	= 'PrsoPluginFrameworkVars';
		$js_vars	= array();
		
		//Localize vars for ajax requests
		
		
		//wp_localize_script( '', $object, $js_vars );
	}
	
	public static function plugin_error_log( $var ) {
		
		ini_set( 'log_errors', 1 );
		ini_set( 'error_log', PRSOSYNDTOOLKIT__PLUGIN_DIR . '/debug.log' );
		
		if( !is_string($var) ) {
			error_log( print_r($var, true) );
		} else {
			error_log( $var );
		}
		
	}
	
	/**
	* send_admin_email
	* 
	* Sends an error warning email to the wordpress admin
	* 
	* @access 	private
	* @author	Ben Moody
	*/
	public static function send_admin_email( $error_msg, $error_type = 'push_error', $admin_email = NULL ) {
		
		//Init vars
		$inc_templates = PRSOSYNDTOOLKIT__PLUGIN_DIR . "inc/templates/email/{$error_type}.php";
		
		$subject = NULL;
		$headers = array();
		$message = NULL;
		
		if( file_exists($inc_templates) ) {
		
			//send admin an email to let them know
			if( empty($admin_email) ) {
				$admin_email = get_option( 'admin_email' );
			}
			
			//Set email content
			$subject = _x( 'WP Content Syndication Toolkit', 'text', PRSOSYNDTOOLKIT__DOMAIN );
			
			ob_start();
				include_once( $inc_templates );
			$message = ob_get_contents();
			ob_end_clean();
			
			//Send Email to admin
			wp_mail( $admin_email, $subject, $message );
			
			//Error log
			PrsoSyndToolkit::plugin_error_log( $message );
			
			return;
		}
		
	}
	
}



