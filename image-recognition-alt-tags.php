<?php
/**
 * Plugin Name: Add Alt Text to Uploaded Images with JS
 * Description: Automatically sets the alt text of uploaded images to "my image" for image formats only, and enqueues a JavaScript file.
 * Version: 1.2
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

        // Hook to enqueue scripts for admin
        add_action('admin_enqueue_scripts', [$this, 'enqueue_admin_scripts']);
    }

    /**
     * Adds alt text to uploaded images and updates custom metadata.
     */
    public function add_alt_text_to_images(array $metadata, int $attachment_id): array {
        // Add custom metadata (for demonstration purposes)
        update_post_meta($attachment_id, 'custom_meta', 'example');

        return $metadata;
    }

    /**
     * Enqueues the JavaScript file for the admin area.
     */
    public function enqueue_admin_scripts(): void {
        $current_screen = get_current_screen();
        if (in_array($current_screen->id, ['upload', 'post', 'post-new'])) {
            wp_enqueue_script(
                'custom-upload-js',
                plugin_dir_url(__FILE__) . 'js/index.js',
                ['jquery', 'media-upload', 'media-views'], // Dependencies for Media Library
                '1.0',
                true
            );

            wp_localize_script('custom-upload-js', 'customUploadData', [
                'ajaxUrl' => admin_url('admin-ajax.php'),
                'nonce'   => wp_create_nonce('custom_nonce'),
            ]);
        }
    }
}

add_action('admin_footer', function() {
    echo '<script>console.log("Checking Media Events:", wp.media.events);
</script>';
});

// Initialize the plugin
new AddAltTextToUploadedImages();
