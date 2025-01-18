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

        // Hook to enqueue scripts for frontend
        add_action('wp_enqueue_scripts', [$this, 'enqueue_frontend_scripts']);

        // Hook to enqueue scripts for admin
        add_action('admin_enqueue_scripts', [$this, 'enqueue_admin_scripts']);
    }

    /**
     * Adds alt text to uploaded images.
     */
    public function add_alt_text_to_images(array $metadata, int $attachment_id): array {
        $mime_type = get_post_mime_type($attachment_id);

        if (strpos($mime_type, 'image/') === 0) {
            update_post_meta($attachment_id, '_wp_attachment_image_alt', 'my image');

             // Inject JavaScript to call logMessage function
            add_action('admin_footer', function() {
                echo '<script>
                    if (typeof logMessage === "function") {
                        logMessage();
                    } else {
                        console.error("logMessage function is not defined.");
                    }
                </script>';
            });
        }

        return $metadata;
    }

    /**
     * Enqueues the JavaScript file for the frontend.
     */
    public function enqueue_frontend_scripts(): void {
        wp_enqueue_script(
            'image-recognition-script',
            plugin_dir_url(__FILE__) . 'js/index.js',
            ['jquery'], // Dependencies
            '1.0',
            true // Load in footer
        );

        wp_localize_script('image-recognition-script', 'icatAjax', [
            'ajaxUrl' => admin_url('admin-ajax.php'),
            'nonce'   => wp_create_nonce('icat_nonce'),
        ]);
    }

    /**
     * Enqueues the JavaScript file for the admin area.
     */
    public function enqueue_admin_scripts(): void {
        wp_enqueue_script(
            'image-recognition-admin-script',
            plugin_dir_url(__FILE__) . 'js/index.js', // Same file can be used if it applies to both areas
            ['jquery'], // Dependencies
            '1.0',
            true // Load in footer
        );

        wp_localize_script('image-recognition-admin-script', 'icatAdminAjax', [
            'ajaxUrl' => admin_url('admin-ajax.php'),
            'nonce'   => wp_create_nonce('icat_nonce'),
        ]);
    }

}

// Initialize the plugin
new AddAltTextToUploadedImages();
