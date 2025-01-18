<?php
/**
 * Plugin Name: Add Alt Text to Uploaded Images
 * Description: Automatically sets the alt text of uploaded images to "my image" for image formats only.
 * Version: 1.0
 * Author: Your Name
 * License: GPL2
 */

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

class AddAltTextToUploadedImages {

    public function __construct() {
        // Hook into the attachment metadata save process
        add_filter('wp_generate_attachment_metadata', [$this, 'add_alt_text_to_images'], 10, 2);
    }

    /**
     * Adds alt text to uploaded images.
     *
     * @param array $metadata Metadata for the attachment.
     * @param int   $attachment_id ID of the attachment.
     * @return array Updated metadata.
     */
    public function add_alt_text_to_images(array $metadata, int $attachment_id): array {
        // Get the attachment mime type
        $mime_type = get_post_mime_type($attachment_id);

        // Check if the uploaded file is an image
        if (strpos($mime_type, 'image/') === 0) {
            // Update the alt text
            update_post_meta($attachment_id, '_wp_attachment_image_alt', 'my image');
        }

        return $metadata;
    }
}

// Initialize the plugin
new AddAltTextToUploadedImages();
