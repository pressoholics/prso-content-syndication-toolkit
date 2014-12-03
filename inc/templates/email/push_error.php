<?php
/**
* Template: email -> push_error
* 
* @Called by: PrsoSyndSetup::push_content_to_clients
*
* Contains plain text content for email sent when an error is detecting making a client push request
* 
* @author	Ben Moody
*/
?>

<?php _ex( 'This is a message from your Content Syndication Toolkit wordpress plugin.', 'text', PRSOSYNDTOOLKIT__DOMAIN ); echo "\r\n"; ?>
		
<?php _ex( 'There was an error when trying to send a push notification to your clients. Check the error log in wp-content folder for more info.', 'text', PRSOSYNDTOOLKIT__DOMAIN ); echo "\r\n"; ?>

<?php _ex( 'Here is the error message:', 'text', PRSOSYNDTOOLKIT__DOMAIN ); echo "\r\n"; ?>

"<?php esc_attr_e( $error_msg ); ?>"