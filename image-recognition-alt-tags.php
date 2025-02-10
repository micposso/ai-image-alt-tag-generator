<?php
/**
 * Plugin Name: Auto Alt Text
 * Description: Automatically sets the alt text of images after they are uploaded.
 * Version: 1.0
 * Author: Your Name
 */

if (!defined('ABSPATH')) {
    exit; // Prevent direct access
}

// Enqueue JavaScript in admin media uploader
function auto_alt_text_enqueue_script($hook)
{
    if ($hook === 'upload.php' || $hook === 'post.php' || $hook === 'post-new.php') {
        wp_enqueue_script(
            'auto-alt-text-js',
            plugin_dir_url(__FILE__) . 'js/custom.js', // Updated path
            ['jquery'],
            '1.0',
            true
        );

        // Pass WordPress REST API nonce for authentication
        wp_localize_script('auto-alt-text-js', 'AutoAltTextSettings', [
            'restUrl' => rest_url('wp/v2/media/'),
            'nonce' => wp_create_nonce('wp_rest'),
        ]);
    }
}
add_action('admin_enqueue_scripts', 'auto_alt_text_enqueue_script');

?>