<?php
/**
 * Plugin Name: Image Classifier Alt Tag
 * Description: Automatically generates alt tags for images uploaded to the media library. Sets the alt tag to "it works".
 * Version: 1.0
 * Author: Your Name
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Automatically set alt tag for uploaded images.
 *
 * @param int $attachment_id The ID of the uploaded attachment.
 */
function icat_set_default_alt_tag( $attachment_id ) {
    // Get the attachment type.
    $attachment = get_post( $attachment_id );

    // Ensure the attachment is an image.
    if ( wp_attachment_is_image( $attachment_id ) ) {
        // Set the alt tag to "it works".
        update_post_meta( $attachment_id, '_wp_attachment_image_alt', 'it works' );
    }
}
add_action( 'add_attachment', 'icat_set_default_alt_tag' );
