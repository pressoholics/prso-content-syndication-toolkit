<?php
/**
* Class PrsoSyndSetup
* 
* Handles all actions required to setup and init the Content Syndication Toolkit Master plugin
* 
* @author	Ben Moody
*/
class PrsoSyndSetup {
	
	private $class_config = array();
	
	function __construct( $config = array() ) {
		
		//Cache plugin config options
		$this->class_config = $config;
		
		//Register custom post types
		$this->register_post_types();
		
		//On post save for 'prso_synd_toolkit' posts, add meta data 'prso_synd_toolkit_ID'
		add_action( 'post_updated', array($this, 'on_save_add_post_meta') );
		
	}
	
	/**
	* register_post_types
	* 
	* If user wishes sydication posts to be stored in unqiue post type,
	* setup the post type here.
	* 
	* @access 	private
	* @author	Ben Moody
	*/
	private function register_post_types() {
		
		//Register prso_synd_toolkit post type and taxonomies
		$this->register_prso_synd_toolkit();
		
		//Prevent public access to the prso_synd_toolkit post type
		//add_action( 'template_redirect', array($this, 'no_public_access_prso_synd_toolkit_posts') );
		
	}
	
	/**
	* register_prso_synd_toolkit
	* 
	* Register 'prso_synd_toolkit' post type and 'prso_synd_group' taxonomy
	* 
	* @access 	private
	* @author	Ben Moody
	*/
	private function register_prso_synd_toolkit() {
		
		//Register post type prso_synd_toolkit
		$labels = array(
			'name'                => _x( 'Syndication Posts', 'Post Type General Name', PRSOSYNDTOOLKIT__DOMAIN ),
			'singular_name'       => _x( 'Syndication Post', 'Post Type Singular Name', PRSOSYNDTOOLKIT__DOMAIN ),
			'menu_name'           => __( 'Syndication Posts', PRSOSYNDTOOLKIT__DOMAIN ),
			'parent_item_colon'   => __( 'Parent Item:', PRSOSYNDTOOLKIT__DOMAIN ),
			'all_items'           => __( 'All Items', PRSOSYNDTOOLKIT__DOMAIN ),
			'view_item'           => __( 'View Item', PRSOSYNDTOOLKIT__DOMAIN ),
			'add_new_item'        => __( 'Add New Item', PRSOSYNDTOOLKIT__DOMAIN ),
			'add_new'             => __( 'Add New', PRSOSYNDTOOLKIT__DOMAIN ),
			'edit_item'           => __( 'Edit Item', PRSOSYNDTOOLKIT__DOMAIN ),
			'update_item'         => __( 'Update Item', PRSOSYNDTOOLKIT__DOMAIN ),
			'search_items'        => __( 'Search Item', PRSOSYNDTOOLKIT__DOMAIN ),
			'not_found'           => __( 'Not found', PRSOSYNDTOOLKIT__DOMAIN ),
			'not_found_in_trash'  => __( 'Not found in Trash', PRSOSYNDTOOLKIT__DOMAIN ),
		);
		$rewrite = array(
			'slug'                => 'syndication-post',
			'with_front'          => true,
			'pages'               => true,
			'feeds'               => false,
		);
		$args = array(
			'label'               => __( 'prso_synd_toolkit', PRSOSYNDTOOLKIT__DOMAIN ),
			'description'         => __( 'All posts to be syndicated to your subscribers', PRSOSYNDTOOLKIT__DOMAIN ),
			'labels'              => $labels,
			'supports'            => array( 'title', 'editor', 'excerpt', 'author', 'thumbnail', 'revisions', ),
			'taxonomies'          => array( 'category', 'post_tag' ),
			'hierarchical'        => false,
			'public'              => true,
			'show_ui'             => true,
			'show_in_menu'        => true,
			'show_in_nav_menus'   => true,
			'show_in_admin_bar'   => true,
			'menu_position'       => 5,
			'can_export'          => true,
			'has_archive'         => true,
			'exclude_from_search' => true,
			'publicly_queryable'  => true,
			'rewrite'             => $rewrite,
			'capability_type'     => 'post',
		);
		register_post_type( 'prso_synd_toolkit', $args );
		
	}
	
	/**
	* no_public_access_prso_synd_toolkit_posts
	* 
	* @Called By Filter: 'template_redirect'
	*
	* Blocks access to 'prso_synd_toolkit' post types on the front end for everyone except users who can edit posts
	* 
	* @access 	public
	* @author	Ben Moody
	*/
	public function no_public_access_prso_synd_toolkit_posts() {
		
		if ( is_user_logged_in() && current_user_can( 'edit_posts' ) )
        	return;
		
		//Redirect public users to home page for all 'prso_synd_toolkit' post type views
		if( is_singular('prso_synd_toolkit') || is_archive('prso_synd_toolkit') ) {
			wp_redirect( get_home_url() );
			exit();
		}
		
		//Redirect public users to home page for all 'prso_synd_group' taxonomy views
		if( is_tax('prso_synd_group') ) {
			wp_redirect( get_home_url() );
			exit();
		}
		
	}
	
	/**
	* on_save_add_post_meta
	* 
	* @Called By Action: 'save_post'
	*
	* Adds custom post meta to post types 'post' and 'prso_synd_toolkit'
	* 
	* @param	int		$post_id
	* @access 	public
	* @author	Ben Moody
	*/
	public function on_save_add_post_meta( $post_id ) {
		
		//Init vars
		$post = NULL;
		
		// If this is just a revision, don't send the email.
		if ( wp_is_post_revision( $post_id ) )
			return;
		
		// Stop WP from clearing custom fields on autosave
	    if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE)
	        return;
	
	    // Prevent quick edit from clearing custom fields
	    if (defined('DOING_AJAX') && DOING_AJAX)
	        return;
	    
	    //Get post type
		$post = get_post($post_id);;
	    
	    //Check if post or 'prso_synd_toolkit' post type
	    if( ($post->post_type == $this->class_config['post_options']['post_type']) && ($post->post_status == 'publish') ) {
	    
		    //Add custom post meta 'prso_synd_toolkit_ID', populate with master post ID
			add_post_meta( $post_id, 'prso_synd_toolkit_ID', $post_id );
			
			//Push content to all registered accounts
			$this->push_content_to_clients();
			
	    }
		
	}
	
	/**
	* push_content_to_clients
	* 
	* Makes a pull request to the pull webhook, parses the server responce and returns the
	* approriate user message to the ajax request
	*
	* @access 	public
	* @author	Ben Moody
	*/
	private function push_content_to_clients() {
		
		//Init vars
		$subscribers	= array();
		$http_request 	= NULL;
		$request_url	= NULL;
		$response		= NULL;
		$output			= NULL;
		$error_msg		= NULL;
		
		//First get all client user account website urls
		$subscribers = get_users( 
			array(
				'role' => PRSOSYNDTOOLKIT__USER_ROLE
			)	
		);
		
		//Loop all subscribers and make a push request to user_url
		if( !empty($subscribers) ){
			
			set_time_limit(0);
			add_filter( 'http_request_timeout', array( $this, 'bump_request_timeout' ) );
			
			foreach( $subscribers as $subscriber ) {
				
				if( isset($subscriber->data->user_url) && !empty($subscriber->data->user_url) ) {
					
					//Form request url with params
					$request_url = add_query_arg( PRSOSYNDTOOLKITREADER__WEBHOOK_PARAM, 'true', $subscriber->data->user_url );
					
					//Make a push request to client server
					$response = wp_remote_request( $request_url, array('timeout' => 1000) );
					
					//Check response code
					if( isset($response['response']['code']) ) {
						
						if( (int) $response['response']['code'] !== 200 ) {
							
							$error_msg = sprintf( __( 'Problem contacting client: %1$s. Server response code: %2$s', PRSOSYNDTOOLKIT__DOMAIN ), $subscriber->data->user_login, $response['response']['code'] );
							
						}
						
					} else {
						
						$error_msg = sprintf( __( 'Problem contacting client: %1$s.', PRSOSYNDTOOLKIT__DOMAIN ), $subscriber->data->user_login );
						
					}
					
				}

				if( !empty($error_msg) ) {
					
					//Send email to client admin and prompt them to perform a manual pull request
					PrsoSyndToolkit::send_admin_email( $error_msg, 'push_error_client_alert', $subscriber->data->user_email );
					
				}
				
			}
		}
		
		if( !empty($error_msg) ) {
		
			//Send email to Master admin
			PrsoSyndToolkit::send_admin_email(
				_x( 'There was a problem contacting at least one of your client servers. Probably a server timeout or the site is down.', 'text', PRSOSYNDTOOLKIT__DOMAIN )
			);
			
		}
		
	}
	
	/**
	 * Added to http_request_timeout filter to force timeout at 60 seconds during import
	 * @return int 180
	 */
	public function bump_request_timeout( $val ) {
		return 1000;
	}
	
}
?>