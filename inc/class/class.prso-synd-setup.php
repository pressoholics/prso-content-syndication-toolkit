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
		
		//On export add any custom post meta to the export xml
		add_action( 'pcst_export_post_meta_xml', array($this, 'export_action_post_meta_xml') );
		
		//Add ajax action for async push on post save 
		add_action( 'wp_ajax_nopriv_pcst-push-content-on-save', array($this, 'push_content_to_clients_on_save'), 10 );
		
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
		
		//Register prso_synd_toolkit post type
		$this->register_prso_synd_toolkit();
		
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
	* export_action_post_meta_xml
	* 
	* @Called By Action: 'pcst_export_post_meta_xml'
	*
	* Called right at the end of the metadata export xml for a post
	* Allows us to inject in custom post meta before exporting to client
	* 
	* @param	obj		$post
	* @access 	public
	* @author	Ben Moody
	*/
	public function export_action_post_meta_xml( $post ) {
		
		//Init vars
		global $pcst_canonical_post_obj;
		$pcst_canonical_post_obj = $post;
		$output = NULL;
		
		//Add meta xml for posts original url (master sever url) for canonical links
		ob_start();
		?>
		<wp:postmeta>
			<wp:meta_key>pcst_canonical_permalink</wp:meta_key>
			<wp:meta_value><?php echo $this->wxr_cdata( get_page_link( $post->ID  )); ?></wp:meta_value>
		</wp:postmeta>
		<?php
		$output.= apply_filters( 'pcst_export_post_meta_xml__canonical', ob_get_contents(), $post );
		ob_end_clean();
		
		echo apply_filters( 'pcst_export_post_meta_xml_filter', $output, $post );
		
	}
	
	/**
	 * Wrap given string in XML CDATA tag.
	 *
	 * @since 2.1.0
	 *
	 * @param string $str String to wrap in XML CDATA tag.
	 * @return string
	 */
	private function wxr_cdata( $str ) {
		if ( seems_utf8( $str ) == false )
			$str = utf8_encode( $str );
	
		// $str = ent2ncr(esc_html($str));
		$str = '<![CDATA[' . str_replace( ']]>', ']]]]><![CDATA[>', $str ) . ']]>';
	
		return $str;
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
			update_post_meta( $post_id, 'prso_synd_toolkit_ID', $post_id );
			
			//Push content to all registered accounts
			if( $this->class_config['post_options']['push_on_publish'] ) {
			
				//PrsoSyndSetup::push_content_to_clients();
				
				//Make an async call to push content to clients
				$this->background_init( 'pcst-push-content-on-save' );
				
			}
			
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
	public static function push_content_to_clients( $subscribers = array(), $is_ajax = FALSE ) {
		
		//Init vars
		$http_request 	= NULL;
		$request_url	= NULL;
		$response		= NULL;
		$output			= NULL;
		$error_msg		= NULL;
		
		//First get all client user account website urls
		if( empty($subscribers) ) {
			$subscribers = get_users( 
				array(
					'role' => PRSOSYNDTOOLKIT__USER_ROLE
				)	
			);
		}
		
		
		//Loop all subscribers and make a push request to user_url
		if( !empty($subscribers) ){
			
			set_time_limit(0);
			add_filter( 'http_request_timeout', array( 'PrsoSyndSetup', 'bump_request_timeout' ) );
			
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
					
					if( !$is_ajax ) {
						//Send email to client admin and prompt them to perform a manual pull request
						PrsoSyndToolkit::send_admin_email( $error_msg, 'push_error_client_alert', $subscriber->data->user_email );
					} else {
						wp_send_json_error( _x( $error_msg, 'text', PRSOSYNDTOOLKIT__DOMAIN ) );
					}
					
				}
				
			}
		}
		
		if( !empty($error_msg) ) {
			
			if( !$is_ajax ) {
				//Send email to Master admin
				PrsoSyndToolkit::send_admin_email(
					_x( 'There was a problem contacting at least one of your client servers. Probably a server timeout or the site is down.', 'text', PRSOSYNDTOOLKIT__DOMAIN )
				);
			}
			
		} elseif( empty($error_msg) && $is_ajax ) {
			
			wp_send_json_success( _x( 'Post Push Completed', 'text', PRSOSYNDTOOLKIT__DOMAIN ) );
			
		}
		
	}
	
	/**
	 * Added to http_request_timeout filter to force timeout at 60 seconds during import
	 * @return int 180
	 */
	public static function bump_request_timeout( $val ) {
		return 1000;
	}
	
	/**
	* push_content_to_clients
	* 
	* @Ajax Call 'wp_ajax_pcst-push-content-on-save'
	* 
	* 
	*
	* @access 	public
	* @author	Ben Moody
	*/
	public function push_content_to_clients_on_save() {
		
		//Init vars
		$subscribers	= array();
		$http_request 	= NULL;
		$request_url	= NULL;
		$response		= NULL;
		$output			= NULL;
		$error_msg		= NULL;
		
		//Push content to subscribers
		PrsoSyndSetup::push_content_to_clients( array(), FALSE );
		
		die( 'push complete' );
		
	}
	
	/**
	* background_init
	* 
	* Called By Action: prso_gform_pluploader_processed_uploads
	* 
	* Hooks into custom action for Gravity Forms Advanced Uploader plugin.
	* Once all attachments have been processed by the plugin and added to wordpress
	* media library. 
	*
	* The array of attachments, Gravity Forms Entry & Form data are prepared to be sent
	* to the next function via a CURL request.
	*
	* Why CURL? - To make sure the upload process to the video hosting service is
	* asyncronous, thus the user will not have to sit and wait for the file to be uploaded
	* before getting some feedback from the form. 
	*
	* Note that the curl request works like a wordpress Ajax request see the 'action' element
	* of $fields array.
	*
	* @param	Array	$wp_attachment_data
	* @param	Array	$entry
	* @param	Array	$form
	* @access 	public
	* @author	Ben Moody
	*/
	public function background_init( $action = NULL ) {
		
		//Init vars
		$shell_exec_path 	= '';
		$command			= '';
		$ajax_hook_slug		= '';		
		$plugin_options		= array();
		
		//** Set Post Vars **//
		
		//Set wp ajax action slug
		$fields['action'] = $action;
		
		//Set nonce
		$fields['ajaxNonce'] = wp_create_nonce( 'pcst-admin-ajax' );
		
		//** Init curl request - note this is asynchronous **//
		$this->init_curl( $fields );
		
	}
	
	/**
	* init_curl
	* 
	* Helper to make a curl request
	* 
	* @param	Array	$post_fields
	* @access 	public
	* @author	Ben Moody
	*/
	private function init_curl( $post_fields ) {
		
		//** Init curl request - note this is asynchronous **//
		$ch = curl_init();
		
		//Cache path to wp ajax script
		$wp_ajax_url = admin_url('admin-ajax.php');
		
		curl_setopt($ch, CURLOPT_URL, $wp_ajax_url);
		curl_setopt($ch, CURLOPT_FRESH_CONNECT, true);
		curl_setopt($ch, CURLOPT_TIMEOUT, 1);
		curl_setopt($ch, CURLOPT_POSTFIELDS, $post_fields);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

		$result = curl_exec($ch);
		
		curl_close($ch);
		
	}
	
	
	
}
?>