<?php
/**
* Class PrsoSyndGetPostsApi
* 
* Handles all actions required to export posts (inc cats, tags, and attachments) requested by client api call
* 
* @author	Ben Moody
*/
class PrsoSyndGetPostsApi {
	
	private $class_config = array();
	public static $current_client = NULL;
	
	function __construct( $config = array() ) {
		
		//Cache plugin config options
		$this->class_config = $config;
		
		//Filter wordpress XMLRPC Methods adding our custom methods
		add_filter( 'xmlrpc_methods', array($this, 'add_xmlrpc_methods') );
		
		//Require the post export function
		if( !function_exists('prso_synd_toolkit_export_wp') ) {
			require_once( PRSOSYNDTOOLKIT__PLUGIN_DIR . 'inc/export.php' );
		}
		
	}
	
	/**
	* add_xmlrpc_methods
	* 
	* @Called By Filter: 'xmlrpc_methods'
	*
	* Adds any custom methods to the wordpress XMLRPC API for callback by Content Syndication Toolkit Reader Plugin
	* 
	* @param	array	$methods
	* @return	array	$methods
	* @access 	public
	* @author	Ben Moody
	*/
	public function add_xmlrpc_methods( $methods ) {
		
		//Add method for our action to get all prso_synd_toolkit post data
		$methods['pcst.getSyndicationPosts'] = array( $this, 'get_syndication_post_data' );
		
		return $methods;
	}
	
	/**
	* get_syndication_post_data
	* 
	* @Called By XMLRPC Method 'pcst.getSyndicationPosts'
	*
	* Performs the query to get 'prso_synd_toolkit' and return it when an XMLRPC request is made
	* to the 'pcst.getSyndicationPosts' XMLRPC method from the Content Syndication Toolkit Reader Plugin
	* 
	* @param	array	$args
	* @var		type	name
	* @return	type	name
	* @access 	public
	* @author	Ben Moody
	*/
	public function get_syndication_post_data( $args ) {
		
		//Init vars
		global $wp_xmlrpc_server;
		$results 	= NULL; //Results of query
		$output		= NULL; //Output to return via api
		
		$wp_xmlrpc_server->escape( $args );
				
		if ( ! $user = $wp_xmlrpc_server->login( $args[1], $args[2] ) ) {
        	//return $wp_xmlrpc_server->error;
        	header('HTTP/1.0 403 Forbidden');
			die();
		}
		
		//Confirm user is of the correct role
		if( !in_array(PRSOSYNDTOOLKIT__USER_ROLE, $user->roles)  ) {
			header('HTTP/1.0 403 Forbidden');
			die();
		}
		
		//Cache args for current user in static var
		self::$current_client = $user;
		
		do_action( 'pcst_client_request_authenticated', $user );
		
		//Require the post export function
		if( !function_exists('prso_synd_toolkit_export_wp') ) {
			require_once( PRSOSYNDTOOLKIT__PLUGIN_DIR . 'inc/export.php' );
		}
		
		//First export all prso_synd_toolkit posts since the last client import
		do_action( 'pcst_before_post_export' );
		$output['posts'] = $this->get_prso_synd_toolkit_posts( $args );
		do_action( 'pcst_after_post_export' );
		
		
		//Next export ALL attachments, client import will filter out duplicates
		do_action( 'pcst_before_attachment_export' );
		$output['attachments'] = $this->get_attachments( $args );
		do_action( 'pcst_after_attachment_export' );
		
		//Also send wordpress default image sizes, need to be sure we can sync these when importing at clients end
		$output['image_sizes'] = $this->get_image_sizes();
		
		if( defined('WP_DEBUG') && WP_DEBUG === TRUE ) {
			PrsoSyndToolkit::plugin_error_log( $output );
		}

		
		return $output;
	}
	
	/**
	* get_image_sizes
	* 
	* @Called By $this->get_syndication_post_data()
	*
	* Helper to get all intermediate image sizes registered in this intance of wordpress
	* 
	* @param	string	$size
	* @return	array	$sizes
	* @access 	private
	* @author	Ben Moody
	*/
	private function get_image_sizes( $size = '' ) {

        global $_wp_additional_image_sizes;

        $sizes = array();
        $get_intermediate_image_sizes = get_intermediate_image_sizes();

        // Create the full array with sizes and crop info
        foreach( $get_intermediate_image_sizes as $_size ) {

                if ( in_array( $_size, array( 'thumbnail', 'medium', 'large' ) ) ) {

                        $sizes[ $_size ]['width'] = get_option( $_size . '_size_w' );
                        $sizes[ $_size ]['height'] = get_option( $_size . '_size_h' );
                        $sizes[ $_size ]['crop'] = (bool) get_option( $_size . '_crop' );

                } elseif ( isset( $_wp_additional_image_sizes[ $_size ] ) ) {

                        $sizes[ $_size ] = array( 
                                'width' => $_wp_additional_image_sizes[ $_size ]['width'],
                                'height' => $_wp_additional_image_sizes[ $_size ]['height'],
                                'crop' =>  $_wp_additional_image_sizes[ $_size ]['crop']
                        );

                }

        }

        // Get only 1 size if found
        if ( $size ) {

                if( isset( $sizes[ $size ] ) ) {
                        return $sizes[ $size ];
                } else {
                        return false;
                }

        }

        return $sizes;
	}
	
	/**
	* get_prso_synd_toolkit_posts
	*
	* Helper to make the export call for all 'prso_synd_toolkit' posts in xml format
	* 
	* @param	array	$args
	* @return	string	$output
	* @access 	private
	* @author	Ben Moody
	*/
	private function get_prso_synd_toolkit_posts( $args ) {
		
		//INit vars
		$start_date	= NULL; //Date of last client post
		$output		= NULL;
		$post_type	= $this->class_config['post_options']['post_type'];
		
		if( isset($args[0]['last_date']) ) {
			$start_date =  esc_attr($args[0]['last_date']);
		}
		
		//Setup args to export posts AFTER the start date provided from client api
		$export_args = array( 'content' => $post_type, 'start_date' => $start_date, 'status' => 'publish' );
		
		//Get export xml
		ob_start();
			prso_synd_toolkit_export_wp( $export_args );
		$output = ob_get_contents();
		ob_end_clean();
		
		return $output;
	}
	
	/**
	* get_attachments
	*
	* Helper to make the export call for all 'attachment' posts in xml format
	* 
	* @param	array	$args
	* @return	string	$output
	* @access 	private
	* @author	Ben Moody
	*/
	private function get_attachments( $args ) {
		
		//INit vars
		global $pcst_exported_posts;
		$export_image_ids	= array();
		$output				= NULL;
		$post_type			= $this->class_config['post_options']['post_type'];
		
		//Loop all exported posts
		if( isset($pcst_exported_posts[ $post_type ]) ) {
			foreach( $pcst_exported_posts[ $post_type ] as $key => $post_data ) {
				
				//Init vars
				$post_id			= NULL;
				$post_content		= NULL;
				$_attached_images 	= NULL;
				$_post_thumbnail_id	= NULL;
				$_content_images	= NULL;
				
				if( isset($post_data['ID'], $post_data['post_content']) ) {
					$post_id = $post_data['ID'];
					$post_content = $post_data['post_content'];
				}
				
				//Get all attached images for this post
				$_attached_images = get_attached_media( 'image', $post_id );
				
				//Get post thumbnail ID
				$_post_thumbnail_id = get_post_thumbnail_id( $post_id );
				
				// Get all images from post's content
				$_content_images = $this->get_content_image_ids( $post_content );
				
				//Cache this post's attachments into our export array
				if( is_array($_attached_images) ) {
					foreach( $_attached_images as $image_id => $Image ) {
						$export_image_ids[] = $image_id;
					}
				}
				
				if( is_array($_content_images) ) {
					foreach( $_content_images as $key => $image_id ) {
						$export_image_ids[] = $image_id;
					}
				}
				
				if( !empty($_post_thumbnail_id) ) {
					$export_image_ids[] = $_post_thumbnail_id;
				}
				
			}
		}
		
		//Remove any duplicates from image export array
		$export_image_ids = array_unique( $export_image_ids );
		
		//Setup args to export only the attachments associated with the posts being exported
		$export_args = array( 'content' => 'attachment', 'post_ids' => $export_image_ids );
		
		//Get export xml
		ob_start();
			prso_synd_toolkit_export_wp( $export_args );
		$output = ob_get_contents();
		ob_end_clean();
		
		return $output;
	}
	
	/**
	* get_content_image_ids
	*
	* Helper to parse post content and return an array of image attachment id's
	* found in the content img tags.
	*
	* Makes use of img css class wp-image-xxx, if this is filtered out from img tag html
	* then this function will not work.
	* 
	* @param	string	$post_content
	* @return	array	$image_ids
	* @access 	private
	* @author	Ben Moody
	*/
	private function get_content_image_ids( $post_content ) {
		
		//Init vars
		$images 	= NULL;
		$image_ids	= array();
		
		// Get all images from post's content
		preg_match_all('/<\s*img [^\>]*src\s*=\s*[\""\']?([^\""\'>]*)/i', $post_content, $images);
		
		//Loop images and cache the image ID from img wp-image-xxx css class
		if( is_array($images[0]) ) {
			$images = $images[0];
			foreach($images as $key => $image) {
				
				$thumb_id = NULL;
				
				//Get ID for any images ebedded in the post content
				preg_match('/wp-image-([\d]*)/i', $image, $thumb_id);
				
				if( isset($thumb_id[1]) ) {
					$image_ids[] = $thumb_id[1];
				}
				
			}
		}
		
		return $image_ids;
	}
	
}
?>