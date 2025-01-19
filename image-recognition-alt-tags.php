<?php
/**
 * Plugin Name: Auto Alt Tag Setter
 * Plugin URI: https://example.com
 * Description: Automatically adds the alt tag "Hello" to images uploaded to the media library.
 * Version: 1.0.0
 * Author: Your Name
 * Author URI: https://example.com
 * License: MIT
 */

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

// Hook into the image upload process
function auto_set_image_alt($post_ID) {
    $mime_type = get_post_mime_type($post_ID);

    // Only process image uploads
    if (strpos($mime_type, 'image/') !== false) {
        update_post_meta($post_ID, '_wp_attachment_image_alt', 'Hello');
    }
}
add_action('add_attachment', 'auto_set_image_alt');
