<?php
/*
Plugin Name: Featured Image Resize
Plugin URI: http://wordpress.org/plugins/featured-image-resize/
Description: This plugin will regenerate all missing image sizes for an image when it's chosen as a featured image for any post. Very useful when you change your template. No settings whatsoever, just activate and forget.
Author: Louy Alakkad
Version: 0.2
Author URI: http://l0uy.com/
*/

add_action('wp_ajax_set-post-thumbnail','featured_image_resize_if_missing',1);
function featured_image_resize_if_missing() {
	$thumbnail_id = $_REQUEST['thumbnail_id'];
	if( !is_numeric($thumbnail_id) ) return false;
	
	$sizes = get_intermediate_image_sizes();
	
	$imagedata = wp_get_attachment_metadata( $thumbnail_id );
	
	if ( !is_array( $imagedata ) ) {
		// image has no metadata, check for existence
		
		global $wpdb;
		$post_exists = $wpdb->get_row("SELECT * FROM $wpdb->posts WHERE id = '" . $thumbnail_id . "'", 'ARRAY_A');
		if( !$post_exists ) return false;
		
	} else {
		// check if all sizes exist
		
		$missing = array();
		
		foreach( $sizes as $size ) {
			if ( !array_key_exists($size, $imagedata['sizes']) ) {
				// TODO: check if original image is smaller than size
				$missing[] = $size;
			}
		}
		
		if( count($missing) == 0 )
			return false;
	}
	
	// regenerate thumbnails
	$upload_dir = wp_upload_dir();
	$image_path = str_replace( $upload_dir['baseurl'], $upload_dir['basedir'], wp_get_attachment_url( $thumbnail_id ) );
	$new = wp_generate_attachment_metadata( $thumbnail_id, $image_path );
	wp_update_attachment_metadata( $thumbnail_id, $new );
	
	return true;
}